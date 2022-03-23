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
    print_header('Room Status', 'room_status.php',  
      'Back', $con);
} else {
    main_menu($_SESSION['uid'],
      $_SESSION['firstname'] . " " . $_SESSION['lastname'],
      $_SESSION['entity_id'], $_SESSION['shift'], $con);

  if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Search')) { 
	 if (!check_date($_REQUEST['adday'], $_REQUEST['admonth'], 
          $_REQUEST['adyear'])) { 
       echo msg_box('Please enter correct arrival date', 
        'guest_status.php', 'Back');
       exit;
    } 
    if (!check_date($_REQUEST['ddday'], $_REQUEST['ddmonth'],
          $_REQUEST['ddyear'])) {
       echo msg_box('Please enter correct departure date',
        'guest_status.php', 'Back');
       exit;
    }
	if (empty($_REQUEST['adday']) || (empty($_REQUEST['ddday']))) {
	  echo msg_box('Please enter both the start and end dates', 
	    'guest_status.php', 'Back');
	  exit;
    }
	if (!isset($_REQUEST['choice'])) {
	  echo msg_box('Please choose a filter or enter a Guest name',
	  'guest_status.php', 'Back');
	  exit;
	}
    ?>
    <table>
      <tr>
       <td colspan="6">
        <table>
         <tr class='class1'>
         <td>
		  <h3>GUEST STATUS</h3>
          <form action="guest_status.php">
           <input type="submit" name="action" value="Print">
           <input type="hidden" name="choice" value="<?=$_REQUEST['choice']?>">
           <input type="hidden" name="filter" value="<?=$_REQUEST['filter']?>">
           <input type="hidden" name="gn" value="<?=$_REQUEST['gn']?>">
		   <input type="hidden" name="adday" value="<?=$_REQUEST['adday']?>">
           <input type="hidden" name="admonth" value="<?=$_REQUEST['admonth']?>">
           <input type="hidden" name="adyear" value="<?=$_REQUEST['adyear']?>">
		   <input type="hidden" name="dday" value="<?=$_REQUEST['dday']?>">
		   <input type="hidden" name="ddmonth" value="<?=$_REQUEST['ddmonth']?>">
           <input type="hidden" name="ddyear" value="<?=$_REQUEST['ddyear']?>">
           <input type="hidden" name="history" value="<?=$_REQUEST['history']?>">
        </form>
         </td>
         </tr>
        </table>
       </td>
      </tr>
	<?
	$ad = make_date($_REQUEST['adyear'], $_REQUEST['admonth'], $_REQUEST['adday']);
    $dd = make_date($_REQUEST['ddyear'], $_REQUEST['ddmonth'], $_REQUEST['ddday']);
    
	$adc = (isset($_REQUEST['adc'])) ? $_REQUEST['adc'] : '';
	$ddc = (isset($_REQUEST['ddc'])) ? $_REQUEST['ddc'] : '';
	
    if (empty($adc)) {
      $adc = "off";
    }
    if (empty($$ddc)) {
     $ddc = "off";
    }
    $sql = "select r.number, g.title, g.firstname, g.lastname, g.phone,
      g.arrival_date,  g.departure_date, r.occupancy from room r join guest g 
      on r.id = g.room_id where ";
    $sql2 = "";
    if (($adc == 'arrival') && ($ddc == 'departure')) {
      $sql2 = " g.arrival_date = '$ad' and g.departure_date = '$dd'";
    }
    if ($ddc == 'off') {
      $sql2 = " g.arrival_date = '$ad' ";
    }
    if ($adc == 'off') {
      $sql2 = " g.departure_date = '$dd' ";
    }
    if ($_REQUEST['filter'] == 'No Date') {
      $sql .= " g.id IS NOT NULL ";
    }
    if ($_REQUEST['filter'] == 'Specific Date') { 
      $sql .= $sql2;
    }
    if ($_REQUEST['filter'] == 'Expected Arrivals') {
      $sql .= " r.occupancy = 'Booked' and $sql2";
    }   
    if ($_REQUEST['choice'] == 'gn') {
      $sql .= " and g.title like '%".$_REQUEST['gn']."%' or g.firstname like '%".$_REQUEST['gn'] ."%' 
       or g.lastname like '%".$_REQUEST['gn']."%' and $sql2";
    }   
	
    $result = mysql_query($sql, $con);
    if (mysql_num_rows($result) == 0) {
      msg_box("No Guest Found", 'guest_status.php', 'Back');
      exit;
    }
    ?>
   
    <tr style="background-color:silver">
     <td>Room</td>
     <td>Name of Guest</td>
     <td>Phone</td>
     <td>Arrival Date</td>
     <td>Departure Date</td>
     <td>Occupancy</td>
    </tr>
    <?
    while ($row = mysql_fetch_array($result)) {
    ?>
      <tr>
       <td><?=$row['number']?></td>
       <td><?=$row['title']?> &nbsp; <?=$row['firstname']?> &nbsp; <?=$row['lastname']?></td>
       <td><?=$row['phone']?></td>
       <td><?=$row['arrival_date']?></td>
       <td><?=$row['departure_date']?></td>
       <td><?=$row['occupancy']?></td>
      </tr>
    <?
   }
   echo "</table>";
   
   exit;
  }
}
?>
<table> 
 <tr class='class1'>
  <td colspan="4">
   <h3>Search Guests</h3>
   <form name="myform" action="guest_status.php" method="post">
  </td>
 </tr> 
 <tr>
  <td>
   <input type="radio" name="choice" value="filter"
     onClick="hide_element('gn');
     display_element('filter');">
   Filter
   <select id="filter" name="filter" style="display:none;">
    <option>No Date</option>
    <option>Specific Date</option>
    <option>Expected Arrivals</option>
   </select>
   <input type="radio" name="choice" value="gn"
    onClick="hide_element('filter');
    display_element('gn');">
   Guest Name
   <input style="display:none" id="gn" type="text" name="gn">
  </td>
 </tr>
 <tr>
  <td>
   <fieldset>
    <legend>Date</legend>
     <table>
      <tr>
       <td><input type="checkbox" name="adc" value="arrival">Arrival</td>
	   <? gen_date('ad'); ?>
      </tr>
      <tr>
       <td><input type="checkbox" name="ddc" value="departure">Departure</td>
       <? gen_date('dd'); ?>
      </tr>
     </table>
    </fieldset>
   </td>
  </tr>
  <tr>
   <td><input name="action" type="submit" value="Search">
          <input name="action" type='submit' value='Cancel'>
   </td>
  </tr>
 </table>
 <? main_footer(); ?>
