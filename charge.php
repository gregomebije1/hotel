<?php
session_start();
if (!isset($_SESSION['uid'])) {
    header('Location: index.php');
    exit;
}
error_reporting(E_ALL);

require_once "ui.inc";
require_once "util.inc";
require_once "hotel.inc";
require_once "acc.inc";

$con = connect();
/*
if (!(user_type($_SESSION['uid'], 'Administrator', $con)
  || user_type($_SESSION['uid'], 'Accounts', $con))) {
  main_menu($_SESSION['uid'],
    $_SESSION['firstname'] . " " . $_SESSION['lastname'], 
	$_SESSION['entity_id'], $_SESSION['shift'], $con);
  echo msg_box('Access Denied!', 'index.php?action=logout', 'Continue');
  exit; 
}  
*/  
if(isset($_REQUEST['action']) && ($_REQUEST['action'] =="Print")) { 
  print_header('List of Guests', 'charge.php', 'Back To Main Menu', $con);
} else {
  main_menu($_SESSION['uid'], 
    $_SESSION['firstname'] . " " . $_SESSION['lastname'],
	$_SESSION['entity_id'], $_SESSION['shift'], $con);
}
if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Add Charge')) {
    if (empty($_REQUEST['name']) || (!is_string($_REQUEST['name'])))  {
       echo msg_box('Please enter a correct fee Name', 
        'fee.php?action=Add', 'Back');
       exit;
    }
    $sql = "select * from charges where name='{$_REQUEST['name']}'";
    if (mysql_num_rows(mysql_query($sql)) > 0) {
      echo msg_box('Error: A charge with the same name already exist<br>
       Please choose another charge', 'charge.php?action=add', 'Back');
      exit;
    }
	if (empty($_REQUEST['amount']) || (!is_numeric($_REQUEST['amount'])))  {
       echo msg_box('Please enter a correct Charge Amount', 
        'charge.php', 'Back');
       exit;
    }
    if (!account_exists($_REQUEST['name'], $con)) {
      //These charges have credit entries 
      $acc_id = add_account($_REQUEST['name'], INCOME,'1',date('Y-m-d'), $con);
      $sql="insert into charges (name, type, amount, acc_id) 
	   values('{$_REQUEST['name']}', '{$_REQUEST['type']}', 
	   '{$_REQUEST['amount']}', $acc_id)";
	} else {
	  echo msg_box("Error: An accont name already exist bearing 
	   the name of this charge", 'charge.php', 'Back');
	  exit;
	}
    mysql_query($sql) or die(mysql_error());
	echo msg_box('Charge successfully added', 'charge.php', 'Continue');
	exit;
  } else if (isset($_REQUEST['action']) && 
    ($_REQUEST['action'] == 'Update Charge')) {

    if (empty($_REQUEST['amount']) || (!is_numeric($_REQUEST['amount'])))  {
       echo msg_box('Please enter a correct Charge Amount', 
        'charge.php', 'Back');
       exit;
    }
    if (empty($_REQUEST['id']))  {
       echo msg_box('Please choose a charge to edit', 
        'charge.php', 'Back');
       exit;
    }
    $sql="update charges set 
	 type='{$_REQUEST['type']}', amount='{$_REQUEST['amount']}'
     where id={$_REQUEST['id']}";
	
    mysql_query($sql) or die(mysql_error());
	echo msg_box('Charges successfully Updated', 'charge.php', 'Continue');
	exit;

  } 
  else if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Delete')) {
    echo msg_box("Deleting this charge will delete the account this charge is tired to<br>
	 It will also delete all the journal entries attached to this account<br> 
	 Are you sure want to delete this charge?<br>", 
     "charge.php?action=confirm_delete&id={$_REQUEST['id']}", 'Continue');
    exit;
  } else if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 
    'confirm_delete')) {

	$acc_id = get_value('charges', 'acc_id', 'id', $_REQUEST['id'], $con);
	$sql="delete from account where id=$acc_id";
	mysql_query($sql) or die(mysql_error());
	    
	$sql="delete from charges where id={$_REQUEST['id']}";
    mysql_query($sql) or die(mysql_error());
	
	echo msg_box('Charge Deleted', 'charge.php', 'Continue');
	exit;
  } 

  else if (isset($_REQUEST['action']) && 
   (($_REQUEST['action'] == 'Add') || ($_REQUEST['action'] == 'Edit') || 
    ($_REQUEST['action'] == 'View'))) {


   if ($_REQUEST['action'] != 'Add') {
     if (empty($_REQUEST['id'])) {
       echo msg_box("Please choose a Charge", 'charge.php', 'Back');
       exit;
     }
    }
    $id = empty($_REQUEST['id']) ? '0' : $_REQUEST['id'];
    $sql="select * from charges where id = $id";
    $result = mysql_query($sql) or die(mysql_error());
    $row = mysql_fetch_array($result);
    $av = array();

	$name =  $row['name'] ? $row['name'] : "";
    $av['name'] = $name;
    $av['type'] = $row['type'] ? $row['type'] : "";
	$av['amount'] = $row['amount'] ? $row['amount'] : "";
     ?>
    <table> 
     <tr class='class1'>
      <td colspan='3'><h3><?php echo $_REQUEST['action']; ?> Charge</h3></td>
     </tr>
     <form name='form1' action="charge.php" method="post">
	 <tr>
      <td style='width:50em;'>
       <table>
     <?php
     if (($_REQUEST['action'] == 'Edit') || ($_REQUEST['action'] == 'View')) {
         echo tr(array('name', textfield('name', 'name','value',
           $name, 'disabled', 'disabled')));
         unset($av['name']);
     }
      foreach($av as $name => $value) 
       if ($name == 'type') {
         echo tr(array('Type', 
         selectfield(array('percentage_of_room_rate'=>'Percentage Of Room Rate',
		  'constant_amount'=>'Constant Amount'),
          $name,$value)));
       } else { 
         echo tr(array($name, textfield('name', $name, 'value', $value)));
       }
     echo "<tr><td>";
     if ($_REQUEST['action'] != 'View') {
       if($_REQUEST['action'] == 'Edit') { 
         echo "<input name='id' type='hidden' value='{$_REQUEST['id']}'>";
       }
       echo "<input name='action' type='submit' value='"; 
       echo $_REQUEST['action'] == 'Edit' ? 'Update' : 'Add';
       echo " Charge'>";
     }
     ?>
     <input name="action" type="submit" value="Cancel">
	 </td>
    </tr>
	</form>
   </table>
   <?php
    exit;
   } 
  if (!isset($_REQUEST['action']) || ($_REQUEST['action'] == 'Cancel')
   || ($_REQUEST['action'] == 'Print')) {
  ?>
  <table border='1'>
   <tr class='class1'>
   <?php 
   if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Print')) {
      echo "<td></td>";
    } else {
      echo "<td>
    <form name='form1' action='charge.php' method='post'>
     <select name='action' onChange='document.form1.submit();'>
      <option value=''>Choose option</option>
      <option value='Add'>Add</option>
	  /*
      <option value='Edit'>Edit</option>
	  */
      <option value='Delete'>Delete</option>
      <option value='Print'>Print</option>
     </select>
    </td>
     ";
    }
   ?>
    <td colspan='8' style='text-align:center;'><h3>Charges</h3></td>
   </tr>
   <tr>
    <th style='width:0.1em;'>&nbsp;</th>
    <th>Name</th>
	<th>Type</th>
	<th>Amount</th>
   </tr>
   <?php
   $result = mysql_query("select * from charges") or die(mysql_error());
   while($row = mysql_fetch_array($result)) {
   ?>
    <tr>
     <td><input type='radio' name='id' value='<?=$row['id']?>'></td>
     <td><?=$row['name']?></td>
	 <td><?=$row['type']?></td>
	 <td><?=$row['amount']?></td>
    </tr>
    <?
    }
    echo '</form></table>';
    main_footer();
  }
?>
