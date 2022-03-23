<?
session_start();
if (!isset($_SESSION['uid'])) {
    header('Location: index.php');
    exit;
}
error_reporting(E_ALL);
require_once "hotel.inc";
require_once "util.inc"; 
require_once "ui.inc";

$con = connect();
main_menu($_SESSION['uid'], 
  $_SESSION['firstname'] . " " . $_SESSION['lastname'], 
  $_SESSION['entity_id'], $_SESSION['shift'], $con);

if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Process')) {
  if (empty($_REQUEST['room_id'])) {
    echo msg_box('Please enter a room number', 'auto_billing.php', 'Back');
    exit;
  } 
  if (empty($_REQUEST['doc_number'])) {
    echo msg_box('Please enter receipt number', 'auto_billing.php', 'Back');
    exit;
  } 
  if (empty($_REQUEST['date'])) {
    echo msg_box('Please enter date', 'auto_billing.php', 'Back');
    exit;
  } 

  $sql = "select r.id, g.bill_to, r.acc_id, c.rate, r.number, a.name from 
   room r left join (room_category c, account a, guest g) 
   on (r.acc_id = a.id and c.id = r.category_id and r.id=g.room_id) 
   where r.id={$_REQUEST['room_id']}";
   
  $a_id = get_acc_id('Room Sales', $con);  
  $result = mysql_query($sql, $con);
  
  $a_i_r = mysql_fetch_array($result);
  
  $guest_name="";
  if ($a_i_r['bill_to'] == 'individual') {
    $guest_name = get_unique_guest_name($_REQUEST['room_id'], $con);
  } else {
    $guest_name = get_unique_group_name($_REQUEST['room_id'], $con);
  }

  #####RECORD AS CREDIT SALES #####     
  $sales_log = "sales";
  $id = add_sales($_REQUEST['date'], $_SESSION['shift'], 
    get_user($_SESSION['uid'], $con), 
        $guest_name, $_REQUEST['doc_number'], get_room($a_i_r['id'], $con),
        "Room Rate Auto Billing", 'Room', 
        'Credit Sales', $a_i_r['rate'], '', '', '', '', '', $con);
  $sales_log = "$sales_log $id";

  #####CREDIT SALES ACCOUNT#####
  $journal_log = "journal";
  $id = j_entry("Credit", $_SESSION['entity_id'], $a_id, $_REQUEST['date'], 
   "Room charge for (". $a_i_r['number'].")", $a_i_r['rate'], $con);
  $journal_log= "$journal_log $id";

  #####DEBIT GUEST'S ACCOUNT#####
  $id = j_entry("Debit", $_SESSION['entity_id'], $a_i_r['acc_id'], 
   $_REQUEST['date'],  "Room charge for (". $a_i_r['number'].")", 
   $a_i_r['rate'], $con);
  $journal_log= "$journal_log $id";
  
  #####CALCULATE AMOUNT FOR VARIOUS CHARGES AND MAKE ENTRIES#####
  $total_percentage = 0;
  $total_amount = 0;

  if (isset($_REQUEST['charges'])) {
    foreach ($_REQUEST['charges'] as $charge_id) {
      $sql="select * from charges where id=$charge_id";
      $result = mysql_query($sql) or die(mysql_error());	  
      $row = mysql_fetch_array($result);

      if ($row['type'] == 'percentage_of_room_rate') { 
        $charge = ($row['amount']/100) * $a_i_r['rate'];
        $charge = floor($charge); //Remove decimals
	$total_percentage += $charge; 
			
        ###Debit Guest Account
        $id = j_entry('Debit', '1', $a_i_r['acc_id'], $_REQUEST['date'], 
          "{$row['name']} for $guest_name ", $charge, $con);
        $journal_log= "$journal_log $id";
  
        ####Credit charges
        $id = j_entry('Credit', '1', $row['acc_id'], $_REQUEST['date'],
          "{$row['name']} for $guest_name", $charge, $con);
        $journal_log= "$journal_log $id";
			
        ####Record as Charges
        $id = add_sales($_REQUEST['date'], $_SESSION['shift'], 
          get_user($_SESSION['uid'], $con), $guest_name, 
          $_REQUEST['doc_number'], get_room($a_i_r['id'], $con), 
	  $row['name'], 'Room', 'Charges', $charge,
	  '', '', '', '', '', $con);
        $sales_log = "$sales_log $id";

      } else if ($row['type'] == 'constant_amount')  {
        $charge = $row['amount'];
	$total_amount += $charge; 
		
        ###Debit Guest Account
        $id = j_entry('Debit', '1', $a_i_r['acc_id'], $_REQUEST['date'], 
          "{$row['name']} for $guest_name", $charge, $con);
        $journal_log= "$journal_log $id";

	###Debit Charges (Expenses)
	$id = j_entry('Credit', '1', $row['acc_id'], $_REQUEST['date'], 
          "{$row['name']} for $guest_name", $charge, $con);
        $journal_log= "$journal_log $id";

        ####Record as Charges
        $id = add_sales($_REQUEST['date'], $_SESSION['shift'], 
          get_user($_SESSION['uid'], $con), $guest_name, 
          $_SESSION['doc_number'], get_room($a_i_r['id'], $con), 
	  $row['name'], 'Room', 'Charges', $charge,
	  '', '', '', '', '', $con);
        $sales_log = "$sales_log $id";
     }
   }
  }
  //Record in the log
  $log = "$sales_log|$journal_log";
  audit_trail($_SESSION['uid'],
   "Auto_bill $guest_name<br /> Receipt Number: {$_REQUEST['doc_number']} <br />
    Date: {$_REQUEST['date']}", $_SESSION['shift'], $log, $con);

  echo msg_box("Room {$a_i_r['number']} has been billed", 
   'auto_billing.php', 'Back');
   exit;
}
?>
<table> 
 <tr class='class1'>
  <td colspan="4">
   <h3>Auto Billing</h3>
   <form name="myform" action="auto_billing.php" method="post">
  </td>
 </tr>
    <?
    $result = mysql_query("select r.id, r.number, c.name, g.title, 
     g.firstname, g.lastname from room r left join 
     (guest g, room_category c) on 
     (r.category_id = c.id and r.id = g.room_id) 
     where r.occupancy = 'Occupied'", $con);

    if (mysql_num_rows($result) <= 0) {
      echo msg_box("No guests to auto bill", 'guest.php', 'Back');
      exit;
    }
    echo " 
     <tr>
     <td>Room<td>
      <select name='room_id'>
       <option></option>
    ";
    while($or = mysql_fetch_array($result)) {
      echo "<option value='{$or['id']}'>
       {$or['number']} {$or['name']} {$or['title']}
       {$or['firstname']} {$or['lastname']} </option>";
    }
    ?>
   </select>
  </td>
 </tr>
 <?php
  echo tr(array('Receipt Number', textfield('name', 'doc_number')));
  echo tr(array('Date', textfield('name', 'date', 
    'value', date('Y-m-d'))));
 ?>
 </tr>
 <tr> 
  <td>Charges</td>
  <td>
   <table>
    <tr>
     <td>
      <select name='charges[]' size='3' multiple='multiple'>
      <?php
      $sql="select * from charges";
      $result = mysql_query($sql) or die(mysql_error());
      while($row = mysql_fetch_array($result))
        echo "<option value='{$row['id']}'>{$row['name']}</option>";
      ?>
      </select>
     </td>
    </tr>
   </table>
  </td>
 </tr>
 <tr style='text-align:center; border: 1px solid #ebf3ff;'>
  <td colspan='2'>
   <input name="action" type="submit" value="Process">
   <input name="action" type="submit" value="Cancel">
  </form>
  </td>
 </tr>
</table>
<? main_footer();?>
