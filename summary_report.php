<?
session_start();

if (!isset($_SESSION['uid'])) {
    header('Location: index.php');
    exit;
}
error_reporting(E_ALL);

require_once "ui.inc";
require_once "hotel.inc";
require_once "util.inc";

$con = connect();

  if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Process')) {
    print_header('Summary Report', 'summary_report.php',  
      'Back Main Menu', $con);

    if (empty($_REQUEST['type'])) {
	  echo msg_box("Please choose a department", 'summary_report.php', 'Back');
	  exit;
	}
	if (empty($_REQUEST['sd']) || empty($_REQUEST['ed'])) {
	  echo msg_box("Please enter correct Start and End dates", 'summary_report.php', 'Back');
	  exit;
	}
	$sddate = $_REQUEST['sd'];
	$eddate = $_REQUEST['ed'];
	
    ?>
    <table>
     <tr><td colspan='6'><h3><? echo get_dname($_REQUEST['type'], $con)?>  Report</h3></td></tr>
   <?
    if (get_dname($_REQUEST['type'], $con) == 'Room') {
	    echo "<tr><td colspan='6'><h3>Room Category: {$_REQUEST['room_category']}</h3></td></tr>";
	}
    summary_report(get_dname($_REQUEST['type'], $con), $sddate, $eddate, $_REQUEST['room_category'], $con); 
    exit;
  }    
 if ((isset($_REQUEST['action']) && ($_REQUEST['action'] != 'Print')) 
   ||(!isset($_REQUEST['action']))) {
    main_menu($_SESSION['uid'],
      $_SESSION['firstname'] . " " . $_SESSION['lastname'],
      $_SESSION['entity_id'], $_SESSION['shift'], $con);
  ?>
    <table> 
     <tr class="class1">
      <td colspan="4"><h3>Summary Report</h3></td>
     </tr>
     <form action="summary_report.php" method="post" name='myform'>
	 <tr>
      <td>Department</td>
       <td>
        <select id='type' name="type" onChange="
		 var ch = document.myform.type.options[document.myform.type.selectedIndex].text;
		 if (ch == 'Room')
		   display_element('room_category_type');
		  else 
		   hide_element('room_category_type');
		   ">
       	 <option></option> 
		 <?
		 
         $result = mysql_query("select id, name from department");
         while($row = mysql_fetch_array($result)) {
             if ($row['name'] == 'Restaurant') {
		      if (user_type($_SESSION['uid'], 'Manager', $con) 
				  || user_type($_SESSION['uid'], 'Accountant', $con)
				  || user_type($_SESSION['uid'], 'Restaurant Cashier', $con)) {
			    echo "<option value='".$row['id']."'>".$row['name']."</option>";
			  } else {
			    continue;
		      }
			} else {
			  if (user_type($_SESSION['uid'], 'Manager', $con) 
		        || user_type($_SESSION['uid'], 'Accountant', $con)
			    || user_type($_SESSION['uid'], 'Front Office Cashier', $con)) {
				echo "<option value='".$row['id']."'>".$row['name']."</option>";
			  } else {
			    continue;
			  }
		    }
         }
		 
		 ?>
        </select>
       </td>
      </tr>
	  <tr id='room_category_type' style='display:none;'>
	   <td>Room Category</td>
	   <td>
        <select name="room_category">
		 <option value='All'>All</option>
		 <?
         $result = mysql_query("select id, name from room_category", $con);
         while($row = mysql_fetch_array($result)) {
           echo "<option value='{$row['name']}'>{$row['name']}</option>";
         }
		 ?>
        </select>
       </td>
	  </tr>
     <tr><td>Start Date</td>
      <td><input type='text' name='sd' value='<?php echo date('Y-m-d'); ?>' size='10' maxlength='10'>YYYY-MM-DD</td>
     </tr>
     <tr> 
      <td>End Date</td>
      <td><input type='text' name='ed' value='<?php echo date('Y-m-d'); ?>' size='10' maxlength='10'>YYYY-MM-DD</td>
     </tr>
      <tr><td><input name="action" type="submit" value="Process">
          <input name='action' type='submit' value='Cancel'>
      </td></tr>
     </table>
<?
  main_footer();
}
