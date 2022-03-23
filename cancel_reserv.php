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

  if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Cancel Reservation')) {
    if (empty($_REQUEST['rid'])) {
       echo msg_box('Please choose a Room', 
        'cancel_reserv.php', 'Back');
       exit;
    }
	$sql = "select r.acc_id, g.id, r.number, g.title, 
     g.firstname, g.lastname from room r join guest g 
     on r.id = g.room_id where r.id=".$_REQUEST['rid'];
    $row = mysql_query($sql, $con);
	$aid = $row['acc_id'];

    mysql_query("delete from guest where id=$aid", $con);
    mysql_query("update room set occupancy = 'Vacant', 
      acc_id = NULL where id=".$_REQUEST['rid'], $con);
    mysql_query("delete from account where id=$aid", $con);
    echo msg_box("Reservation for ".$row['number'].' '.$row['title'].' '.$row['firstname']
 	  .' '.$row['lastname'].' has been canceled', 
	  'cancel_reserv.php', 'Back');
	exit;
  } 
  ?>
  <table> 
      <tr style="background-color:silver">
       <td colspan="4">
        <h3>Cancel Reservation</h3>
        <form name="myform" action="cancel_reserv.php" method="post">
       </td>
      </tr>
     <tr>
       <td>Room
       
        <select name="rid">
         <option></option>
 <?
  $result = mysql_query("select r.id, r.number, c.name, g.title, 
    g.firstname, g.lastname from room r left join 
    (guest g, room_category c) on 
    (r.category_id = c.id and r.id = g.room_id) 
    where r.occupancy = 'Booked'", $con);
  while($or = mysql_fetch_array($result)) {
    echo "<option value='". $or['id'] ."'>"
     . $or['number'].' '.$or['name'].' '.$or['title'].' '.$or['firstname'].' '.$or['lastname']." </option>";
  }
  ?>
        </select>
       </td>
      </tr>
      <tr><td>
	     <input name="action" type="submit" value="Cancel Reservation">
         <input name="action" type="submit" value="Cancel">
        </form>
      </td></tr>
     </table>
  <?
   main_footer();
   ?>

