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
    print_header('Available Department(s)', 'department.php',  
      'Back to Department', $con);
} else {
  require_once "library/main_menu.inc";
}
if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Delete')) {  
  check($_REQUEST['id'], 'Please choose a Department', 'department.php', 'Back');
  	 
  echo msg_box("Are you sure you want to delete " . 
	  get_value('department', 'name', 'id', $_REQUEST['id'], $con) . "?", 
	  "department.php?action=confirm_delete&id={$_REQUEST['id']}", 
	 'Continue to Delete');
  exit;
} else if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'confirm_delete')) {
    check($_REQUEST['id'],'Please choose a Department', 'department.php');

	$result = mysql_query("select name from department where id=".$_REQUEST['id'], $con);
	$row = mysql_fetch_array($result);
	$aid = get_acc_id($row['name'] . ' Sales', $con);
	 
    mysql_query("DELETE FROM department where id=" . $_REQUEST['id'], $con) or die(mysql_error()); 	
	mysql_query("DELETE FROM account where id=$aid", $con) or die(mysql_error());
	mysql_query("delete from journal where acc_id=$aid", $con)or die(mysql_error());
 
} else if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Insert')) {  
  check($_REQUEST['name'],'Please enter Department Name', 'department.php?action=Add');
  
  $sql="select * from department where name='{$_REQUEST['name']}'";
  if (mysql_num_rows(mysql_query($sql)) > 0) {
    echo msg_box("{$_REQUEST['number']} already exists in the database. 
     Please enter another Name", 'department.php?action=Add', 'Back');
    exit;
  }
  
  $acc_id = add_account($_REQUEST['name'] . " Sales", '', '',INCOME, 1, date('Y-m-d'), 0, 0, $con);
  //n, $c, $d, $t, $entity_id, $date, $parent, $children, $con
  if (!is_numeric($acc_id)) {
    msg_box($acc_id, 'department.php?action=Add', 'Back');
    exit;
  }
  $sql = gen_insert_sql('department', array(), $con);
  mysql_query($sql, $con) or die(mysql_error());
  
} else if (isset($_REQUEST['action']) && 
  (($_REQUEST['action'] == 'Add') || ($_REQUEST['action'] == 'Edit'))) {

  if (($_REQUEST['action'] == 'Edit') && (!isset($_REQUEST['id']))){
    echo msg_box('Please choose a Department to Edit', 'department.php', 'Back');
    exit;
  }  
  if (($_REQUEST['action'] == 'Edit') && isset($_REQUEST['id'])) 
    $id = $_REQUEST['id'];
  else
    $id = 0;

  $sql = "select * from department where id=$id";
  $result = mysql_query($sql) or die(mysql_error());
  $row = mysql_fetch_array($result);
  
  $action = ($_REQUEST['action'] == 'Edit') ? 'Delete' : $_REQUEST['action'];
  $skip = array("id");
  generate_form($action,'department.php',$id,'department', $row, $skip, "", "", $con);   
  exit;   
}
?>
<div class='class1'>
<?php
  if(isset($_REQUEST['action']) && ($_REQUEST['action'] != 'Print')
   || (!isset($_REQUEST['action']))) {
      echo "<a href='department.php?action=Add'>Add</a>  |
	   <a href='department.php?action=Print'>Print</a>";
  }
?>
 <h3 class='sstyle1'>Department</h3>
</div>
<table class='tablesorter'>

<?php
  $sql = "select * from department order by name asc";
  gen_list('department', 'department.php', 'name', array('id'), $sql, $con);
?>

</table>

<?php 
 require_once "library/tablesorter_footer.inc"; 
 main_footer();
?>
