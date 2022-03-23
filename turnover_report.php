<?
session_start();

if (!isset($_SESSION['uid'])) {
    header('Location: index.php');
    exit;
}
error_reporting(E_ALL);

require_once "hotel.inc";
require_once "util.inc";

$con = connect();

if(isset($_REQUEST['action']) && ($_REQUEST['action'] =="Print")) {
    print_header('Turnover Report', 'turnover_report.php',  
      'Back to Turnover Report', $con);
} else {
    main_menu($_SESSION['uid'],
      $_SESSION['firstname'] . " " . $_SESSION['lastname'],
      $_SESSION['entity_id'], $_SESSION['shift'], $con);

  if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Process')) {
    if (!check_date($_REQUEST['sdday'], $_REQUEST['sdmonth'], 
          $_REQUEST['sdyear'])) { 
       echo msg_box('Please enter correct starting date', 
        'turnover_report.php', 'Back');
       exit;
    } 
   if (!check_date($_REQUEST['edday'], $_REQUEST['edmonth'],
          $_REQUEST['edyear'])) {
       echo msg_box('Please enter correct ending date',
        'turnover_report.php', 'Back');
       exit;
    }
 
    $sddate = make_date($_REQUEST['sdyear'], 
      $_REQUEST['sdmonth'], $_REQUEST['sdday']);
    $eddate = make_date($_REQUEST['edyear'], 
      $_REQUEST['edmonth'], $_REQUEST['edday']);
    ?>
    <table>
      <tr>
       <td colspan="6">
        <table>
         <tr class='class1'>
         <td>
          <form action="turnover_report.php">
           <input type="submit" name="action" value="Print">
         <input type="hidden" name="sdday" value="<?=$_REQUEST['sdday']?>">
         <input type="hidden" name="sdmonth" value="<?=$_REQUEST['sdmonth']?>">
         <input type="hidden" name="sdyear" value="<?=$_REQUEST['sdyear']?>">
         <input type="hidden" name="edday" value="<?=$_REQUEST['edday']?>">
         <input type="hidden" name="edmonth" value="<?=$_REQUEST['edmonth']?>">          <input type="hidden" name="edyear" value="<?=$_REQUEST['edyear']?>">
        </form>
         </td>
         </tr>
        </table>
       </td>
      </tr>
     <tr><td colspan='6'><h3>DAILY TURNOVER REPORT</h3></td></tr>
   <?
    turnover_report($sddate, $eddate, $con); 
    exit;
  }
}    
 if ((isset($_REQUEST['action']) && ($_REQUEST['action'] != 'Print')) 
   ||(!isset($_REQUEST['action']))) {
  ?>
    <table> 
     <tr class="class1">
      <td colspan="4"><h3>Turnover Report</h3></td>
     </tr>
     <form action="turnover_report.php" method="post">
     <tr><td>Start Date</td>
      <? gen_date('sd') ?>
     </tr>
     <tr> 
      <td>End Date</td>
      <? gen_date('ed')?>
     </tr>
      <tr><td><input name="action" type="submit" value="Process">
          <input name='action' type='submit' value='Cancel'>
      </td></tr>
     </table>
<?
  main_footer();
}
