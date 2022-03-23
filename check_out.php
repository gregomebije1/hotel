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
main_menu($_SESSION['uid'], 
  $_SESSION['firstname'] . " " . $_SESSION['lastname'], 
  $_SESSION['entity_id'], $_SESSION['shift'], $con);

$acc_name = '';
$doc_number = '';
$bal = 0;
$room_number = '';
$gid = '';
$room_id = '';

global $acc_name, $doc_number, $bal, $room_number, $gid, $room_id, $grp_cmp_id;

if (!empty($_REQUEST['rid'])) {
  $rid = $_REQUEST['rid'];
  $sql="select g.id, a.name as 'acc_name', r.acc_id, r.number, c.name, g.title, 
  g.firstname, g.lastname, g.bill_to, g.arrival_date, g.room_id,
  g.doc_number, g.grp_cmp_id  from room r 
  left join (guest g, room_category c, account a) on 
  (r.category_id = c.id and r.id=g.room_id and r.acc_id = a.id)
  where r.id=$rid and r.occupancy='Occupied'";
 
  $result = mysql_query($sql, $con);
  $or = mysql_fetch_array($result);
 	
  $bal = get_acc_bal($or['acc_id'], 1, $or['arrival_date'], 
    $_REQUEST['dd'], $con);

  if (empty($grp_cmp_id)) 
    $name_of_guest = get_unique_guest_name($rid, $con);
  else 
    $name_of_guest = get_unique_group_name($rid, $con); 
}

if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Check Out')) {
  
  if(empty($_REQUEST['rid'])) {
    echo msg_box('Please choose a hotel room', 'check_out.php', 'Back');
    exit;
  }  
  if(empty($_REQUEST['dd'])) {
    echo msg_box('Please enter departure date', 'check_out.php', 'Back');
    exit;
  }
  echo "<h1>Balance is $bal</h1>";

  $sql ="SELECT a.name from account a join room r 
   on a.id = r.acc_id where r.id=$rid";
	 
   $result2 = mysql_query($sql, $con);
   $a_name = mysql_fetch_array($result2);
	
   $acc_name = "{$or['number']} {$or['name']} {$or['title']}
      {$or['firstname']} {$or['lastname']} of the group account: 
      {$a_name['name']}";

  $date = $_REQUEST['dd'];
  echo "<form action='check_out.php'>";
  if ($bal > 0) {
    if ($or['bill_to'] == 'GroupCompany') {
      $r = mysql_query("select name from group_company_guests 
        where id={$or['grp_cmp_id']}", $con);
      $or2 = mysql_fetch_array($r);
      echo "<b style='color:red'>The guest you are trying to check out  
        belongs to the group {$or2['name']} <br>
         Checking out this guest will
	 Remove this account and other guests booked under this account</b>";
    }
    ?>
      <p>
        <b><?=$acc_name?></b>
          still has an OUTSTANDING balance of 
        <b><?=$bal?></b>
      </p> 
      <p>
       Do you want to check out 
        <b><?=$acc_name?></b>
        <input type="hidden" name="checkout_type" value="forced">
		<input type="hidden" name="date" value="<?=$date?>">
      </p> 
     <?
  } elseif ($bal == 0) {
	 if ($or['bill_to'] == 'GroupCompany') {
	   $r = mysql_query("select name from group_company_guests 
            where id={$or['grp_cmp_id']}", $con);
	   $or2 = mysql_fetch_array($r);
	   echo "<b style='color:red;'>The guest you are trying to 
            check out belongs to
	    the group {$or2['name']} <br>Checking out this guest will
	    Remove this account and other guests booked under this account</b>";
	  }
      ?>
        <p>
         Are you sure you want to check out
         <b><?=$acc_name?></b>
        </p>
        <input type="hidden" name="checkout_type" value="normal">
		<input type="hidden" name="date" value="<?=$date?>">
     <?
  } elseif ($bal < 0) {
    if ($or['bill_to'] == 'GroupCompany') {
      $r = mysql_query("select name from group_company_guests 
        where id={$or['grp_cmp_id']}", $con);
      $or2 = mysql_fetch_array($r);
      echo "<b style='color:red;'>The guest you are trying to check out 
        belongs to the group {$or2['name']} <br> Checking out this guest will
         Remove this account and other guests booked under this account</b>";
    }
    ?>
      <p>
        <b><?=$acc_name?></b>
          still has a REFUNDABLE balance of 
        <b><?=$bal?></b>
      </p> 
      <p>
       Do you want to check out
        <b><?=$acc_name?></b>
      </p> 
      <input type="hidden" name="checkout_type" value="forced">
	  <input type="hidden" name="date" value="<?=$date?>">
    <?
    } 
?>
      <input type="submit" name="checkout" value="Yes">
	  
      <input type="button" value="Cancel" 
       onClick="location.href='guest.php'">
      <input type="hidden" name="rid" value="<?=$rid?>">
	  <input type="hidden" name="bal" value="<?=$bal?>">
	  <input type="hidden" name="date" value="<?=$date?>">
     </form>
<?
  exit;
} elseif (isset($_REQUEST['checkout']) && ($_REQUEST['checkout']=='Yes')) {
  /*if (get_bill_to($_REQUEST['rid'], $con) == 'individual') {
    $guest_name = get_unique_guest_name($_REQUEST['rid'], $con);
  } else {
    $group_name = get_unique_group_name($_REQUEST['rid'], $con);
  } 
  */
  
  $result = check_out($_REQUEST['rid'], $_REQUEST['date'], $_REQUEST['bal'], $_SESSION['shift'], $_SESSION['uid'], $con); 
  if ($result == 1) {
    echo msg_box("Successfully Checked Out", 'check_out.php', 'Back');
	exit;
  } else {
    echo msg_box($result, 'check_out.php', 'Back');
    exit;
  }
}
?>
      <table> 
      <tr class='class1'>
       <td colspan="4">
        <h3>Check Out</h3>
        <form name="myform" action="check_out.php" method="post">
       </td>
      </tr>
     <tr>
       <td>Room</td>
       <td>
        <select name="rid">
         <option></option>
 <?
 $result = mysql_query("select r.id, r.number, c.name, g.title, 
    g.firstname, g.lastname from room r left join 
    (guest g, room_category c) on 
    (r.category_id = c.id and r.id = g.room_id) 
    where r.occupancy = 'Occupied'", $con);

  if (mysql_num_rows($result) <= 0) {
    echo msg_box("There are no checked in Guests to check out", 'guest.php', 
      'Back');
    exit;
  }
  while($or = mysql_fetch_array($result)) {
    echo "<option value='" . $or['id'] . "'>"
      . $or['number'].' '.$or['name'].' '.$or['title'] 
      . ' ' . $or['firstname'].' '. $or['lastname']. ' '. "</option>";
  }
?>
        </select>
       </td>
      </tr>
	  
     <tr>
      <td>Departure Date</td>
      <td><input type='text' name='dd' value='<?php echo date('Y-m-d'); ?>' size='10' maxlength='10'>YYYY-MM-DD</td>
     </tr>
     <tr>
	 
      <tr>
       <td>
        <input name="action" type="submit" value="Check Out">
        <input name="action" type="submit" value="Cancel">
        </form>
      </td></tr>
     </table>
<? 
  main_footer();
?>
