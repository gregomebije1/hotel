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
$con = connect();

if(isset($_REQUEST['action']) && ($_REQUEST['action'] =="Print")) {
    print_header('Profit and Loss', 'profit_and_loss.php',  
      'Back', $con);
} else {
    main_menu($_SESSION['uid'],
      $_SESSION['firstname'] . " " . $_SESSION['lastname'], $con);

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
     #Process Sales
     $sql = "SELECT * FROM account where acc_type_id = 4 and status='Active'";
     $sales = print_accounts($sql, 'Sales', $sdate, $edate, $con);

     echo "<tr><td>&nbsp;</td></tr>";

     #Process COGS
     $sql  = "SELECT * FROM account where acc_type_id = 6 and status='Active'";
     $cogs = print_accounts($sql, 'Less Cost of Goods Sold', $sdate, $edate, 
      $con);
     echo "<tr><td>&nbsp;</td></tr>";
     echo "<tr><td>&nbsp;</td></tr>";
     $gross_profit = $sales - $cogs;
     ?>
    <tr>
     <td><h3>Gross Profit</h3></td> 
     <td>&nbsp;</td>
     <td><h3><?=$gross_profit?></h3></td>
    </tr>
    <?
    #Process Expenses
    $sql = "SELECT * FROM account where acc_type_id = 5 and status='Active'";
    $expenses = print_accounts($sql, 'Expenses', $sdate, $edate, $con);
    echo "<tr><td>&nbsp;</td></tr>";

    $net_profit = $gross_profit - $expenses;
    ?>
    <tr>
     <td><h3>Net Profit</h3></td>
     <td>&nbsp;</td>
     <td><h3><?=$net_profit?></h3></td>
    </tr>
   <tr><td>&nbsp;</td></tr>
  </table>
 <? 
 main_footer();
 exit;
 }
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
