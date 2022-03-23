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
main_menu($_SESSION['uid'], 
  $_SESSION['firstname'] . " " . $_SESSION['lastname'], 
  $_SESSION['entity_id'], $_SESSION['shift'], $con);

if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Post')) {
  ####All characters which have HTML character entity equivalents are translated into these entities
  ####Input: A 'quote' is <b>bold</b>
  ####Output: A &#039;quote&#039; is &lt;b&gt;bold&lt;/b&gt;
  foreach ($HTTP_POST_VARS as $key => $value) {
      $_REQUEST[$key] = htmlentities($value, ENT_QUOTES);
  } 
  if (empty($_REQUEST['date'])) {
    echo msg_box('Please choose a correct date', 'record_expenses.php', 'Back');
      exit;
   }
   $date = $_REQUEST['date'];
  if (empty($_REQUEST['rid'])) {
    echo msg_box('Please add a Room', 'record_expenses.php', 'Back');
    exit;
   }
   
   for ($i = 0; $i < 10; $i++) {
     $department = "department" . $i;
     $tran_details = "tran_details" . $i;
     $bill_no = "bill_no" . $i;
     $amount = "amount" . $i;

     if ((empty($_REQUEST[$tran_details]) 
      || (empty($_REQUEST[$amount]))) && ($i == 0)) {
       echo msg_box("Please enter transaction details with amount", 
         "record_expenses.php", "Back");
       exit;
     }   
     if(!empty($_REQUEST[$amount])) {
       if(empty($_REQUEST[$tran_details])) {
         echo msg_box('Please enter transaction details with amount', 
          'record_expenses.php', 'Back');
         exit;
       }
       $result = mysql_query("SELECT acc_id from room where 
       id=".$_REQUEST['rid'], $con); 
       $row = mysql_fetch_array($result);
       $aid = $row['acc_id'];

       $desc = $_REQUEST[$tran_details] . " " . $_REQUEST[$bill_no];  
       $sql="select name from department where id = ". $_REQUEST[$department];
       $result2 = mysql_query($sql, $con);
       $temp = mysql_fetch_array($result2);
	  
       if (get_bill_to($_REQUEST['rid'], $con) == 'individual') {
         $guest_name = get_unique_guest_name($_REQUEST['rid'], $con);
       } else {
         $guest_name = get_unique_group_name($_REQUEST['rid'], $con);
       }  
	
       #Debit Guest's Account
       $journal_log="journal";
       $id = j_entry("Debit",'1',$aid,$date,"$desc", $_REQUEST[$amount], $con);
       $journal_log = "$journal_log $id"; 

       #Credit the department income account for the service
       $id = j_entry("Credit", '1', 
         get_acc_id(get_dname($_REQUEST[$department], $con). " Sales", $con), 
        $date, "$desc", $_REQUEST[$amount], $con);
       $journal_log = "$journal_log $id";
		
      #####RECORD Transaction as Credit Sales#####
      $id = add_sales($date, $_SESSION['shift'], get_user($_SESSION['uid'], 
       $con),
       $guest_name, $_REQUEST[$bill_no], get_room($_REQUEST['rid'], $con), 
       "Credit Sales $desc", $temp['name'], 'Credit Sales', $_REQUEST[$amount],
       '', '', '', '', '', $con);
      $sales_log = "sales $id";

      $log = "$sales_log|$journal_log";
      audit_trail($_SESSION['uid'], 
       "$guest_name <br>
       Credit Sales: $desc <br>
       Receipt Number {$_REQUEST[$bill_no]} <br>
       Amount: {$_REQUEST[$amount]} ",
       $_SESSION['shift'], $log, $con);
  
    }
  }
  echo msg_box("Successfully posted", 'record_expenses.php', 'Back');
  exit;
}
?>
    <form action="record_expenses.php" method="post">
    <table width='100%'>
      <tr class='class1'>
       <td colspan="4"><h3>Record Expenses</h3></td>
      </tr>
      <tr>
       <td>
        <table width="100%">
         <tr>
          <td>Room
           <select name="rid">
<?
  $result = mysql_query("select r.id, r.number, g.title, 
  g.firstname, g.lastname from room r join guest g on g.room_id = r.id
   where occupancy = 'Occupied'", $con);
  while($rooms = mysql_fetch_array($result)) {
    echo "<option value=" . $rooms['id'] . ">" . 
     $rooms['number'].' '.$rooms['title'].' '.$rooms['firstname'].' '.$rooms['lastname']. ' </option>';
  }
  ?>
           </select>
          </td>
          <td>Date</td>
		  <td><input type='text' name='date' value='<?php echo date('Y-m-d'); ?>' size='10' maxlength='10'>YYYY-MM-DD</td>
  
         </tr>
        </table>
       </td>
      </tr>
      <tr><td colspan="3">&nbsp;</td></tr>
      <tr>
      <td>
       <table width='100%' cellspacing="0" cellpadding="0">
        <tr class='class1'>
         <td>Department</td>
         <td>Transaction Details</td>
         <td>Receipt No.</td>
         <td>Amount</td>
        </tr>

        <?
        for ($i = 0; $i < 10; $i++) {
          echo "<tr>";
          //$result = mysql_query("select id, name from department where name!='Room'", $con);
		  $result = mysql_query("select id, name from department", $con);
          echo " 
            <td>
             <select name='department$i'>";
          while($row = mysql_fetch_array($result)) {
	        if (user_type($_SESSION['uid'], 'Manager', $con) 
				  || user_type($_SESSION['uid'], 'Accountant', $con)
				  || user_type($_SESSION['uid'], 'Restaurant Cashier', $con)
				  || user_type($_SESSION['uid'], 'Front Office Cashier', $con)) {
			    echo "<option value='".$row['id']."'>".$row['name']."</option>";
			} else {
			    continue;
		    } 
          }
          echo " 
             </select>
            </td>
            <td><input type='text' name='tran_details$i'></td>
            <td><input type='text' name='bill_no$i'></td>
            <td><input type='text' name='amount$i'></td>
           </tr>
           ";
        }
        ?>
       </table>
      </td>
     </tr>
     <tr>
      <td>
       <input type="submit" name="action" value="Post">
       <input type="submit" name="action" value="Cancel">
      </form>
      </td>
     </tr>
    </table>
