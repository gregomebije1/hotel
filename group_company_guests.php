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
    print_header('Available Group/Company(s)', 'group_company_guests.php',  
      'Back to Group/Company', $con);
} else {
    main_menu($_SESSION['uid'],
      $_SESSION['firstname'] . " " . $_SESSION['lastname'],
      $_SESSION['entity_id'], $_SESSION['shift'], $con);

  if (isset($_REQUEST['action']) &&($_REQUEST['action']=='Add Group/Company')){
    if (empty($_REQUEST['n']))  {
       echo msg_box('Please enter a value for Group/Company', 
        'group_company_guests.php?action=Add', 'Back To Group Company');
       exit;
    }
	/*
	if (!check_date($_REQUEST['adday'], $_REQUEST['admonth'], 
     $_REQUEST['adyear'])){
     echo msg_box('Please choose correct arrival date', 
       'guest.php?action=Add', 'Back');
     exit;
   }
    $addate = make_date($_REQUEST['adyear'], $_REQUEST['admonth'], 
     $_REQUEST['adday']);
	*/
	if(empty($_REQUEST['ad'])) {
	  echo msg_box('Please enter value of arrival date', 'group_company_guests.php?action=Add', 'Back');
	  exit;
	}
	$addate = $_REQUEST['ad'];
	
    $result = add_group_company_guests($_REQUEST['n'], $_REQUEST['a'],  
      $_REQUEST['p'], $addate, $con);
    if ($result  != 1) { 
      echo msg_box($result, 'group_company_guests.php?action=Add', 
        'Back To Group/Company');
      exit;
    }
  }
  elseif (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Edit')) {
    if(!isset($_REQUEST['gcid'])) {
      echo msg_box("Please choose a Group/Company",
       'group_company_guests.php', 'Back'); 
      exit;
    }
    $result = mysql_query("SELECT name, address, phone 
      from group_company_guests where id =". $_REQUEST['gcid'], $con)
     or die("Cannot execute SQL @Edit Group/Company " . mysql_error());
    $row = mysql_fetch_array($result); 
    ?>
    <form action='group_company_guests.php' method='post'>
     <table>
      <tr><td>Name</td><td><?=$row['name']?></td></tr>
      <tr><td>Address</td>
       <td><textarea rows='10' cols='20' name='a'> 
        <?=$row['address']?></textarea></td></tr>
      <tr>
       <td>Phone</td>
       <td><input type='text' name='p' value='<?=$row['phone']?>'></td></tr>
      <tr>
       <td><input type='submit' name='action' value='Update'></td>
       <td><input type='submit' name='action' value='Cancel'></button>
       <input type='hidden' name='gcid' value='<?=$_REQUEST['gcid']?>'>
       <input type='hidden' name='n' value='<?=$row['name']?>'>
       </td>
      </tr>
     </table>
    </form>
<?  exit;
  }
  elseif (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Update')) {
    mysql_query("update group_company_guests set
    address = '" . $_REQUEST['a']  . "', phone = '" . $_REQUEST['p'] 
    . "' where id =" . $_REQUEST['gcid'], $con)
    or die("Cannot execute SQL @Group/Company Update " . mysql_query());

  } elseif (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Delete')) {
    #Delete only when there is no guest under this account 
    if(!isset($_REQUEST['gcid'])) {
      echo msg_box("Please choose a Group/Company",
       'group_company_guests.php', 'Back'); 
      exit;
    }

    if(mysql_num_rows(mysql_query("select * from guest where grp_cmp_id = " 
     . $_REQUEST['gcid'], $con)) > 0) {
      echo msg_box("Cannot delete Group/Company because there are still 
        guests registered under this account", 
        'group_company_guests.php', 'Back');
      exit;
    } else {
       #Todo: Make sure you also delete any account registered under this name
      $result = mysql_query("DELETE FROM group_company_guests where id=" 
       . $_REQUEST['gcid']) 
       or die("Cannot execute SQL @Group/Company Delete2" . mysql_error()); 
    }
  } elseif (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Add')) {
  ?>
    <table> 
     <tr class="class1">
      <td colspan="4"><h3>Add Group/Company</h3></td>
     </tr>
     <form action="group_company_guests.php" method="post">
     <tr><td>Name</td><td><input type="text" name="n"></td></tr>
     <tr><td>Address</td><td><input type="text" name="a"></td></tr>
     <tr><td>Phone</td><td><input type="text" name="p"></td></tr>
	 <tr>
      <td>Arrival Date</td>
      <!--<?php echo gen_date("ad"); ?>-->
	  <td><input type='text' name='ad' value='<?php echo date('Y-m-d'); ?>' size='10' maxlength='10'>YYYY-MM-DD</td>
     </tr>
     <tr>
      <td><input name="action" type="submit" value="Add Group/Company">
          <input name="action" type="submit" value="Cancel"> </td>
     </tr>
    </table>
  <?php
    exit;
  }
}
if ((isset($_REQUEST['action']) && ($_REQUEST['action'] != 'Print'))  ||(!isset($_REQUEST['action']))) {
?>
  <table>
   <tr class='class1'>
    <td>
    <form name='form1' action="group_company_guests.php" method="post">
     <select name='action' onChange='document.form1.submit();'>
      <option value=''>Choose option</option>
      <option value='Add'>Add</option>
      <option value='Edit'>Edit</option>
      <option value='Delete'>Delete</option>
      <option value='Print'>Print</option>
     </select>
   </td>
   <td colspan='3'><h3>Group/Company</h3></td>
  </tr>
   <?php
}
?>
  <tr class='class1'>
   <td></td>
   <td>Name</td>
   <td>Address</td>
   <td>Phone</td>
  </tr>
<?php  
  $result = mysql_query("select id, name, address, phone from 
    group_company_guests order by name asc", $con) 
  or die("Cannot execute SQL " . mysql_error());
  while ($row = mysql_fetch_array($result)) {
  ?>
    <tr>
     <td> <input type="radio" name="gcid" value="<?=$row['id']?>"></td>
     <td><?=$row['name']?></td>
     <td><?=$row['address']?></td>
     <td><?=$row['phone']?></td>
    </tr>
  <?
  }
  echo "</form></table>";
  main_footer();
?>
