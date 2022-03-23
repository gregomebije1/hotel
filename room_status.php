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
    print_header('Room Status', 'room_status.php?action=Form',  
      'Back To Main Menu', $con);
} else {
    main_menu($_SESSION['uid'],
      $_SESSION['firstname'] . " " . $_SESSION['lastname'],
      $_SESSION['entity_id'], $_SESSION['shift'], $con);

  if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Form')) { 
 ?>
<table> 
 <tr class='class1'>
  <td colspan="4">
   <h3>Room Status</h3>
   <form action="room_status.php" method="post">
  </td>
 </tr>
 <!--
 <tr>
  <td width="20%">Status</td>
  <td>
   <select name="rs">
    <option>All</option>
    <option>Clean</option>
    <option>Dirty</option>
    
   </select>
  </td>
 </tr>

 <tr>
  <td>Occupancy</td>
  <td>
   <select name="oc">
    <option>All</option>
    <option>Vacant</option>
    <option>Occupied</option>
    
   </select>
  </td>
 </tr>
 -->
 <tr>
  <td>Category</td>
  <td> 
   <select name="rc">
    <option>All</option>
    <?
    $result = mysql_query("select id, name from room_category", $con);
    while($room_category = mysql_fetch_array($result)) {
      ?> <option value="<?=$room_category['id']?>"><?=$room_category['name']?></option>
    <? } ?>
   </select>
  </td>
 </tr>
 <tr>
  <td><input name="action" type="submit" value="Process">
      <input name="action" type="submit" value="Cancel">
  </td>
 </tr>
 </form>
</table> 
 
<?
  exit;
  }
}  
if (isset($_REQUEST['action']) && 
   (($_REQUEST['action'] == 'Print') || ($_REQUEST['action'] == 'Process'))) { 
  if (empty($_REQUEST['rc'])) {
      echo msg_box('Please choose room category', 
	    'room_status.php?action=Form', 'Back');
      exit;	  
  }
  
    ?>
    <table>
      <tr>
       <td colspan="8">
	   <table>
		 <?
		 if ($_REQUEST['action'] != 'Print') {
		   ?>
		    
		    <tr class='class1'><td>ROOM STATUS</td>
           <form action="room_status.php">
            <tr class='class1'><td><input type="submit" name="action" value="Print"></td></tr>
		    <tr><td><input type='hidden' name='rc' value='<?php echo $_REQUEST['rc'];?>'></td></tr>
           </form>
		  <? } ?>
         
        </table>
       </td>
      </tr>
	<?
    //$rs = ($_REQUEST['rs'] == 'All') ? "status is not null" : "status = '" . $_REQUEST['rs'] . "'";
    //$oc = ($_REQUEST['oc'] == 'All') ? "occupancy is not null" : "occupancy = '" . $_REQUEST['oc'] . "'";
    $rc = ($_REQUEST['rc'] == 'All') ? "rc.id is not null " : "rc.id = " . $_REQUEST['rc'];
  
    $sql = "select g.id, r.number, g.title, 
      g.firstname, g.lastname, g.phone, g.arrival_date,
      g.departure_date, rc.name from room r left join 
	   (guest g, room_category rc) on (r.id = g.room_id and r.category_id = rc.id)
      where $rc";
    //echo $sql;
	
	$result = mysql_query($sql, $con);
    if (mysql_num_rows($result) == 0) {
      echo msg_box("No Room currently occupied that fits your search
       criteria ", 'room_status.php?action=Form', 'Back');
      exit;
    } 
    ?> 
    <tr class='class1'>
       <th>Room</th>
       <th>Title</th>
       <th>Firstname</th>
       <th>Lastname</th>
       <th>Phone</th>
       <th>Arrival</th>
       <th>Departure</th>
      </tr>
    <?
    while($row = mysql_fetch_array($result)) {
	  ?>
	  <tr>
        <!--<td><input type='radio' name='gid' value='<?//=$row['id']?>'></td>-->
		
            <td><a href='guest.php?action=View&gid=<?=$row['id']?>'><?=$row['number']?> <?=$row['name']?></a></td>
            <td><?=$row['title']?></td>
            <td><?=$row['firstname']?></td>
            <td><?=$row['lastname']?></td>
            <td><?=$row['phone']?></td>
            <td><?=$row['arrival_date']?></td>
            <td><?=$row['departure_date']?></td>
          </tr>
      <?
    }
    echo "</table>";
	main_footer();
	exit;
 }
?>
