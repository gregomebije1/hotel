<?
session_start();

if (!isset($_SESSION['uid'])) {
    header('Location: index.php');
    exit;
}
error_reporting(E_ALL);

require_once "ui.inc";
require_once "util.inc";
require_once "acc.inc";
require_once "hotel.inc";


$con = connect();

/*
if (!(user_type($_SESSION['uid'], 'Administrator', $con)
 || user_type($_SESSION['uid'], 'Accountant', $con))){
  main_menu($_SESSION['uid'],
    $_SESSION['first_name'] . " " . $_SESSION['last_name'], $con);
  echo msg_box('Access Denied!', 'index.php?action=logout', 'Continue');
  exit;
}
*/
if(isset($_REQUEST['action']) && ($_REQUEST['action'] =="Print")) {
    print_header('Trial Balance', 'trial_balance.php',  
      'Back', $con);
} else {
    main_menu($_SESSION['uid'],
      $_SESSION['firstname'] . " " . $_SESSION['lastname'], '1', '', $con);

  if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Process')) {
    if (empty($_REQUEST['sdate']) || empty($_REQUEST['edate'])) {
      echo msg_box('Please enter correct begining and ending date',
        'trial_balance.php', 'Back');
      exit;
    }
    $sdate = $_REQUEST['sdate'];
    $edate = $_REQUEST['edate'];
    ?>
    <table border='1'>
     <tr>
      <td colspan="6">
       <table>
        <tr class='class1'>
         <td>
         <form action="trial_balance.php">
          <input type="submit" name="action" value="Print">
          <input type="hidden" name="sdate" value="<?=$_REQUEST['sdate']?>">
          <input type="hidden" name="edate" value="<?=$_REQUEST['edate']?>">   
        </form>
       </td>
      </tr>
     </table>
    </td>
   </tr>
   <tr><td colspan='6'><h3>TRIAL BALANCE</h3></td></tr>
   <tr>
    <td colspan='6'>
     <table width="100%">
      <tr>
       <td><b>Start Date</b></td><td><?=$sdate?></td>
       <td><b>End Date</b></td><td><?=$edate?></td>
      </tr>
     </table>
    </td>
   </tr>
   <tr style="background-color:silver">
    <td>Acct. Name</td><td>Debit</td><td>Credit</td></tr>
    <?
     $d_bal = 0;
     $c_bal = 0;
    $result = mysql_query("SELECT * FROM account", $con);
    while($accounts = mysql_fetch_array($result)) {
      echo"<tr><td>". $accounts['name'] ."</td>";
      $bal = get_acc_bal($accounts['id'], '1', $sdate, $edate, $con);
      if( 
       ($accounts['acc_type_id'] % 2) == 1) {
        echo "<td>" . number_format($bal, 2) . "</td><td>&nbsp;</td></tr>";
        $d_bal += $bal;
      } else {
        echo "<td>&nbsp;</td><td>" . number_format($bal, 2) . "</td></tr>";
        $c_bal += $bal;
      }
    }
    ?>
    <tr><td>&nbsp;</td></tr>
    <tr>
     <td><b>Total</b></td>
     <td><b><?php echo number_format($d_bal, 2); ?></b></td>
	 <td><b><?php echo number_format($c_bal,  2); ?></b></td>
    </tr>
    <?
    exit;  //Dont process Process beyond this point
  } // end of 'action=Process'
}  // end of else
if ((isset($_REQUEST['action']) && ($_REQUEST['action'] != 'Print')) 
   ||(!isset($_REQUEST['action']))) {
  ?>
  <table> 
   <tr class="class1">
    <td colspan="4"><h3>Trial Balance</h3></td>
   </tr>
   <form action="trial_balance.php" method="post">
   <tr>
    <td>Start Date</td>
    <td>
     <input type='text' name='sdate' value='<?php echo date('Y-m-d');?>'>
    </td>
   </tr>
   <tr> 
    <td>End Date</td>
    <td>
     <input type='text' name='edate' value='<?php echo date('Y-m-d');?>'>
    </td>
   </tr>
   <tr>
    <td>
     <input name="action" type="submit" value="Process">
     <input name='action' type='submit' value='Cancel'>
    </td>
   </tr>
  </form>
 </table>
<?  main_footer(); } ?>
