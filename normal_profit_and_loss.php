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

if(isset($_REQUEST['action']) && ($_REQUEST['action'] =="Print")) {
    print_header('Profit and Loss', 'profit_and_loss.php',  
      'Back', $con);
} else {
    main_menu($_SESSION['uid'],
      $_SESSION['firstname'] . " " . $_SESSION['lastname'], 
      $_SESSION['entity_id'], $_SESSION['shift'], $con);
}
  if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Process')) {
    if (empty($_REQUEST['sdate']) || (empty($_REQUEST['edate']))) {
      echo msg_box('Please enter both the start and end dates', 
        'profit_and_loss.php', 'Back');
      exit;
    }
    $sdate = $_REQUEST['sdate'];
    $edate = $_REQUEST['edate'];
    ?>
    <table>
     <tr>
      <td colspan="6">
       <table>
        <tr class='class1'>
         <td>
	  <h3>Profit and Loss</h3>
          <form action="profit_and_loss.php">
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
       <table>
        <tr>
         <td>Start Date<td><td><b><?=$sdate?></b></td>
         <td>End Date<td><td><b><?=$edate?></b></td>
        </tr>
       </table>
      </td>
     </tr>
     <?
     ########
     #Process Sales
     ######
     $sql = "SELECT * FROM account where acc_type_id=" . INCOME; 
     $sales = get_accounts($sql, $sdate, $edate, $con);
     if ($sales != 0) {
       print_accounts($sql, 'Sales', $sdate, $edate, $con);
     }

     echo "<tr><td>&nbsp;</td></tr>";
     $sql  = "SELECT * FROM account where acc_type_id =" . OPENING_STOCK;
     $opening_stock = get_accounts($sql, $sdate, $edate, $con);

     $sql  = "SELECT * FROM account where acc_type_id =" . PURCHASES;
     $purchases = get_accounts($sql, $sdate, $edate, $con);

     $sum_opn_stck_purc = $opening_stock + $purchases;

     $sql  = "SELECT * FROM account where acc_type_id =" . CLOSING_STOCK;
     $clo_stock = get_accounts($sql, sdate,$edate,$con);

     $cogs = $sum_opn_stck_purc - $clo_stock;

     if (($opening_stock != 0) && ($purchases != 0) && ($sum_opn_stck_purc != 0)
       && ($clo_stock != 0) && ($cogs != 0)) {
       echo "<tr><td><b>Less Cost of Goods Sold </b></td></tr>";

       print_accounts($sql,'Opening Stock', $sdate, $edate, $con);
       print_accounts($sql,'Purchases', $sdate, $edate, $con);

       echo "<tr><td>&nbsp</td><td>$sum_opn_stck_purc</td></tr>";
       print_accounts($sql,'Less Closing Stock', $sdate, $edate, $con);
    
       echo "<tr><td>&nbsp;</td><td>$cogs</td></tr>";
     }

     $gross_profit = $sales + $cogs;
     ?>
    <tr>
     <td><h3>Gross Profit</h3></td> 
     <td>&nbsp;</td>
     <td><h3><?php echo number_format($gross_profit, 2); ?></h3></td>
    </tr>
    <?
    #Process Expenses
    $sql = "SELECT * FROM account where acc_type_id =" . EXPENSES;
    $expenses = get_accounts($sql, $sdate, $edate, $con);
    if ($expenses != 0) {
      print_accounts($sql,'Expenses', $sdate, $edate, $con);
    }

    $net_profit = $gross_profit - $expenses;
    echo "
    <tr>
     <td><h3>Net Profit</h3></td>
     <td>&nbsp;</td>
     <td><h3> " . number_format($net_profit, 2) . " </h3></td>
    </tr>
   <tr><td>&nbsp;</td></tr>
  </table>";
 exit;
 }
?>
  <table> 
   <tr class='class1'>
    <td colspan="4">
     <h3>Profit and Loss</h3>
     <form action="profit_and_loss.php" method="post">
    </td>
   </tr>
   <tr>
    <td>Starting Date</td>
    <td>
     <input type='text' name='sdate' value='<?php echo date('Y-m-d'); ?>'>
    </td>
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
