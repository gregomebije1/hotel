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
/*
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
*/
     #Process Sales
     echo "
      <table style='width:100%'>
       <tr>
        <td>
         <table style='border: solid 1px black; width:100%;'>
          <tr><td><h1>GLORIANA HOTELS & SUITE LTD</h1></td></tr>
	  <tr><td>&nbsp;</td></tr>
	  <tr><td><span style='font-size:1.5em;'>
            TRADING, PROFIT & LOSS ACCOUNT</span></td></tr>
          <tr><td><span style='font-size:1.5em;'>
           FOR THE MONTH ENDED 31ST JANUARY, 2011</span></td></tr>
         </table>
        </td>
       </tr>
       <tr>
        <td>
         <table style='width:100%' border='1'>
          <tr><td>&nbsp;</td><td>N</td><td>N</td></tr>
          <tr><td>Sales</td><td>&nbsp;</td><td>&nbsp;</td></tr>
     ";
     $sql = "SELECT * FROM account where acc_type_id = 4";
     $result = mysql_query($sql, $con) or die(mysql_error());
     $total_income = 0;
     if (mysql_num_rows($result) > 0) {
       while($row = mysql_fetch_array($result)) {
         $total_income += get_acc_bal($row['id'], '1', $con);
         echo "<tr>
                <td>{$row['name']}</td>
               <td>" . get_acc_bal($row['id'], '1', $con) . "</td>
               <td>&nbsp;</td>
              </tr>";
      }
    }
    echo "<tr>
           <td>&nbsp;</td>
           <td>&nbsp;</td>
           <td class='top'>$total_income</td></tr>";
    echo "
    <tr><td>&nbsp;</td></tr>
    <tr><td class='underline'>Less- Service Charge & VAT</td></tr>
    <tr><td>Service Charge Receivable</td><td>982.61</td><td>&nbsp;</td></tr>
    <tr><td>VAT Chargeable</td><td>491.30</td><td>1,473.91</td></tr>
    <tr>
     <td class='bold;'>Net Sales</td>
     <td>&nbsp;</td>
     <td class='top' style='font-weight:bold;'>9,826.09</td></tr>
    <tr><td>&nbsp;</td></tr>
    <tr><td>Opening Stocks</td><td>3,250.00</td></tr>
    <tr><td>&nbsp;</td></tr>
    <tr><td class='underline'>Purchases</td></tr>
    <tr><td>Guest Kitchen</td><td>1,250.00</td></tr>
    <tr><td>Beverages</td><td>1,100.00</td></tr>
    <tr><td>Laundry Items</td><td>800.00</td></tr>
    <tr><td>CAFÉ Expenses</td><td>1,000.00</td></tr>
    <tr><td>Guest Complimentary</td><td>1,050.00</td></tr>
    <tr><td>F & B Complimentary</td><td>250.00</td></tr>
    <tr><td>House-Keeping Items</td><td>385.00</td></tr>
    <tr><td></td><td class='top'>9,085.00</td></tr>
    <tr><td>&nbsp;</td></tr>
    <tr><td>Closing Stocks</td><td class='top'>3,000.00</td></tr>
    <tr><td>&nbsp;</td><td></td><td class='bottom'>6,085.00</td></tr>
    <tr><td>Gross Profit/(Loss)</td><td></td><td class='bold'>3,741.09</td></tr>
    <tr><td>&nbsp;</td></tr>
    <tr><td>Expenses</td><td>&nbsp;</td><td>&nbsp;</td></tr>
    ";
    $sql = "SELECT * FROM account where acc_type_id = 5";
     $result = mysql_query($sql, $con) or die(mysql_error());
     $total_expenses = 0;
     if (mysql_num_rows($result) > 0) {
       while($row = mysql_fetch_array($result)) {
         $total_expenses += get_acc_bal($row['id'], '1', $con);
         echo "<tr>
                <td>{$row['name']}</td>
                <td>" . get_acc_bal($row['id'], '1', $con) . "</td>
               <td>&nbsp;</td>
              </tr>";
      }
    }
    echo "
   </table>
  </td>
 </tr>
</table>
";
?>
