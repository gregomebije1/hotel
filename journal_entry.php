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
require_once "acc.inc";

$con = connect();

/*
if (!(user_type($_SESSION['uid'], 'Report Viewer', $con)
 || user_type($_SESSION['uid'], 'Accountant', $con)
 || user_type($_SESSION['uid'], 'Admin', $con)
  )){
  main_menu($_SESSION['uid'],
    $_SESSION['first_name'] . " " . $_SESSION['last_name'], $con);
  echo msg_box('Access Denied!', 'index.php?action=logout', 'Continue');
  exit;
}
*/

main_menu($_SESSION['uid'],
  $_SESSION['firstname'] . " " . $_SESSION['lastname'], '1', '', $con);

if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Post')) {
  if ((empty($_REQUEST['desc']))) {
    echo msg_box('Please enter description', 'journal_entry.php', 'Back');
	exit;
  } 
  /*
  if (!check_date($_REQUEST['day'], $_REQUEST['month'], $_REQUEST['year'])) {
    echo msg_box("<table><tr><td>Please choose the correct date</td>
     </tr></table>");
    exit;
  }
  
  $date = make_date($_REQUEST['year'], $_REQUEST['month'], $_REQUEST['day']);
  */
  if (empty($_REQUEST['date'])) {
    echo msg_box("<table><tr><td>Please enter the correct date</td>
     </tr></table>");
    exit;
  }
  $date = $_REQUEST['date'];

  $x = 0;
  $d = 0;
  $c = 0; 
  for ($i = 0; $i <= 5; $i++) {
    $amt = "amt" . $i;
    $t_type = "t_type" .$i;
    $a_id = "a_id" . $i;
    
    if ((empty($_REQUEST[$amt]) || (!is_numeric($_REQUEST[$amt])))  
       && ($i == 0)) {
      echo msg_box("Please enter correct amount", 'journal_entry.php', 
      'Back');
      exit;
    }
    //Check for type and account ID
    //Make sure amoount is a number

    #The total amount of Debits/Credits
    if ($_REQUEST[$t_type] == 'Debit') {
      $d += $_REQUEST[$amt];
    } elseif ($_REQUEST[$t_type] == 'Credit') {
      $c += $_REQUEST[$amt];
    }

    #store journal entry details
    if (!empty($_REQUEST[$amt])) { 
      $j[] = $_REQUEST[$t_type];
      $j[] = $_REQUEST[$a_id];
      $j[] = $_REQUEST[$amt];
    }
  }
  if ($d != $c) {
    echo msg_box("Error: Your debit entries are not equal to
     your credit entries", 'journal_entry.php', 'Back');
    exit;
  }
  #Todo: Resolve the following:
  # Debit  Cash 20,000
  # Credit Cash 20,000

  ?>
    <table width="100%" border="1" style="text-align:left">
     <tr><td colspan='5' class='class1' style='text-align:center;'>
      <h3>Journal Entries</h3></td></tr>
     <tr> 
      <th>Type</th>
      <th>Account</th>
      <th>Date</th>
      <th>Description</th>
      <th>Amount</th>
     </tr>
  <?
  $log = "journal"; 
  for ($i = 0; $i < count($j); $i += 3) {
    echo "
       <tr>
        <td>{$j[$i]}</td>
        <td>" . get_acc_name($j[$i+1], $con) . "</td>
        <td>$date</td>
        <td>{$_REQUEST['desc']}</td>
        <td>{$j[$i+2]}</td>
       </tr>";
    $id = j_entry($j[$i],"1",$j[$i+1],$date, $_REQUEST["desc"], $j[$i+2], $con);
    $log .= " $id";
  }
  $log .= "|";
  audit_trail($_SESSION['uid'],
       "Journal Entry: {$_REQUEST['desc']} ", $log, $con);
  echo "<tr><td>&nbsp;</td></tr></table>";
  echo msg_box('Successfully posted', 'journal_entry.php', 'Continue');
  exit;
}
?>
   <table> 
    <tr class="class1">
     <td colspan="4"><h3>Journal Entry</h3></td>
    </tr>
   </table>
   <form action="journal_entry.php" method="post">
   <table>
    <tr>
     <td>Date:</td>
     <td><input type='text' name='date' value='<?php echo date('Y-m-d');?>'
      maxlength='10' size='10'></td>
    </tr>         
    <tr>
     <td>Description:</td>
     <td><input type="text" size="50" name="desc"></td>
    </tr>
   </table>
   <table>
   <?php
    for ($x = 0; $x <= 5; $x++) {
      $result = mysql_query("select * from account",  
      $con);
      echo "<tr><td><select name='a_id$x'>";
      while($row = mysql_fetch_array($result)) { 
        echo "<option value='{$row['id']}'>{$row['name']}</option>";
      }
      echo "
       </select>
       <select name='t_type{$x}'>
        <option >Debit</option>
        <option>Credit</option>
       </select>
       <input type='text' name='amt{$x}'> </td></tr>";
    }
    ?>
    <tr><td>&nbsp;</td></tr>
    <tr>
     <td>
      <input type="submit" name="action" value="Post">
      <input type="Submit" name='action' value='Cancel'>
      </form>
     </td>
    </tr>
   </table>
<? main_footer(); ?>
