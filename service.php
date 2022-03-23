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
    print_header('Available Service(s)', 'service.php',  
      'Back to Services', $con);
} else {
    main_menu($_SESSION['uid'],
      $_SESSION['firstname'] . " " . $_SESSION['lastname'],
      $_SESSION['entity_id'], $_SESSION['shift'], $con);

  if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Add Service')) {
    if (empty($_REQUEST['n'])) {
       echo msg_box('Please enter a value for services', 
        'service.php?action=Add', 'Back To Service');
       exit;
    }
    if (empty($_REQUEST['d'])) {
       echo msg_box('Please choose a Department',
        'service.php?action=Add', 'Back To Service');
       exit;
    }
    $result = mysql_query("select * from service where name='" .
      $_REQUEST['n'] . "'", $con)
    or die("Cannot execute SQL @Service Insert1 " . mysql_error());
    if (mysql_num_rows($result) > 0) {
      echo msg_box('This Service name is taken. Please 
      choose another', 'service.php?action=Add', 'Back to Service');
      exit;
    } else {
      $result = mysql_query("insert into service(name, dept_id) 
      values('" . $_REQUEST['n'] . "', '" . $_REQUEST['d'] 
      . "')", $con)
       or die("Cannot execute SQL @Service Insert2" . mysql_error());
    } 
  } elseif (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Delete')) {
    #May be they should have been some check somewhere here before 
    #Deleting so as to be sure, deleting a service won't compromise
    #the whole system
    if (!isset($_REQUEST['sid'])) {
      echo msg_box('Please choose a service to delete', 
        'service.php', 'Back To Service');
     exit;
    }
    $result = mysql_query("DELETE FROM service where id=" 
         . $_REQUEST['sid']) 
    or die("Cannot execute SQL @Service Delete" . mysql_error()); 

  } elseif (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Add')) {
  ?>
    <table> 
     <tr class="class1">
      <td colspan="4"><h3>Add Service</h3></td>
     </tr>
     <form action="service.php" method="post">
     <tr><td>Department</td><td><select name="d">
   <?php
     $result = mysql_query("SELECT id, name from department")
      or die("Cannot execute SQL " . mysql_error());
     while ($row = mysql_fetch_array($result)) {
     ?>  
       <option value="<?=$row['id']?>"><?=$row['name']?></option>;
     <?php
     }
   ?>
    </select></td></tr>
    <tr><td>Name</td><td><input type="text" name="n"></td></tr>
     <tr>
      <td><input name="action" type="submit" value="Add Service">
          <input name='action' type="submit" value='Cancel'></td>
     </tr>
    </table>
  <?php
    exit;
  }
}
if ((isset($_REQUEST['action']) && ($_REQUEST['action'] != 'Print')) || (!isset($_REQUEST['action']))) {
?>
  <table>
   <tr class='class1'>
    <td>
     <form name='form1' action="service.php" method="post">
     <select name='action' onChange='document.form1.submit();'>
      <option value=''>Choose option</option>
      <option value='Add'>Add</option>
      <option value='Delete'>Delete</option>
      <option value='Print'>Print</option>
     </select>
   </td>
   <td></td>
  <td colspan='2'><h3>Service List</h3></td>
  </tr>

<?php
}
?>
  <tr class='class1'>
   <td></td>
   <td>Name</td>
   <td>Department</td>
  </tr>
<?php  
  $result = mysql_query("select s.id, s.name, d.name as 'name2' 
   from service s join
        department d on s.dept_id = d.id order by s.name asc", $con) 
   or die("Cannot execute SQL " . mysql_error());
  while ($row = mysql_fetch_array($result)) {
  ?>
    <tr>
     <td> <input type="radio" name="sid" value="<?=$row['id']?>"></td>
     <td><?=$row['name']?></td>
     <td><?=$row['name2']?></td>
    </tr>
  <?
  }
  echo "</form></table>";
  main_footer();
?>
