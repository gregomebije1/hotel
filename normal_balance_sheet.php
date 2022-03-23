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

##########
function profit_and_loss($sdate, $edate, $con) {
  $sales = get_acc_type_bal(INCOME, $sdate, $edate, $con);

  $opening_stock = get_acc_type_bal(OPENING_STOCK, $sdate, $edate, $con);
  $purchases = get_acc_type_bal(PURCHASES, $sdate, $edate, $con);

  $sum_opn_stck_purc = $opening_stock + $purchases;

  $cogs = $sum_opn_stck_purc - $clo_stock;
  $gross_profit = $sales - $cogs;

  $expenses = get_acc_type_bal(EXPENSES, $sdate, $edate, $con);
  $net_profit = $gross_profit - $expenses;

  return $net_profit;
}

/*
if (!(user_type($_SESSION['uid'], 'Report Viewer', $con)
 || user_type($_SESSION['uid'], 'Accountant', $con))){
  main_menu($_SESSION['uid'],
    $_SESSION['first_name'] . " " . $_SESSION['last_name'], $con);
  echo msg_box('Access Denied!', 'index.php?action=logout', 'Continue');
  exit;
}
*/
if(isset($_REQUEST['action']) && ($_REQUEST['action'] =="Print")) {
    print_header('Balance Sheet', 'balance_sheet.php',  
      'Back', $con);
} else {
    main_menu($_SESSION['uid'],
      $_SESSION['firstname'] . " " . $_SESSION['lastname'], '1', '', $con);

  if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Process')) {
    if (empty($_REQUEST['sdate']) || (empty($_REQUEST['edate']))) {
      echo msg_box('Please enter both the start and end dates', 
       'balance_sheet.php', 'Back');
      exit;
    }
    $sdate = $_REQUEST['sdate'];
    $edate = $_REQUEST['edate'];
    ?>
    <table>
     <tr>
      <td colspan="6">
       <table border='1'>
        <tr class='class1'>
         <td>
	  <h3>Balance Sheet</h3>
          <form action="balance_sheet.php">
           <input type="submit" name="action" value="Print">
           <input type="hidden" name="sdate" value="<?=$_REQUEST['sdate']?>">
           <input type="hidden" name="edate" value="<?=$_REQUEST['edate']?>">
          </form>
         </td>
        </tr>
       </table>
      </td>
     </tr>
     <tr>
      <td colspan='6'>
       <table border='1'>
        <tr>
         <td>Start Date<td><td><b><?=$sdate?></b></td>
         <td>End Date<td><td><b><?=$edate?></b></td>
        </tr>
       </table>
      </td>
     </tr>
     <tr>
     <?
     #Process Fixed Assets
     $sql = "SELECT * FROM account where acc_type_id = " . FIXED_ASSETS;
     $f_assets = get_accounts($sql, $sdate, $edate, $con);
     print_accounts($sql, 'Fixed Assets', $sdate, $edate, $con);
     
     #Process Account Receivable 
     $sql = "SELECT * FROM account where acc_type_id = " . ACCOUNT_RECEIVABLE;
     $c_assets1 = get_accounts($sql, $sdate, $edate, $con);
     print_accounts($sql,'Account Receivable',$sdate,$edate,$con);
     
     #Process other current assets 
     $sql = "SELECT * FROM account where acc_type_id=".OTHER_CURRENT_ASSETS;
     $c_assets2 = get_accounts($sql, $sdate, $edate, $con);
     print_accounts($sql,'Other Current Assets',$sdate,$edate,$con);

     $c_assets = $c_assets1 + $c_assets2;

     #Process Account Payable 
     $sql  = "SELECT * FROM account where acc_type_id = ".ACCOUNT_PAYABLE ;
     $c_liabilities1 = get_accounts($sql, $sdate, $edate, $con);
     print_accounts($sql, 'Account Payable', $con);

     #Process other current Liabilities 
     $sql="SELECT * FROM account where acc_type_id=".OTHER_CURRENT_LIABILITIES;
     $c_liabilities2 = get_accounts($sql, $sdate, $edate, $con);
     print_accounts($sql, 'Other Current Liabilities',$sdate,$edate, $con);

     $c_liabilities = $c_liabilities1 + $c_liabilities2;
     echo "$c_liabilities<br>";

     #Net current assets/(liabilities)
     $net_curr_a_l = $c_assets - $c_liabilities;
     echo "$net_curr_a_l<br>";

     echo "<tr>
        <td><b><i>NET CURRENT ASSETS/(LIABILIIES)</i></b></td>
        <td>&nbsp;</td><td><b>"
        . number_format($net_curr_a_l, 2)."</b></td></tr>";
     echo "<tr><td>&nbsp;</td></tr>";
     
     $total_a_less_c_l = ($f_assets + $c_assets) - $c_liabilities;
     echo "<tr><td><i><b>TOTAL ASSETS LESS CURRENT LIABILITIES</b></i></td> 
        <td>&nbsp;</td>
        <td><b>".number_format((($f_assets + $c_assets) - $c_liabilities), 2) 
         . "</b></td></tr>
        <tr><td>&nbsp;</td></tr>"; 

     #Process Long Liabilities 
     $sql="SELECT * FROM account where acc_type_id = ". LONG_TERM_LIABILITIES;
     $l_liabilities = get_accounts($sql, $sdate, $edate, $con);

     print_accounts($sql,'Creditors: Amounts Falling Due After One Year',
        $sdate,$edate, $con);
     
     echo "<tr><td><b>CAPITAL</b></td></tr>";

     #Process Equity 
     $sql = "SELECT * FROM account where acc_type_id = " . EQUITY;
     $t_equities = get_accounts($sql, $sdate, $edate, $con);
     $t_equities = print_accounts($sql, 'Equity', $sdate, $edate, $con);

     $profit_and_loss = profit_and_loss($sdate, $edate, $con);
     echo "<tr><td>Profit/Loss for the year</td><td>&nbsp;</td><td><b>" . 
        number_format($profit_and_loss, 2) . "</b></td><td>&nbsp;</td></tr>";

     $t_liabilities = $l_liabilities + $t_equities + $profit_and_loss;
     echo "<tr><td>&nbsp;</td><td>&nbsp;</td>
        <td><b>" . number_format($t_liabilities, 2) . "</b></td></tr>";

     echo "</table>";
     main_footer();
     exit;
   }
 }
?>
  <table> 
   <tr class='class1'>
    <td colspan="4">
     <h3>Balance Sheet</h3>
     <form action="balance_sheet.php" method="post">
    </td>
   </tr>
   <tr>
    <td>Starting Date</td>
    <td> 
     <input type='text' name='sdate' value='<?php echo date('Y-m-d'); ?>'>
    </td>
   </tr>
   <tr>
    <td>Ending Date</td>
    <td> 
     <input type='text' name='edate' value='<?php echo date('Y-m-d'); ?>'>
    </td> 
   </tr>
   <tr>
    <td>
     <input name="action" type="submit" value="Process">
     <input name="action" type="submit" value="Cancel">
    </td>
   </tr>
  </table>
  <? main_footer(); ?>
