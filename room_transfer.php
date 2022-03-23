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

main_menu($_SESSION['uid'], $_SESSION['firstname'] 
    . " " . $_SESSION['lastname'],  $_SESSION['entity_id'], 
  $_SESSION['shift'], $con);

if(isset($_REQUEST['action']) && ($_REQUEST['action'] =="OK")) {
  if (empty($_REQUEST['orn']) || empty($_REQUEST['nrn'])) {
    echo msg_box('Please choose old and new rooms', 
    'room_transferphp', 'Back');
    exit;
  }
  change_rooms($_REQUEST['orn'], $_REQUEST['nrn'], $con);
  //echo msg_box('Rooms have been changed', 'index.php', 'Back');
}
    ?>
    <table>
     <tr class='class1'>
      <td colspan="4">
        <h3>Room Transfer</h3>
        <form name="myform" action="room_transfer.php" method="post">
       </td>
      </tr>
     <tr><td>Old Room No</td><td><select name="orn" 
       onchange="var a = document.myform.orn.value; 
                 document.myform.gid.value = a;
                 ">
      <option></option>
   <?
   $result = mysql_query("SELECT r.id as 'rid', r.number, g.id, g.title, g.firstname, 
   g.lastname from room r join guest g on r.id = g.room_id", $con);

   while($rg = mysql_fetch_array($result)) {
     echo "<option value=".$rg['rid'].">"
      . $rg['number']. ' '. $rg['title'] . ' ' .  $rg['firstname'] 
      . ' ' . $rg['lastname'] . "</option>";
   }
   ?>
      </select></td></tr>
      <input type="hidden" name="gid" value="0"> 
      <tr><td>New Room No</td><td><select name="nrn">
       <option></option>
  <?
  $result = mysql_query("SELECT r.id, r.number, c.name, r.occupancy, 
   r.status from room r join room_category c on r.category_id = c.id where 
   r.occupancy = 'Vacant'", $con);
  while ($room = mysql_fetch_array($result)) {
    echo "<option value=". $room['id'] . ">" . $room['number'] 
     .' '.$room['name'].' '.$room['occupancy'].' '. $room['status'] 
     . "</option>";
  }
  ?>
      </select></td>
      <tr><td><input name="action" type="submit" value="OK">
      </td></tr>
     </form>
     </table>
  <? 
  main_footer();
?>
