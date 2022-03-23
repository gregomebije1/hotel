<?
session_start();
if (!isset($_SESSION['uid'])) {
  header('Location: index.php');
  exit;
}
error_reporting(E_ALL);
require_once "hotel.inc";

$con = connect();
$rate = 0;


if(isset($_REQUEST['action']) && ($_REQUEST['action'] =="Print")) { 
  print_header('List of Guests', 'guest.php', 'Back To Main Menu', $con);
} else {
  //Include Main menu
  require_once "library/main_menu.inc";
}  
###Please make sure required fields in tables are set
startup_checks(array('user', 'room_category', 'room', 'department',
'account', 'account_type'));
  

if (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Add Guest')) {
    if(empty($_REQUEST['rid'])) {
      echo msg_box('Please choose a room', 'guest.php?action=Add', 'Back');
      exit;
    }
    if(empty($_REQUEST['r_choice'])) {
      echo msg_box('Please choose a rate', 'guest.php?action=Add', 'Back');
      exit;
    }
    if ($_REQUEST['r_choice'] == 'rr') {
      if (empty($_REQUEST['rr'])) {
        echo msg_box('Cannot determine Room rate of room', 
         'guest.php?action=Add', 'Back');
        exit;
      } else {
        $rate = $_REQUEST['rr'];
      }
    }
    if ($_REQUEST['r_choice'] == 'sr') {
      if (empty($_REQUEST['sr'])) {
        echo msg_box('Please enter a correct numeric value for Special Rate',
          'guest.php?action=Add', 'Back');
        exit;
      } else {
        $rate = $_REQUEST['sr'];
      }
    }
    if (empty($_REQUEST['t']) || empty($_REQUEST['f']) || 
      empty($_REQUEST['l']) ) {
      echo msg_box('Please enter firstname and lastname', 
        'guest.php?action=Add', 'Back');
      exit;
    }
    if (empty($_REQUEST['sx'])) {
      echo msg_box('Please choose sex', 'guest.php?action=Add', 
       'Back to guest');
      exit;
    }
    if (empty($_REQUEST['b']) ) {
      echo msg_box('Please choose whome to Bill To',
       'guest.php?action=Add', 'Back');
      exit;
    }
    if (empty($_REQUEST['ad'])  || empty($_REQUEST['dd'])) {
      echo msg_box('Please enter arrival date and departure date', 
      'guest.php?action=Add', 'Back');
      exit;
    }
    $addate = $_REQUEST['ad'];
    $dddate = $_REQUEST['dd'];
	
    if (isset($_REQUEST['b']) && ($_REQUEST['b'] == 'GroupCompany')){
      if (empty($_REQUEST['group_company_guests'])){
        echo msg_box("Please choose a Group Company", 
         'guest.php?action=Add','Back');
	exit;
      }
    }
    if (empty($_REQUEST['d_nbr'])) {
      echo msg_box('Please enter a value for receipt number', 
       'guest.php?action=Add', 'Back');
      exit;
    }
    if (empty($_REQUEST['amt']) || (!is_numeric($_REQUEST['amt']))) {
      echo msg_box('Please enter a correct value for amount', 
       'guest.php?action=Add', 'Back');
      exit;
    }
    $gcname='';
    $gc_acc_id = '';
    if(!empty($_REQUEST['group_company_guests'])) {
      $result = mysql_query("SELECT a.name, a.id 
        from group_company_guests g 
	join account a on g.acc_id = a.id 
        where g.id={$_REQUEST['group_company_guests']}", $con);
      $row = mysql_fetch_array($result);
      $gcname = $row['name'];
      $gc_acc_id = $row['id'];
    }
    $result = mysql_query("SELECT * FROM guest WHERE
      firstname ='{$_REQUEST['f']}' and 
      lastname = '{$_REQUEST['l']}'", $con);
    if(mysql_num_rows($result) > 0) {
      echo msg_box("This Guest's firstname and lastname has already been taken. 
       Please choose another", 'guest.php', 'Back');
       exit;
    } else {
      $sql = "INSERT INTO guest (title, firstname, lastname, 
      phone, room_id, sex, address, bill_to, grp_cmp_id, 
      arrival_date, departure_date, doc_number)
      VALUES ('{$_REQUEST['t']}', '{$_REQUEST['f']}', '{$_REQUEST['l']}', 
       '{$_REQUEST['p']}', '{$_REQUEST['rid']}', '{$_REQUEST['sx']}', 
       '{$_REQUEST['a']}', '{$_REQUEST['b']}', 
       '{$_REQUEST['group_company_guests']}','$addate', '$dddate', 
       '{$_REQUEST['d_nbr']}')";

      ###Make sure there is Cash and Room Sales Account
      if (!account_exists('Room Sales', $con)) {
        echo msg_box("Room Sales account has not been created<br>
	 Please contact the vendor", 'guest.php', 'Back to Guests');
	exit;
      }
	  
      ####START TRANSACTION####
      mysql_query("start transaction", $con); //Is this how its done? 
      $result = mysql_query($sql, $con) or die(mysql_error());
      $id = mysql_insert_id();

      ####update room status####
      $sql="UPDATE room set occupancy = 'Occupied' where id={$_REQUEST['rid']}";
	  mysql_query($sql, $con);
      
      ####CREATE GUEST ACCOUNT #####
      $sql = "select number from room where id={$_REQUEST['rid']}";
      $result = mysql_query($sql, $con) or die(mysql_error());
      $r = mysql_fetch_array($result);

      ####creating the account name####
      $acc_name = $r['number'];
      if ($_REQUEST['b'] == 'individual') {
        $acc_name .= ": {$_REQUEST['t']} {$_REQUEST['f']} {$_REQUEST['l']}"; 
      } else {
        $acc_name = "$gcname"; 
      }
      if ($_REQUEST['b'] == 'individual') {
        //Open a new current asset (debtor) account
        $guest_acc_id = add_account($acc_name, '', '', OTHER_CURRENT_ASSETS, '', $addate, 0, 0, $con);
		   
      } else {
         //Determine group account id
         $guest_acc_id = $gc_acc_id; 
      }
	  
      ####Update accountID column in room table####
      $sql="update room set account_id=" . get_acc_id($acc_name, $con)
        . " where id = {$_REQUEST['rid']}";
	  mysql_query($sql, $con) or die(mysql_error());
	   
      ####Get a unique guest name
      if ($_REQUEST['b'] == 'individual') {
        $guest_name = get_unique_guest_name($_REQUEST['rid'], $con);
      } else {
        $guest_name = get_unique_group_name($_REQUEST['rid'], $con);
      }
	 	  
      ####Make accounting entries
      /* 
        Cr Guest Account,  Dr Cash
        Cr Room Sales, Dr Guest Account
        Dr Guest Account, Cr Tax payable 
        Dr Guest Account, Cr Service Charge expenses
        Dr Guest Account, Cr Other Charges
      */ 
      if (account_exists('Room Sales', $con) 
        && (account_exists(get_acc_name($guest_acc_id, $con), $con))
        && (account_exists('Cash', $con))) {
	  
        $sales_log = "sales";
        ####Entries to record payment of cash 
        #####RECORD SALES AS CASH RECEIVED#####
		$id = add_sales($addate, '', get_value('user', 'username', 'id', $_SESSION['uid'], $con),
          $guest_name, $_REQUEST['d_nbr'], get_room($_REQUEST['rid'], $con), 
          "Deposit ", 'Room', 'Cash Deposit', $_REQUEST['amt'], $addate, $dddate, $_REQUEST['p'], $_REQUEST['sx'], $_REQUEST['a'], $con);
        $sales_log = "$sales_log $id";

 
        $account_log = "journal";
	    ####Credit Guest Account
	    $id = j_entry('Credit', '1', $guest_acc_id, $addate, 
         "Cash received from $guest_name", $_REQUEST['amt'], $con);
        $account_log = "$account_log $id";
		
	    ####Debit Cash Account
	    $id = j_entry('Debit', '1', get_acc_id('Cash', $con), $addate, 
          "Cash received from $guest_name", $_REQUEST['amt'], $con);
        $account_log = "$account_log $id";

        ####RECORD AS CREDIT SALES ##### 
        $id = add_sales($addate, '',  get_value('user', 'username', 'id', $_SESSION['uid'], $con), 
          $guest_name, $_REQUEST['d_nbr'], get_room($_REQUEST['rid'], $con), "Room Rate ", 'Room', 'Credit Sales', $rate,
	      $addate, $dddate, $_REQUEST['p'], $_REQUEST['sx'], $_REQUEST['a'], $con);
        $sales_log = "$sales_log $id";

        ###Debit Guest Account
        $id = j_entry('Debit', '1', $guest_acc_id, $addate, "Checked In $guest_name", $rate, $con);
        $account_log = "$account_log $id";

        ###Credit Room Sales
        $id = j_entry('Credit', '1', get_acc_id('Room Sales', $con), $addate, "Checked In $guest_name", $rate, $con);
        $account_log = "$account_log $id";
		

        ###Credit all the charges
        ####Mimah suites makes payment of charges optional####
	    $total_percentage = 0;
	    $total_amount = 0;
        if (isset($_REQUEST['charges'])) {
          foreach ($_REQUEST['charges'] as $charge_id) {
            $sql="select * from charges where id=$charge_id";
            $result = mysql_query($sql) or die(mysql_error());
	        while ($row = mysql_fetch_array($result)) {
              if ($row['type'] == 'percentage_of_room_rate') { 
	            $charge = ($row['amount']/100) * $rate;
                $charge = floor($charge); //Remove decimals

	            $total_percentage += $charge; 

                ###Debit Guest Account
                $id = j_entry('Debit', '1', $guest_acc_id, $addate, "{$row['name']} for $guest_name", $charge, $con);
                $account_log = "$account_log $id";
			
                ####Credit charges
	            $id = j_entry('Credit', '1', $row['acc_id'], $addate,  "{$row['name']} for $guest_name", $charge, $con);
                $account_log = "$account_log $id";
			
                ####Record as Charges
                $id = add_sales($addate, '', get_value('user', 'username', 'id', $_SESSION['uid'], $con), $guest_name, 
				  $_REQUEST['d_nbr'], get_room($_REQUEST['rid'], $con), 
	            $row['name'], 'Room', 'Charges', $charge, $addate, $dddate, '', '', '', $con);
                $sales_log = "$sales_log $id";

	          } else if ($row['type'] == 'constant_amount')  {
                $charge = $row['amount'];
	            $total_amount += $charge; 
			
                ###Debit Guest Account
                $id = j_entry('Debit', '1', $guest_acc_id, $addate, "{$row['name']} for $guest_name", $charge, $con);
                $account_log = "$account_log $id";

	            $id = j_entry('Credit', '1', $row['acc_id'], $addate,   "{$row['name']} for $guest_name", $charge, $con);
                $account_log = "$account_log $id";

                ####Record as Charges
                $id = add_sales($addate, '', get_value('user', 'username', 'id', $_SESSION['uid'], $con), 
				  $_REQUEST['d_nbr'], get_room($_REQUEST['rid'], $con), 
	            $row['name'], 'Room', 'Charges', $charge, $addate, $dddate, '', '', '', $con);
                $sales_log = "$sales_log $id";
	          }
            }
          }
        } 
	  ####END TRANSACTION####
      //Record in the log
      $log = "$sales_log|$account_log"; 
      audit_trail($_SESSION['uid'], "Added Guest $guest_name<br /> Cash Deposit = {$_REQUEST['amt']}", '', $log, $con);

	  mysql_query("commit", $con); //Is this how its done?
    } else {
      echo msg_box('Problem with accounts', 'guest.php?action=Add', 'Back');
      exit;
     }
   }
 } elseif (isset($_REQUEST['action']) && 
  (($_REQUEST['action'] == 'Edit') || ($_REQUEST['action'] == 'View') 
   || ($_REQUEST['action'] == 'Add'))) {
   
  $gid = 0;
  if(($_REQUEST['action'] == 'Edit') || ($_REQUEST['action'] == 'View')) {
    if(empty($_REQUEST['gid'])) {
      echo msg_box("Please choose a room to View", 'guest.php', 'Back');
      exit;
    }
  }
  $sql="select * from guest where id={$gid}";
  $result = mysql_query($sql, $con) or die(mysql_error());
  $row = mysql_fetch_array($result);
   ?>
   <table> 
    <tr class='class1'>
     <td colspan='7'> 
      <h3><?php echo $_REQUEST['action']; ?></h3>
      <form name="myform" action="guest.php" method="post">
     </td>
    </tr>
    <tr><td><input type="hidden" name='gid' 
     value="<?php echo $gid; ?>"></td></tr>
    <?
    if($_REQUEST['action'] == 'Add') {
      echo "
    <tr>
     <td>Room</td>
     <td>
      <select id='rid' name='rid' onchange='fetch_rate();'>";
      //Get all the vacant rooms
      $result = mysql_query("select r.id, r.number, c.name, r.occupancy, 
       r.status from room r join room_category c on r.room_category_id = c.id
       where r.occupancy = 'Vacant' order by r.number", $con) 
      or die(mysql_error());
      echo "<option></option>";
      while($row = mysql_fetch_assoc($result)) { 
        echo "<option value='" . $row['id'] . "'>";
        echo $row['number'] ."&nbsp;" .  $row['name'] . "&nbsp;"
        . $row['occupancy'] . "&nbsp;" .  $row['status'] . "</option>";
      }
      echo "
      </select>
     </td>
     <td>
        Profile<input type='radio' name='from' value='profile'
        onClick=\"display_element('profile'); \">
     </td>
     <td>
      <select name='profile' id='profile' style='display:none;'
       onChange=\"get_profile_reserv('profile');\"> 
      ";
      //Get profiles of previously checked out guests
      $result = mysql_query("select id, title, firstname, lastname from
       guest_checked_out order by firstname", $con) 
      or die("Cannot execute SQL query " . mysql_error());
      echo "<option></option>";
      while ($row = mysql_fetch_assoc($result)) {
        echo "<option value='{$row['id']}'> 
         {$row['title']} {$row['firstname']} {$row['lastname']}
          </option>";
      } 
      echo "
      </select>
     </td>
    </tr>
    ";
    ?>
    <tr>
     <td colspan="2">
      <fieldset>
       <legend>Rate</legend>
        <table>
         <tr>
          <td>
          Room Rate
          <input type="radio" name="r_choice" id="r_choice" value="rr" 
            onClick="display_element('rr'); hide_element('sr');">
         </td>
         <td>
          <input style="display:none;" readonly="readonly" id = "rr" 
           type="text" name="rr">
         </td>
         <td>
          Special Rate
           <input type="radio" name="r_choice" id="r_choice" value="sr" 
            onClick="display_element('sr'); hide_element('rr');">
         </td>
         <td>
          <input type="text" id="sr" name="sr" style="display:none">
         </td>
        </tr>
       </table>
      </fieldset>
     </td>
     </tr>
     <?php } 
     if ($_REQUEST['action'] == 'Edit')
       echo "<tr><td colsapn='3'><b>Please note: 
         You can't change the Title, Firstname and Lastname. 
         To Do that, Delete the guest</b></td></tr>";
     ?>
     <tr>
      <td colspan='3'>
       <table>
        <tr>
	 <td>Title</td>
         <td><input type="text" id="t" name="t" value="<?php echo $row['title'];?>"
         <?php 
         if ($_REQUEST['action'] == 'Edit') 
           echo "disabled='disabled'";
         ?> 
         >
          </td>
         <td>Firstname</td>
         <td><input type="text" id="f" name="f" 
          value="<?php echo $row['firstname']; ?>"
         <?php 
         if ($_REQUEST['action'] == 'Edit') 
           echo "disabled='disabled'";
         ?> 
          ></td>
         <td>Lastname</td><td><input type="text" 
          id="l" name="l" value="<?php echo $row['lastname']; ?>"
         <?php 
         if ($_REQUEST['action'] == 'Edit') 
           echo "disabled='disabled'";
         ?> 
         ></td>
	</tr>
       </table>
      </td>
     </tr> 
     <tr>
      <td colspan='3'>
       <table>
        <tr>
         <td>Phone</td>
         <td><input type="text" id="p" name="p" value="<?php echo $row['phone']; ?>">
         </td>
         <td>Sex</td>
         <td>
	  <select id="sx" name="sx">
           <option <? if($row['sex'] =='Male') echo 'Selected';?>>Male</option> 
	   <option <? if($row['sex'] =='Female') echo ' Selected';?>>Female</option>
	  </select>
         </td>
        </tr>
        <tr>
	 <td>Address</td>
	 <td>
          <textarea rows="5" cols="50" id="a" name="a"><?php echo $row['address']; ?></textarea>
	 </td>
        </tr>
       </table>
      </td>
     </tr>
     <tr>
      <td>
       <?php
	if ($_REQUEST['action'] == 'Add') {
	?>
       <table>
        <tr>
	 <td>Bill To</td>
         <td>Individual<input type="radio" id="individual" name="b" 
           value="individual"
           onClick="hide_element('group_company_guests');  "checked='checked'>
           Group/Company<input type="radio" id="gc" name="b" 
           value="GroupCompany" 
           onClick="display_element('group_company_guests'); 
           ">
          <select style="display:none" id="group_company_guests" 
           name="group_company_guests">
           <?php
           //Get all group company guests
           $sql="Select id, name from group_company_guests";
           $result = mysql_query($sql, $con) or die(mysql_error());
           echo "<option></option>";
           while($row = mysql_fetch_array($result)) {
             echo "<option value='{$row['id']}'>{$row['name']}</option>";
           }
           ?>
          </select>
         </td>
        </tr>
	<tr>
         <td>Arrival Date</td>
         <td>
          <input type='text' name='ad' 
           value='<?php echo $row['arrival_date']; ?>' 
           size='10' maxlength='10'>YYYY-MM-DD</td>
        </tr>
        <tr>
         <td>Expected Departure Date</td> 
         <td><input type='text' name='dd'  
          value='<?php echo $row['departure_date']; ?>' 
          size='10' maxlength='10'>YYYY-MM-DD</td>
        </tr>
	<?
	}
	?>
	<tr>
         <td>Receipt Number</td> 
         <td>
          <input type="text" id="d_nbr"  name="d_nbr" 
           value="<?php echo $row['doc_number']; ?>"></td>
        </tr>
	<?php
	if ($_REQUEST['action'] == 'Add') {
	?>
	<tr>
         <td>Deposit</td> 
         <td><input type="text" id="amt" name="amt"></td>
        </tr>
       </table>
      <!--List charges -->
      </td>
      <td style='vertical-align:top;'>
       <table>
        <tr><td>Charges to room rate</td></tr>
        <tr>
         <td>
          <select name='charges[]' size='5' multiple='multiple'>
           <?php 
           $sql="select * from charges";
           $result = mysql_query($sql) or die(mysql_error());
           while($row = mysql_fetch_array($result)) 
             echo "<option value='{$row['id']}'>{$row['name']}</option>";
           echo "
          </select>
         </td>
        </tr>
       </table>
      </td>";
      }
      ?>
     </tr>
     <tr>
      <td>
      <?php
      if($_REQUEST['action'] == 'Edit') {
       echo "<input name='action' type='submit' value='Update'>";
       echo "<input name='gid' type='hidden' value='{$_REQUEST['gid']}'>";
      } else if ($_REQUEST['action'] == 'Add') {
        echo "<input name='action' type='submit' value='Add Guest'>";
      }
      ?>
      <input name="action" type="submit" value="Cancel">
      </td>
     </tr>
    </table>
  </form>
  <?
  exit;
}
elseif (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Update')) {
  $sql="Update guest set phone='{$_REQUEST['p']}', sex='{$_REQUEST['sx']}', 
    address='{$_REQUEST['a']}',
    doc_number ='{$_REQUEST['d_nbr']}' where id={$_REQUEST['gid']}";
  mysql_query($sql, $con);
}
elseif (isset($_REQUEST['action']) && ($_REQUEST['action'] == 'Delete')) {
  if(empty($_REQUEST['gid'])) {
    echo msg_box('Please choose a guest to delete', 'guest.php', 'Back');
    exit;
  }
  ?>
  <table> 
   <tr class='class1'>
    <td colspan='7'> 
     <h3>Confirm Delete</h3>
      <form name="myform" action="guest.php" method="post">
    </td>
   </tr>
  <?
  $result = mysql_query("select id, title, firstname, lastname, 
  grp_cmp_id from guest where id=".$_REQUEST['gid'], $con);
  $temp = mysql_fetch_array($result);
  if (empty($temp['grp_cmp_id'])) {
    echo "<tr><td>Are you sure you want to delete<b>{$temp['title']} 
     {$temp['firstname']} {$temp['lastname']}</b></td></tr>";
  } else {
    echo "
     <tr><td><b>{$temp['title']} {$temp['firstname']}
      {$temp['lastname']}</b> Belongs to
     " . get_group_company($temp['grp_cmp_id'], $con) .  
     "group account</td></tr>
     <tr><td>Deleting this guest will delete the group account 
      and all the guests registered under this account</td></tr>
      <tr><td>Are you sure you want to delete?</td></tr>";
  }
  ?>
   <tr>
     <td>
      <input name='gid' value='<?=$temp['id']?>' type='hidden'>
      <input name="action" type="submit" value="Continue Delete">
      <input name="action" type="submit" value="Cancel">
     </td>
   </tr>
   </table>
   </form>
   <?
   exit;
}
elseif (isset($_REQUEST['action']) && 
  ($_REQUEST['action'] == 'Continue Delete')) {
  $sql = "select r.id, r.acc_id, a.name, r.number, g.title, 
   g.firstname,  g.lastname,  g.arrival_date, g.doc_number, g.bill_to, 
   g.grp_cmp_id, rc.name as 'rc_name' from guest g inner join 
   (room r, account a, room_category rc) on 
   (g.room_id = r.id and r.acc_id = a.id and r.category_id = rc.id) 
    where g.id=".$_REQUEST['gid'];
  $result = mysql_query($sql, $con);
  $gr = mysql_fetch_array($result);
   
  $grp_cmp_id = $gr['grp_cmp_id'];
  $bill_to = $gr['bill_to'];
  $arrival_date = $gr['arrival_date'];
  $number = $gr['number'];
  $name = $gr['name'];
  $rid = $gr['id'];
  $acc_id = $gr['acc_id'];
  $rc_name = $gr['rc_name'];
   
  mysql_query('start transaction', $con);
  if ($gr['bill_to'] == 'individual') {
    $unique_guest_name = get_unique_guest_name($rid, $con);
    $sql = "delete from guest where room_id=$rid";
    mysql_query($sql, $con);
	 
    #### Update room data for guest####
    $sql = "update room set occupancy = 'Vacant', 
     status='Clean', acc_id = 'NULL' where id=$rid";
    mysql_query($sql, $con);
  } else {
    $unique_group_name = get_unique_group_name($rid, $con);
    $result = mysql_query("select id, room_id from guest 
     where grp_cmp_id =$grp_cmp_id", $con);

    while($row=mysql_fetch_array($result)) {
      $sql ="Delete from guest where id=".$row['id'];
      mysql_query($sql, $con);
	   
      $sql = "update room set occupancy='Vacant', status='Clean', 
        acc_id='NULL' where id=". $row['room_id'];
      mysql_query($sql, $con);
    }
  }
  $sql ="DELETE FROM account where id=$acc_id";
  mysql_query($sql, $con);
   
  mysql_query('commit', $con);
  $msg = "{$gr['title']} {$gr['firstname']} {$gr['lastname']} 
          <br /> has been deleted from the list of guest ";
  if (!empty($grp_cmp_id)) 
     $msg .= " <br /> along with others belonging to his/her group";
  echo msg_box($msg, 'guest.php', 'Back');
  exit;
}
?> 
 <div class='class1'>
<?php
  if(isset($_REQUEST['action']) && ($_REQUEST['action'] != 'Print')
   || (!isset($_REQUEST['action']))) {
      echo "<a href='guest.php?action=Add'>Add</a>  |
	   <a href='guest.php?action=Print'>Print</a>";
  }
?>
 <h3 class='sstyle1'>Guest</h3>
</div>
<table>
 <tr>
  <th>Room</th>
  <th>Title</th>
  <th>Firstname</th>
  <th>Lastname</th>
  <th>Phone</th>
  <th>Arrival</th>
  <th>Expected Departure</th>
 </tr>
 <?
  $result = mysql_query("select g.id, r.number, g.title, 
	g.firstname, g.lastname, g.phone, g.arrival_date,
	g.departure_date from room r join guest g on r.id = g.room_id 
	where r.occupancy = 'Occupied'", $con);
  while($row = mysql_fetch_array($result)) {
   echo "
   <tr>
	<td><a href='guest.php?gid={$row['id']}&action=Edit'>{$row['number']}</td>
	<td>{$row['title']}</td>
	<td>{$row['firstname']}</td>
	<td>{$row['lastname']}</td>
	<td>{$row['phone']}</td>
	<td>{$row['arrival_date']}</td>
	<td>{$row['departure_date']}</td>
   </tr>";
  }
  echo '</form></table>'; 
  main_footer();
?>
