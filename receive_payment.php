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
  $aid = "";
  if (empty($_REQUEST['whom'])) {
    echo msg_box('Who is this transaction for?',
        'receive_payment.php', 'Back');
    exit;
  }
  if(($_REQUEST['whom'] == 'guest') && (empty($_REQUEST['rid']))) {
    echo msg_box('Please choose the Guest making this payment',
        'receive_payment.php', 'Back');
       exit;
  }
  if(($_REQUEST['whom'] == 'walkin') && (empty($_REQUEST['c_name']))) {
    echo msg_box("Please enter the walkin customer's name",
        'receive_payment.php', 'Back');
       exit;
  }
  if (empty($_REQUEST['dept'])){
    echo msg_box('Please choose a department',
        'receive_payment.php', 'Back');
       exit;
  }
  if(empty($_REQUEST['t_type'])) {
    echo msg_box('Please choose a transaction type', 'receive_payment.php', 'Back');
	exit;
  }
  if(empty($_REQUEST['date'])) {
    echo msg_box('Please enter correct date', 'receive_payment.php', 'Back');
	exit;
  }
  if(($_REQUEST['whom'] == 'walkin') && (get_dname($_REQUEST['dept'], $con) == 'Room')) {
    echo msg_box('A walkin customer cannot pay for any transaction concerning a Room',
	'receive_payment.php', 'Back');
      exit;
  }
  if(($_REQUEST['whom'] == 'walkin') && ($_REQUEST['t_type'] == 'previous')){
    echo msg_box('A walkin customer cannot pay for previous accrued  
     debt', 'receive_payment.php', 'Back');
    exit;
  }
  if((get_dname($_REQUEST['dept'], $con) != 'Room') && empty($_REQUEST['t_type'])) {
    echo msg_box('Please choose a transaction type', 
      'receive_payment.php', 'Back');
    exit;
  }
  $date = $_REQUEST['date'];
  if (empty($_REQUEST['amt'])) {
    echo msg_box('Please enter a correct value for amount', 
      'receive_payment.php', 'Back');
      exit;
  }
  if (($_REQUEST['whom'] == 'guest')  && ($_REQUEST['t_type'] == 'previous')) {
    $result = mysql_query("SELECT acc_id from room where id="
     . $_REQUEST['rid'], $con);
    $row = mysql_fetch_array($result);
    $aid = $row['acc_id'];
 
    ###GUEST IS PAYING FOR PREVIOUS DEBT OWING TO THE PARTICULAR DEPARTMENT####
    #####RECORD AS CASH RECEIVED FOR THAT DEPARTMENT#####
    if (get_bill_to($_REQUEST['rid'], $con) == 'individual') {
      $guest_name = get_unique_guest_name($_REQUEST['rid'], $con);
    } else {
      $guest_name = get_unique_group_name($_REQUEST['rid'], $con);
    }  

    $journal_log="journal"; 
    #Credit Debtor  
    $id = j_entry('Credit','1',$aid, $date, $_REQUEST['td'], 
      $_REQUEST['amt'], $con);
    $journal_log="$journal_log $id";

    #Debit Cash
    $id = j_entry('Debit', '1', get_acc_id('Cash', $con), $date,  
      $_REQUEST['td'], $_REQUEST['amt'], $con);
    $journal_log="$journal_log $id";

    $sales_log="sales"; 
    $id = add_sales($date, $_SESSION['shift'],get_user($_SESSION['uid'], $con), 
      $guest_name, $_REQUEST['doc_number'],  get_room($_REQUEST['rid'], $con), 
      "Cash Received: {$_REQUEST['td']}", get_dname($_REQUEST['dept'], $con), 
      'Cash Received', $_REQUEST['amt'], 
      '', '', '', '', '', $con);
    $sales_log="$sales_log $id";

    //Record in the log
    $log = "$sales_log|$journal_log";
    audit_trail($_SESSION['uid'],
      "$guest_name <br>
       Cash Received: {$_REQUEST['td']} <br>
       Receipt Number {$_REQUEST['doc_number']} <br>
       Amount: {$_REQUEST['amt']} ",
       $_SESSION['shift'], $log, $con);
  
 } elseif (($_REQUEST['whom'] == 'guest') && ($_REQUEST['t_type'] == 'current')) {
   ####GUEST IS PAYING FOR WHAT IT JUST BOUGHT####
   $guest_name = get_unique_guest_name($_REQUEST['rid'], $con);
   
 
    #Credit the department Sales 
    $journal_log = "journal";
    $id = j_entry('Credit', '1', 
      get_acc_id(get_dname($_REQUEST['dept'], $con). " Sales", $con), 
     $date, $_REQUEST['td'], $_REQUEST['amt'], $con);
    $journal_log = "$journal_log $id";
    
    #Debit Cash
    $id = j_entry('Debit', '1', get_acc_id('Cash', $con),  
      $date, $_REQUEST['td'], $_REQUEST['amt'], $con);
    $journal_log ="$journal_log $id";
    
   $id = add_sales($date, $_SESSION['shift'], get_user($_SESSION['uid'], $con), 
      $guest_name, $_REQUEST['doc_number'], get_room($_REQUEST['rid'], $con), 
      "Cash Sales: {$_REQUEST['td']}", get_dname($_REQUEST['dept'], $con), 
      'Cash Sales', $_REQUEST['amt'], 
       '', '', '', '', '', $con);
   $sales_log = "sales $id";

   //Record in the log
    $log = "$sales_log|$journal_log";
    audit_trail($_SESSION['uid'],
      "$guest_name <br>
       Cash Sales {$_REQUEST['td']}<br>
       Receipt Number {$_REQUEST['doc_number']} <br>
       Amount: {$_REQUEST['amt']} ",
       $_SESSION['shift'], $log, $con);
	   
 } elseif ($_REQUEST['whom'] == 'walkin') {
   $desc = $_REQUEST['c_name'] . ' ' . $_REQUEST['td'];
   
    $journal_log="journal";
    #Credit Department Sales with Amount
    $id = j_entry("Credit", '1', 
      get_acc_id(get_dname($_REQUEST['dept'], $con). " Sales", $con), 
      $date, $desc, $_REQUEST['amt'], $con);
    $journal_log="$journal_log $id";
	
   #Debit Cash with Amount
   
   $id = j_entry("Debit", '1', get_acc_id('Cash', $con), 
     $date, $desc, $_REQUEST['amt'], $con);
   $journal_log="$journal_log $id";
   
   #####RECORD AS CASH SALES FOR THAT DEPARTMENT#####   
   $id = add_sales($date, $_SESSION['shift'], get_user($_SESSION['uid'], $con),
       $_REQUEST['c_name'],  $_REQUEST['doc_number'], ' ',
       "Cash Sales: $desc", get_dname($_REQUEST['dept'], $con), 'Cash Sales', 
       $_REQUEST['amt'], '', '', '', '', '', $con);
   $sales = "sales $id";

  $log = "$sales_log|$journal_log";
  audit_trail($_SESSION['uid'],
      "Walkin Customer $guest_name <br>
       Cash Sales : $desc " . get_dname($_REQUEST['dept'], $con) . "<br>
       Receipt Number {$_REQUEST['doc_number']}<br>
       Amount: {$_REQUEST['amt']} ",
       $_SESSION['shift'], $log, $con);
  }	   
  echo msg_box('Successfully Posted', 'receive_payment.php', 
    'Back To Receive Payment');
  exit;
}
 ?>   
 <table>
  <tr class='class1'>
   <td colspan="4">
    <h3>Cash Transactions</h3>
   </td>
  </tr>
  <form name="myform" action="receive_payment.php" method="post">
     <!--A GUEST PAYMENT OR A WALK IN PAYMENT -->
     <tr>
       <td colspan="3">
        <fieldset>
         <legend>For Whom</legend>
          <table>
           <tr>
            <td>
             <input type="radio" name="whom" value="guest"
               onClick="display_element('rooms'); hide_element('c_name')">
             Guest
             <select id="rooms" name="rid" style="display:none;">
              <option></option>
  <? 
  $result = mysql_query("select r.id, r.number, g.title, g.firstname, 
          g.lastname from room r join guest g on r.id = g.room_id", $con);
  while($row = mysql_fetch_array($result)) {
    ?>
	<option value="<?=$row['id']?>">
	<?=$row['number']?>: 
	<?=$row['title']?>&nbsp;
	<?=$row['firstname']?>&nbsp;
    <?=$row['lastname']?>
	</option>
	<?
  }
  ?>
             </select>
            </td>
            <td>
             <input type="radio" name="whom" value="walkin"
              onClick="display_element('c_name'); hide_element('rooms')">
              Walk In
             <div style="display:none;" id="c_name">
              Customer's Name <input type="text" name="c_name" size='40'> 
             </div>
            </td>
           </tr>
          </table>
        </fieldset>
       </td>
      </tr>
     <!--WHAT ARE YOU PAYING FOR -->
     <tr>
       <td colspan="3">
        <fieldset>
         <legend>For What</legend>
          <table>
           <tr>
            <td>
             Department
              <select name="dept" id="dept">
               <option></option>
  <?
              //$result = mysql_query("Select id, name from department where name!='Room'");
			  $result = mysql_query("Select id, name from department");
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
  ?>
             </select> 
            </td>
           </tr>
          </table>
        </fieldset>
       </td>
      </tr>
     <!--PAYING CASH FOR CURRENT TRANSACTION OR SETTLING PREVIOUS DEBT -->
     <tr>
       <td colspan="3">
        <fieldset>
         <legend>Paying for:</legend>
          <table>
           <tr>
            <td>
             <input type="radio" name="t_type" value="current">
             Receiving Cash
            </td>
            <td>
             <input type="radio" name="t_type" value="previous">
             Paying previous debt
            </td>
           </tr>
          </table>
         </fieldset>
        </td>
       </tr>
      <tr>
       <td>
        <fieldset>
         <legend>Payment Details</legend>
         <table width="100%">
          <tr>
            <td>Date</td>
	        <td><input type='text' name='date' value='<?php echo date('Y-m-d'); ?>' size='10' maxlength='10'>YYYY-MM-DD</td>
     </tr>
		 <tr><td>Receipt Number</td><td><input type="text" name="doc_number"></td></tr>
         
          <tr>
           <td>Transaction Details</td>
           <td colspan="3">
            <textarea rows="5" cols="40" name="td"></textarea>
           </td>
          </tr>
		  <tr><td>Amount</td><td><input type="text" name="amt"></td></tr>
         </table>
        </fieldset>
       </td>
      </tr>
     <tr><td colspan="5"><hr /></td></tr>
      <tr><td><input name="action" type="submit" value="Post">
          <input name='action' type='submit' value='Cancel'>
      </td></tr>
     </table>
  <?
  main_footer();
?>
