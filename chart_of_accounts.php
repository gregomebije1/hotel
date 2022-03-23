<?
session_start();
if (!isset($_SESSION['uid'])) {
    header('Location: index.php');
    exit;
}
error_reporting(E_ALL);

require_once "ui.inc";
require_once "hotel.inc";
require_once "util.inc";
require_once 'acc.inc';

$con = connect();

/*
if (!(user_type($_SESSION['uid'], 'Report Viewer', $con)
 || user_type($_SESSION['uid'], 'Accountant', $con)
 || user_type($_SESSION['uid'], 'Admin', $con)
 )){
  main_menu($_SESSION['uid'],
    $_SESSION['first_name'] . " " . $_SESSION['last_name'], $con);
  echo msg_box('Access Denied!', 'index.php?action=logout', 'Continue');
  exit;
}
*/
if(isset($_REQUEST['action']) && ($_REQUEST['action'] =="Print")) {
    print_header('Chart of Accounts', 'chart_of_accounts.php',  
      'Back to Chart Of Accounts', $con);
} else {
    main_menu($_SESSION['uid'],
      $_SESSION['firstname'] . " " . $_SESSION['lastname'], '', '', $con);
?>
    <table border="0" width="100%" rules="rows">
     <tr class='class1'>
      <td>
       <h3>Chart of Accounts</h3>
       <a href='account.php?action=Form'>Add Account</a>
       <form action="chart_of_accounts.php" method="post">
       </form>
      </td>
     </tr>
<?
 }
  $result = mysql_query("SELECT id, name from account_type", $con); 
  while($acc_type = mysql_fetch_array($result)) {
    echo("<tr><td><b>".$acc_type['name']."</b></td></tr>");
    $result2 = mysql_query("SELECT * FROM account 
      where acc_type_id={$acc_type['id']}", $con); 
    while($accounts = mysql_fetch_array($result2)) {
      echo "<tr><td>{$accounts['d_created']}
       &nbsp;&nbsp;{$accounts['name']}&nbsp;&nbsp;
       <a href='account.php?action=Delete&id={$accounts['id']}'>
           Delete</a></td></tr>";
    }   
    echo "<tr><td>&nbsp;</td></tr>";
  }
  echo "</table>";
  main_footer();
?>
