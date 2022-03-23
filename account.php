<?
session_start();
if (!isset($_SESSION['uid'])) {
    header('Location: index.php');
    exit;
}
error_reporting(E_ALL);

require_once "hotel.inc";
require_once "util.inc";
require_once "acc.inc";

$con = connect();

main_menu($_SESSION['uid'],
  $_SESSION['firstname'] . " " . $_SESSION['lastname'], 
  $_SESSION['entity_id'], $_SESSION['shift'], $con);

if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Add')) {
  if (empty($_REQUEST['n']))  {
    echo msg_box('Please enter a value in the name field', 
        'account.php', 'Back');
    exit;
  }
  if (empty($_REQUEST['t']))  {
    echo msg_box('Please add an account type', 
        'account.php?action=Form', 'Back');
    exit;
  }
  $result = add_account($_REQUEST["n"], $_REQUEST["t"], OTHER_CURRENT_ASSETS, 
   date('Y-m-j'), $con);
  /*
   //Make an opening balance entry
  if(($_REQUEST['t'] == 1) || ($_REQUEST['t'] == 6) || ($_REQUEST['t'] == 5)
     || ($_REQUEST['t'] == 7)) {
     $type = 'Debit';
  } else {
     $type = 'Credit';
  }
  j_entry($type, '1', get_acc_id($_REQUEST['n'], $con),  date('Y-m-j'), 
     'Opening Balance', $_REQUEST['opening_balance'], $con);
  */
  echo msg_box('Account has been added', 'chart_of_accounts.php', 
   'Continue');
  exit;
} 
if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Delete')) {
  if (empty($_REQUEST['id'])) {
    echo msg_box("Please choose an Account", 'chart_of_accounts.php', 'Back');
    exit;
  }
  $sql="select * from journal where acc_id={$_REQUEST['id']}";
  $result = mysql_query($sql) or die(mysql_error());
  if (mysql_num_rows($result) > 0) {
    echo msg_box("***WARNING***<br>
      Deleting this account will delete all journal 
      entries still tired to this Account<br>
      Are you sure you want to delete " . 
      get_value('account', 'name', 'id', $_REQUEST['id'], $con)
      . " Account?" , 
      "account.php?action=confirm_delete&id={$_REQUEST['id']}", 
      'Continue to Delete');
      exit;
  } else {
    //Will never reach here
    echo msg_box("***WARNING***<br>
      Are you sure you want to delete " . 
      get_value('account', 'name', 'id', $_REQUEST['id'], $con)
      . " Account?" , 
      "account.php?action=confirm_delete&id={$_REQUEST['id']}", 
      'Continue to Delete');
    exit;
 }
}

if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'confirm_delete')) {
  if (empty($_REQUEST['id'])) {
    echo msg_box("Please choose an Account", 'chart_of_accounts.php', 'Back');
    exit;
  }
  $sql="select * from account where id={$_REQUEST['id']}";
  $result = mysql_query($sql) or die(mysql_error());
  if (mysql_num_rows($result) <= 0) {
    echo msg_box("Account does not exist in the database", 
      'chart_of_accounts.php', 'OK');
    exit;
  }
  $sql="delete from journal where acc_id={$_REQUEST['id']}";
  $result = mysql_query($sql) or die(mysql_error());
	
  $sql="delete from account where id={$_REQUEST['id']}";
  $result = mysql_query($sql) or die(mysql_error());
	
  echo msg_box("Account has been deleted", 'chart_of_accounts.php', 'OK');
  exit;
}
if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Form')) {
?>
	<table> 
     <tr class="class1">
      <td colspan="4"><h3>Add Account</h3></td>
     </tr>
     <form action="account.php" method="post">
     <tr><td>Name</td><td><input type="text" name="n"></td></tr>
	 <tr><td>Type</td><td><select name="t">
	 
    <?
    $result = mysql_query("select id, name from account_type", $con);
    while($row = mysql_fetch_array($result)) {
    ?>
      <option value="<?=$row['id']?>"><?=$row['name']?></option>
    <?} ?>
     </select></td></tr>
     <!--
	 <tr><td>Opening Balance</td><td><input type="text" name="opening_balance"></td></tr>
     -->
     <tr>
      <td><input name="action" type="submit" value="Add">
          <input name="action" type="submit" value="Cancel"> </td>
     </tr>
    </table>
	<?
}
main_footer();
?>
