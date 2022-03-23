<?
session_start();
error_reporting(E_ALL);
if (!isset($_SESSION['uid'])) {
    header('Location: index.php');
    exit;
}

require_once "hotel.inc";
$con = connect();
if(isset($_REQUEST['action']) && ($_REQUEST['action'] =="Print")) {
    print_header('Available Room(s)', 'room.php',  
      'Back to Room', $con);
} else {
  require_once "library/main_menu.inc";
}
if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Delete')) {  
  check($_REQUEST['id'], 'Please choose a Room', 'room.php', 'Back');
  	 
  $sql="select * from room where id={$_REQUEST['id']} and occupancy = 'Occupied'";
  $result = mysql_query($sql, $con) or die(mysql_error());
  if (mysql_num_rows($result) > 0) {
    echo msg_box("Cannot delete Room because still occupied", 'room.php', 'OK');
    exit;
  } 
  echo msg_box("Are you sure you want to delete " . 
	  get_value('room', 'number', 'id', $_REQUEST['id'], $con) . "?", 
	  "room.php?action=confirm_delete&id={$_REQUEST['id']}", 
	 'Continue to Delete');
  exit;
} else if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'confirm_delete')) {
  check($_REQUEST['id'], 'Please choose a Room', 'room.php', 'Back');
  
  mysql_query("DELETE from room where id={$_REQUEST['id']}") or die(mysql_error());
}
else if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Update')) {

  check($_REQUEST['number'], 'Please enter Room Name', 'room.php?action=Add');
  
  $sql = gen_update_sql('room', $_REQUEST['id'], array('picture1', 'picture2', 'account_id', 'occupancy') ,$con);
  mysql_query($sql, $con) or die(mysql_error());
}

if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Insert')) {
    
  check($_REQUEST['number'],'Please enter Room Number', 'room.php?action=Add');
  
  $sql="select * from room where number='{$_REQUEST['number']}'";
  if (mysql_num_rows(mysql_query($sql)) > 0) {
    echo msg_box("{$_REQUEST['number']} already exists in the database. 
     Please enter another number", 'room.php?action=Add', 'Back');
    exit;
  }
  $sql = gen_insert_sql('room', array('picture1', 'picture2', 'account_id', 'occupancy'), $con);
  mysql_query($sql, $con) or die(mysql_error());
  $id = mysql_insert_id();
  
  $sql="update room set occupancy='Vacant' where id={$id}";
  mysql_query($sql, $con) or die(mysql_error());
   
} else if (isset($_REQUEST['action']) && 
  (($_REQUEST['action'] == 'Add') || ($_REQUEST['action'] == 'Edit'))) {

  /*How do you change the name/category of the room when it is
  occupied by a guest.
  */
  
  if (($_REQUEST['action'] == 'Edit') && (!isset($_REQUEST['id']))){
    echo msg_box('Please choose a Room to Edit', 'room.php', 'Back');
    exit;
  }
  
  if (($_REQUEST['action'] == 'Edit') && isset($_REQUEST['id'])) 
    $id = $_REQUEST['id'];
  else
    $id = 0;
	
  //Edit only when a room is vacant 
  $sql="select * from room where id=$id and occupancy = 'Occupied'"; 
  $result = mysql_query($sql, $con)or die(mysql_error());
  if (mysql_num_rows($result) > 0) {
    echo msg_box("Cannot edit because Room still occupied", 'room.php', 'Back');
    exit;
  }
  $sql = "select * from room where id=$id";
  $result = mysql_query($sql) or die(mysql_error());
  $row = mysql_fetch_array($result);
  
  $skip = array("id", 'account_id', 'picture1', 'picture2', 'occupancy');
  generate_form($_REQUEST['action'],'room.php',$id,'room', $row, $skip, "", "", $con);   
  exit;   
}
?>
<div class='class1'>
<?php
  if(isset($_REQUEST['action']) && ($_REQUEST['action'] != 'Print')
   || (!isset($_REQUEST['action']))) {
      echo "<a href='room.php?action=Add'>Add</a>  |
	   <a href='room.php?action=Print'>Print</a>";
  }
?>
 <h3 class='sstyle1'>Room</h3>
</div>
<table class='tablesorter'>

<?php
  $skip = array('id', 'picture1', 'picture2', 'account_id', 'comment');
  $sql = "select * from room order by id asc";
  gen_list('room', 'room.php', 'number', $skip, $sql, $con);
?>

</table>

<?php 
 require_once "library/tablesorter_footer.inc"; 
 main_footer();
?>
