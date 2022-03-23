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
    print_header('List of users', 'users.php',  
      'Back to Users', $con);
} else {
  require_once "library/main_menu.inc";
}
if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Insert')) {
  check($_REQUEST['username'], 'Please choose enter username', 'users.php?action=Add', 'Back');
  check($_REQUEST['password1'], 'Please choose enter password', 'users.php?action=Add', 'Back');
  check($_REQUEST['password1'], 'Please choose Re-enter password', 'users.php?action=Add', 'Back');
  
  if ($_REQUEST['password1'] != $_REQUEST['password2']) {
    echo msg_box("Passwords are not equal.<br>Please enter equal passwords",
	 'users.php?action=Add', 'Back');
	exit;
  }
  
  $sql="insert into user(username, password) value ('{$_REQUEST['username']}', 
   sha1('{$_REQUEST['password1']}'))";
  mysql_query($sql, $con) or die(mysql_error());
  $uid = mysql_insert_id();
  
  $sql="insert into user_permissions(uid, pid) value($uid, {$_REQUEST['pid']})";
  mysql_query($sql, $con) or die(mysql_error());
} elseif (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Update')) {
  
  check($_REQUEST['username'], 'Please choose enter username', 'users.php?action=Add', 'Back');
  check($_REQUEST['password1'], 'Please choose enter password', 'users.php?action=Add', 'Back');
  check($_REQUEST['password1'], 'Please choose Re-enter password', 'users.php?action=Add', 'Back');
  
  if ($_REQUEST['password1'] != $_REQUEST['password2']) {
    echo msg_box("Passwords are not equal.<br>Please enter equal passwords",
	 "users.php?action=Edit?id={$_REQUEST['id']}", 'Back');
	exit;
  }
  
  $sql = "update user set username = '{$_REQUEST['username']}',
      password =sha1('{$_REQUEST['password1']}') where id={$_REQUEST['id']}";
  mysql_query($sql, $con) or die(mysql_error());
  
  $sql="update user_permissions set pid={$_REQUEST['pid']}
    where uid ={$_REQUEST['id']}";
  mysql_query($sql, $con) or die(mysql_error());
 } elseif (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Delete')) {
    if (empty($_REQUEST['uid'])) {
	  echo msg_box('Please choose a user', 'users.php', 'Back');
	  exit;
	} else {
       mysql_query("DELETE FROM user where id=" . $_REQUEST['uid']);
	   mysql_query("DELETE FROM user_permissions where uid=". $_REQUEST['uid']);
	   echo msg_box('User has been deleted', 'users.php', 'Back to users');
	   exit;
	 }
} 
else if (isset($_REQUEST['action']) && 
  (($_REQUEST['action'] == 'Add') || ($_REQUEST['action'] == 'Edit'))) {

  if (($_REQUEST['action'] == 'Edit') && (!isset($_REQUEST['id']))){
    echo msg_box('Please choose a User to edit', 'users.php', 'Back');
    exit;
  }  
  if (($_REQUEST['action'] == 'Edit') && isset($_REQUEST['id'])) 
    $id = $_REQUEST['id'];
  else
    $id = 0;

  $sql="select u.id, pid as 'pid', u.username, p.name as 'pname'  from 
   user u left join(user_permissions up, permissions p) on 
   (u.id = up.uid and up.pid = p.id) where u.id=$id";
  $result = mysql_query($sql, $con) or die(mysql_error());
  $row = mysql_fetch_array($result);
  ?>
   <table> 
    <tr class='class1'>
	 <td colspan='3'><h3><?php echo $_REQUEST['action'];?> User</h3></td></tr>
      <form action="users.php" method="post">
    <tr><td>Username</td><td><input type="text" name="username" value="<?php echo $row['username'];?>"></td></tr>
    <tr><td>Password</td><td><input type="password" name="password1"></td></tr>
    <tr><td>Retype Password</td>
    <td><input type="password" name="password2"></td></tr>
    <tr>
	 <td>Permissions</td><td>
	  <?
	  $sql="select * from permissions";
	  $arr = my_query($sql, "id", "name");
	  echo selectfield($arr, 'pid', $row['id']);
	  ?>
	 </td>
	</tr>
    <tr>
	<?php
	 if ($_REQUEST['action'] == 'Edit') {
	   echo "<input type='hidden' name='id' value='$id'>
	   <td><input name='action' type='submit' value='Update'></td>";
	  } else 
	   echo "<td><input name='action' type='submit' value='Insert'></td>";
	  ?>
     <td><input name="action" type="submit" value="Cancel"></td>
	</tr>
   </table>
  <?php
    exit;
}
?>
<div class='class1'>
<?php
  if(isset($_REQUEST['action']) && ($_REQUEST['action'] != 'Print')
   || (!isset($_REQUEST['action']))) {
      echo "<a href='users.php?action=Add'>Add</a>  |
	   <a href='users.php?action=Print'>Print</a>";
  }
?>
 <h3 class='sstyle1'>User</h3>
</div>
<table class='tablesorter'>
 <thead>
  <tr><th>Username</th><th>Permission</th></tr>
 </thead>
 <tbody>
<?php
  $sql="select u.id, u.username, p.name as 'pname' from user u left join(user_permissions up, permissions p) 
     on (u.id = up.uid and up.pid = p.id)";
  $result = mysql_query($sql, $con) or die(mysql_error());
  while($row = mysql_fetch_array($result)) {
    echo "<tr>
	      <td><a href='users.php?action=Edit&id={$row['id']}'>{$row['username']}</a></td>
	      <td>{$row['pname']}</td>
		 </tr>";
  }
?>
 </tbody>
</table>
<?php 
 require_once "library/tablesorter_footer.inc"; 
 main_footer();
?>