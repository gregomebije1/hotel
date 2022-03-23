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
  print_header('List of Reservation', 'reservation.php', 
   'Back To Main Menu', $con);
} else {
  main_menu($_SESSION['uid'], 
    $_SESSION['firstname'] . " " . $_SESSION['lastname'], 
    $_SESSION['entity_id'], $_SESSION['shift'], $con);

  if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Add Guest')) {
    ####Input: A 'quote' is <b>bold</b>
    ####Output: A &#039;quote&#039; is &lt;b&gt;bold&lt;/b&gt;
    foreach ($HTTP_POST_VARS as $key => $value) {
      $_REQUEST[$key] = htmlentities($value, ENT_QUOTES);
    }
    if(empty($_REQUEST['rid'])) {
      echo msg_box('Please choose a room', 
        'reservation.php?action=Add', 'Back');
      exit;
    }
    if (empty($_REQUEST['t']) || empty($_REQUEST['f']) || 
     empty($_REQUEST['l']) ) {
      echo msg_box('Please enter firstname and lastname', 
       'reservation.php?action=Add', 'Back');
      exit;
    }
    if (empty($_REQUEST['sx'])) {
      echo msg_box('Please choose sex', 
        'reservation.php?action=Add', 'Back');
	  exit;
    }
    if (empty($_REQUEST['ad'])  || empty($_REQUEST['dd'])) {
      echo msg_box('Please enter arrival date and departure date', 
      'reservation.php?action=Add', 'Back');
      exit;
    }
    $addate = $_REQUEST['ad'];
    $dddate = $_REQUEST['dd'];
	
    $result = mysql_query("SELECT * FROM reservation 
     WHERE firstname ='" . $_REQUEST['f']."' and lastname = '".$_REQUEST['l']
     ."'", $con);
    if(mysql_num_rows($result) > 0) {
      echo msg_box("This Guest's firstname and lastname is taken. 
       Please choose another", 'reservation.php?action=Add', 'Back');
	  exit;
    } else {
      $sql = "INSERT INTO reservation (title, firstname, lastname, 
        phone, room, sex, address,
        arrival_date, departure_date)
        VALUES ('{$_REQUEST['t']}', '{$_REQUEST['f']}', '{$_REQUEST['l']}', 
              '{$_REQUEST['p']}', '{$_REQUEST['rid']}', '{$_REQUEST['sx']}', 
              '{$_REQUEST['a']}','$addate', '$dddate')";

      $result = mysql_query($sql, $con)
       or die("Cannot execute SQL " . mysql_error());
    }
  } elseif (isset($_REQUEST['action']) && 
   (($_REQUEST['action'] == 'Edit') || ($_REQUEST['action'] == 'View') 
     || ($_REQUEST['action'] == 'Add'))) {
   //Get guests who have booked rooms
    $title = '';
    $firstname = '';
    $lastname = '';
    $phone ='';
    $sex = '';
    $address = '';
    $arrival_date = date('Y-m-d');
    $departure_date = date('Y-m-d');
    $room = '';
    $id = '';
    $action = $_REQUEST['action'];
    if(($_REQUEST['action'] == 'Edit') || ($_REQUEST['action'] == 'View')) {
      if(empty($_REQUEST['gid'])) {
        echo msg_box("Please choose a reservation to View", 
         'reservation.php', 'Back');
        exit;
      }
      $result = mysql_query("select * from reservation 
        where id={$_REQUEST['gid']}",   $con) 
        or die("Cannot execute SQL query " . mysql_error());
      $row = mysql_fetch_array($result);
      $title = $row['title'];
      $firstname = $row['firstname'];
      $lastname = $row['lastname'];
      $phone = $row['phone'];
      $sex = $row['sex'];
      $address = $row['address'];
      $arrival_date = $row['arrival_date'];
      $departure_date = $row['departure_date'];
      $room = $row['room'];
      $gid = $_REQUEST['gid'];
    }
    ?>
    <table> 
     <tr class='class1'>
      <td colspan='7'> 
       <h3><?php echo $_REQUEST['action']; ?></h3>
       <form name="myform" action="reservation.php" method="post">
      </td>
     </tr>
     <tr><td><input type="hidden" name='gid' value="<?php echo $gid; ?>">
       </td></tr>
      <?
       if($_REQUEST['action'] == 'Add') {
         echo "
	   <tr>
            <td>Room</td>
            <td>
             <select id='rid' name='rid' onchange='fetch_rate();'>";
         //Get all the vacant rooms
         $result = mysql_query("select r.id, r.number, c.name, r.occupancy, 
          r.status from room r join room_category c on r.category_id = c.id
          where r.occupancy = 'Vacant' order by r.number", $con) 
         or die("Cannot execute SQL query " . mysql_error());
         echo "<option></option>";
         while($row = mysql_fetch_assoc($result)) { 
           echo "<option value='{$row['number']} {$row['name']}'>";
           echo $row['number'] ."&nbsp;" .  $row['name'] . "&nbsp;"
           . $row['occupancy'] . "&nbsp;" .  $row['status'] . "</option>";
         }
       }
       echo "
          </select>
         </td>
	</tr>
       ";
      ?>
      <tr>
       <td colspan='3'>
	 <table>
          <tr>
	   <td>Title</td>
           <td>
            <input type="text" id="t" name="t" value="<?php echo $title;?>">
           </td>
           <td>Firstname</td>
           <td>
            <input type="text" id="f" name="f" 
             value="<?php echo $firstname; ?>"></td>
           <td>Lastname</td><td><input type="text" id="l" name="l" 
             value="<?php echo $lastname; ?>"></td>
	  </tr>
         </table>
	</td>
       </tr> 
       <tr><td>Phone</td><td><input type="text" id="p" name="p" 
        value="<?php echo $phone; ?>"></td></tr> 
       <tr>
        <td>Sex</td>
        <td>
	 <select id="sx" name="sx">
          <option <? if($sex =='Male') echo 'Selected';?>>Male</option> 
	  <option <? if($sex =='Female') echo ' Selected';?>>Female</option>
	 </select>
        </td>
       </tr>
       <tr>
        <td>Address</td>
	<td>
         <textarea rows="5" cols="50" id="a" name="a">
          <?php echo $address; ?>
         </textarea>
	</td>
       </tr>
       <tr>
      <td>Arrival Date</td>
      <td><input type='text' name='ad' 
       value='<?php echo $arrival_date; ?>' size='10' maxlength='10'>
       YYYY-MM-DD</td>
     </tr>
     <tr>
      <td>Departure Date</td> 
      <td><input type='text' name='dd' 
        value='<?php echo $departure_date; ?>' size='10' maxlength='10'>
        YYYY-MM-DD</td>
     </tr>
     <tr>
      <td>
	 <?php
	  if($_REQUEST['action'] == 'Edit') {
	    echo "<input name='action' type='submit' value='Update'>";
  	    echo "<input name='gid' type='hidden' value='{$_REQUEST['gid']}'>";
	  } else if ($_REQUEST['action'] == 'Add') {
            echo "<input name='action' type='submit' value='Add Guest'>";
	  }
	 ?>
       <input name="action" type="submit" value="Cancel">
      </td>
     </tr>
    </table>
   </form>
  <?
  exit;
  }
elseif (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Update')) {
  $sql="Update reservation set title='{$_REQUEST['t']}', 
   firstname='{$_REQUEST['f']}',lastname='{$_REQUEST['l']}', 
   phone='{$_REQUEST['p']}', sex='{$_REQUEST['sx']}', 
   address='{$_REQUEST['a']}', arrival_date='{$_REQUEST['ad']}', 
   departure_date='{$_REQUEST['dd']}' where id={$_REQUEST['gid']}";
  mysql_query($sql, $con);

} elseif (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Delete')) {
   if(empty($_REQUEST['gid'])) {
     echo msg_box('Please choose a guest to delete', 'reservation.php', 
      'Back');
      exit;
   }
   $sql ="Delete from reservation where id={$_REQUEST['gid']}";
   mysql_query($sql, $con);
 } 
}
?>
 <table>
<?php
if ((isset($_REQUEST['action']) && ($_REQUEST['action'] != 'Print')) 
   || (!isset($_REQUEST['action']))) {
  ?>   
   <tr class='class1'>
    <td>
    <form name='form1' action="reservation.php" method="post">
     <select name='action' onChange='document.form1.submit();'>
      <option value=''>Choose option</option>
      <option value='Add'>Add</option>
      <option value='View'>View</option>
      <option value='Edit'>Edit</option>
      <option value='Delete'>Delete</option>
      <option value='Print'>Print</option>
     </select>
   </td>
   <td colspan='7'><h3>Reservation List</h3></td>
  </tr>
<?php  } 
  if (isset($_REQUEST['action']) || (!isset($_REQUEST['action']))) {
  ?>
  <tr>
    <th></th>
       <th>Room</th>
       <th>Title</th>
       <th>Firstname</th>
       <th>Lastname</th>
       <th>Phone</th>
       <th>Arrival</th>
       <th>Departure</th>
      </tr>

      <?
      $result = mysql_query("select * from reservation", $con);
      while($row = mysql_fetch_array($result)) {
        ?>
          <tr>
        <td><input type='radio' name='gid' value='<?=$row['id']?>'></td>
            <td><?=$row['room']?></td>
            <td><?=$row['title']?></td>
            <td><?=$row['firstname']?></td>
            <td><?=$row['lastname']?></td>
            <td><?=$row['phone']?></td>
            <td><?=$row['arrival_date']?></td>
            <td><?=$row['departure_date']?></td>
          </tr>
        <?
      }
      echo '</form></table>'; 
      main_footer();
 }
?>
