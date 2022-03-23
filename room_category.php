<?
session_start();
if (!isset($_SESSION['uid'])) {
    header('Location: index.php');
    exit;
}
error_reporting(E_ALL);
require_once "hotel.inc";
$con = connect();

if(isset($_REQUEST['action']) && ($_REQUEST['action'] =="Print")) {
    print_header('Available Room Categories', 'room_category.php',  
      'Back to Room Category', $con);
} else {
    //Include Main menu
    require_once "library/main_menu.inc";
}
if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Delete')) {
  
  check($_REQUEST['id'], 'Please choose a Room Category', 'room_category.php', 'Back');
  	 
  $sql="select * from room where room_category_id={$_REQUEST['id']} and occupancy = 'Occupied'";
  $result = mysql_query($sql, $con) or die(mysql_error());
  if (mysql_num_rows($result) > 0) {
    echo msg_box("Cannot delete Room category because 
        there are still occupied rooms under this Room Category", 'room_category.php', 'OK');
    exit;
  } 
  echo msg_box("Are you sure you want to delete " . 
	  get_value('room_category', 'name', 'id', $_REQUEST['id'], $con) . "?", 
	  "room_category.php?action=confirm_delete&id={$_REQUEST['id']}", 
	 'Continue to Delete');
  exit;
} else if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'confirm_delete')) {

  check($_REQUEST['id'], 'Please choose a Room Category', 'room_category.php', 'Back');
  
  #All the rooms under this category is either Vacant 
  #or they don't exist
  #Delete all the rooms under this category
  mysql_query("DELETE from room where room_category_id={$_REQUEST['id']}") or die(mysql_error());
  mysql_query("DELETE FROM room_category where id={$_REQUEST['id']}") or die(mysql_error()); 
}
else if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Update')) {

  check($_REQUEST['name'], 'Please enter Category Name', 'room_cateogry.php?action=Add');
  if (!is_numeric($_REQUEST['rate']) || (!is_numeric($_REQUEST['deposit']))){
    echo msg_box('Please enter numeric values for Rate/Deposit', 
      "room_category.php?action=Edit&id={$_REQUEST['id']}", 'Back');
    exit;
  }

  $sql = gen_update_sql('room_category', $_REQUEST['id'], array() ,$con);
  mysql_query($sql, $con) or die(mysql_error());
}
if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Insert')) {
    
  check($_REQUEST['name'],'Please enter Room Category Name', 'room_category.php?action=Add');
  check($_REQUEST['rate'],'Please enter Room Rate', 'room_category.php?action=Add');
  check($_REQUEST['deposit'],'Please enter Room Deposit', 'room_category.php?action=Add');
  if (!is_numeric($_REQUEST['rate']) || (!is_numeric($_REQUEST['deposit']))){
    echo msg_box('Please enter numeric values for Rate/Deposit', 
      'room_category.php?action=Add Category', 'Back To Room Category');
    exit;
  }

  $sql="select * from room_category where name='{$_REQUEST['name']}'";
  if (mysql_num_rows(mysql_query($sql)) > 0) {
    echo msg_box("{$_REQUEST['name']} already exists in the database. 
     Please enter another name", 'room_category.php?action=Add', 'Back');
    exit;
  }
  $sql = gen_insert_sql('room_category', array(), $con);
  mysql_query($sql, $con) or die(mysql_error());
} else if (isset($_REQUEST['action']) && 
  (($_REQUEST['action'] == 'Add') || ($_REQUEST['action'] == 'Edit'))) {

  if (($_REQUEST['action'] == 'Edit') && (!isset($_REQUEST['id']))){
    echo msg_box('Please choose a Room Catgory to Edit', 'room_category.php', 'Back');
    exit;
  }
  
  if (($_REQUEST['action'] == 'Edit') && isset($_REQUEST['id'])) 
    $id = $_REQUEST['id'];
  else
    $id = 0;
	
  //Edit only when a room that has such room category is vacant 
  $sql="select * from room where room_category_id = $id and occupancy = 'Occupied'"; 
  $result = mysql_query($sql, $con)or die(mysql_error());
  if (mysql_num_rows($result) > 0) {
    echo msg_box("Cannot edit Room Category because 
      <br />rooms of such category(s) are still occupied", 'room_category.php', 
        'Back To Room Category');
    exit;
  }
  	
  $sql = "select * from room_category where id=$id";
  $result = mysql_query($sql) or die(mysql_error());
  $row = mysql_fetch_array($result);
  
  $skip = array("id");
  generate_form($_REQUEST['action'],'room_category.php',$id,'room_category', $row, $skip, "", "", $con);   
  exit;   
}
?>
<div class='class1'>
<?php
  if(isset($_REQUEST['action']) && ($_REQUEST['action'] != 'Print')
   || (!isset($_REQUEST['action']))) {
      echo "<a href='room_category.php?action=Add'>Add</a>  |
	   <a href='room_category.php?action=Print'>Print</a>";
  }
?>
 <h3 class='sstyle1'>Room Category</h3>
</div>
<table class='tablesorter'>

<?php
  $skip = array('id');
  $sql = "select id, name, rate, deposit from room_category order by id asc";
  gen_list('room_category', 'room_category.php', 'name', $skip, $sql, $con);
?>

</table>

<?php 
 require_once "library/tablesorter_footer.inc"; 
 main_footer();
?>