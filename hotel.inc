<?php 

include_once 'library/ui.inc';
include_once 'library/acc.inc';

function audit_trail($uid, $desc, $shift, $others, $con) {
  #use current_date instead of curr_date_time to allow searching by sd and ed
  $result = mysql_query("insert into audit_trail
   (dt, staff_id, descr, shift, ot, dt2) values(NOW(), '$uid', '$desc', '$shift', 
   '$others', CURDATE())", $con) or die("Cannot execute SQL audit_trail " . mysql_error());
}

function startup_checks($tables) { 
  foreach($tables as $table) {
    $sql="select * from $table";
	if (mysql_num_rows(mysql_query($sql)) <= 0) {
      echo msg_box("Please enter values in " . format_label($table) . " table before you proceed", '', '');
      exit;
    }
  }
}
function get_charges() {
  $total_charges = array();
  $result_c = mysql_query("select * from charges") or die(mysql_error());	  
  while ($row_c = mysql_fetch_array($result_c)) {
    $total_charges[$row_c['name']] = 0;
  }
  return $total_charages();
}
		  
function is_group_account($name_of_guest) {
  //Format of group name account 'GroupName<space><number><space><date>'
  //Where <date> is YYYY-MM-DD
  //example: 100: Mr Greg Omebije 2011-04-02 Wrong Match
  //exmaple: g 1 2011-04-02 - Right Match
  //example: Group1 19 2011-03-17 - Right Match
  //if (preg_match("/^\w+\s+\d+\s+/i", $name_of_guest, $matches)) {
  if (preg_match("/^\w+\s+\d+\s+(\d{4})-(\d{2})-(\d{2})/i", $name_of_guest, $matches)) {
	return true;
  } else {
    return false;
  }
}	  
function get_balance($name_of_guest, $r_number, $con) {
  $credit_sales = 0.0;
  $cash_deposit = 0.0;
  $cash_received = 0.0;
  $cash_refund = 0.0;
    
  //Credit Sales
  $sql = "SELECT sum(amount) as 'amount' from sales where dept='Room' and p_type = 'Credit Sales' and name_of_guest='$name_of_guest'";
  if (empty($r_number)) {
    $sql .= " group by name_of_guest";
  } else {
    $sql .= " and r_number='$r_number' group by r_number";
  }
  $result = mysql_query($sql, $con);
  $row = mysql_fetch_array($result);
  $credit_sales = $row['amount'];
	  
  //Cash Deposit
  $sql = "SELECT sum(amount) as 'amount' from sales where dept='Room' and p_type = 'Cash Deposit' and name_of_guest='$name_of_guest'";
  if (empty($r_number)) {
    $sql .= " group by name_of_guest";
  } else {
    $sql .= " and r_number='$r_number' group by r_number";
  }
  $result = mysql_query($sql, $con);
  $row = mysql_fetch_array($result);
  $cash_deposit = $row['amount'];
	  
  //Cash Received
  $sql = "SELECT sum(amount) as 'amount' from sales where dept='Room' and p_type = 'Cash Received' and name_of_guest='$name_of_guest'";
  if (empty($r_number)) {
    $sql .= " group by name_of_guest";
  } else {
    $sql .= " and r_number='$r_number' group by r_number";
  }
  $result = mysql_query($sql, $con);
  $row = mysql_fetch_array($result);
  $cash_received = $row['amount'];
	
  //Getting Cash Refund is not neccessary
  $balance = $credit_sales - ($cash_deposit + $cash_received);
  return $balance;
}
function get_bill_to($rid, $con) {
  $row = mysql_fetch_array(mysql_query("select g.bill_to from 
   guest g join room r on g.room_id = r.id where g.room_id=$rid", $con));
  return $row['bill_to'];
}
function get_did($d, $con) {
  $row = mysql_fetch_array(mysql_query("select id from department 
    where name='$d'", $con));
  return $row['id'];
}
function get_dname($id, $con) {
  $row = mysql_fetch_array(mysql_query("select name from department 
   where id=$id", $con));
  return $row['name'];
}
function get_field_by_id($field_name, $table_name, $id, $con) {
  $sql="select $field_name from $table_name where id=$id";
  $result = mysql_query($sql, $con);
  $row = mysql_fetch_array($result);
  return $row[$field_name];
}

function get_group_company($id, $con) {
  $row = mysql_fetch_array(mysql_query("select name from group_company_guests 
   where id=$id", $con));
  return $row['name'];
}

function get_guest($rid, $con){
  $row = mysql_fetch_array(mysql_query("select title, firstname, lastname from 
   guest where room_id=$rid", $con));
  return $row['title']. " " .$row['firstname']." ".$row['lastname'];
}
function get_room_number_guest($rid, $con) {
  $row = mysql_fetch_array(mysql_query("select r.number, g.doc_number, g.title, g.firstname, g.lastname from 
   guest g join room r on g.room_id = r.id where g.room_id=$rid", $con));
  return $row['number']. ": " . $row['title'] . ' '.$row['firstname'].' '.$row['lastname'].' '.$row['doc_number'];
}
function get_unique_guest_name($rid, $con) {
  $row = mysql_fetch_array(mysql_query("select r.number, g.arrival_date, g.title, g.firstname, g.lastname from 
   guest g join room r on g.room_id = r.id where g.room_id=$rid", $con));
  return $row['number']. ": " . $row['title'] . ' '.$row['firstname'].' '.$row['lastname'].' '.$row['arrival_date'];
} 

function get_unique_group_name($rid, $con) {
  $row = mysql_fetch_array(mysql_query("select a.name from account a left join 
   (guest g, group_company_guests gc) on (g.grp_cmp_id = gc.id and gc.acc_id = a.id) where g.room_id=$rid", $con));
  return $row['name'];
}
function get_unique_group_name2($gcid, $con) {
 $row = mysql_fetch_array(mysql_query("select gc.name, gc.arrival_date, r.number, g.title, g.firstname, g.lastname from 
   guest g join (room r, group_company_guests gc) on (g.room_id = r.id and g.grp_cmp_id = gc.id) where g.grp_cmp_id=$gcid", $con));
  return $row['name']. ' '.$row['arrival_date'];
}
function get_group($gid, $con) {
 $row = mysql_fetch_array(mysql_query("select name from 
   group_company_guests where id=$gid", $con));
  return $row['name'];
}

function get_tax($con) {
  $result = mysql_query("select tax from hotel_info", $con) or 
   die("Cannot execute SQL hotel.in@get_tax:1 " . mysql_error());
  if (mysql_num_rows($result) > 0) {
    $row = mysql_fetch_array($result);
    return $row['tax'];
  } else {
    return 0;
  }
}
function get_sc($con){
  $result = mysql_query("select service_charge from hotel_info", $con) or 
   die("Cannot execute SQL hotel.in@get_sc:1 " . mysql_error());
  if (mysql_num_rows($result) > 0) {
    $row = mysql_fetch_array($result);
    return $row['service_charge'];
  } else {
    return 0;
  }
}
function get_room($rid, $con) {
  $result = mysql_query("select r.id, r.number, c.name from room r 
    join room_category c on r.room_category_id = c.id where r.id=$rid", $con);
  $row = mysql_fetch_array($result);
  return $row['number'] . ' ' . $row['name'];
}
function get_room_category($cid, $con) {
  $result = mysql_query("select name from room_category where id=$cid", $con);
  $row = mysql_fetch_array($result);
  return $row['name'];
}
function get_room_and_category_vacant($con) {
  mysql_fetch_array(mysql_query("select r.id, r.number, c.name, r.occupancy, 
   r.status from room r join room_category c on r.category_id = c.id 
    where r.occupancy= 'Vacant'", $con));
}

function get_vacant_or_booked($con) {
  mysql_fetch_array(mysql_query("select r.id, r.number, c.name, r.occupancy, 
    r.status from room r join room_category c 
    on r.category_id = c.id where r.occupancy != 'Occupied'", $con));
}


function add_city_ledger ($t, $f, $l, $sx, $a, $p, $bal, $con)  {
  $sql = "SELECT * FROM city_ledger WHERE firstname ='$f' and
      lastname = '$l'";
  $result = mysql_query($sql, $con) 
   or die("Cannot execute SQL hotel.inc@add_city_ledger:1" . mysql_error());
  if(mysql_num_rows($result) > 0) {
        return "There's already an account with $f $l. Please choose another";
  } else {
    $sql = "INSERT INTO city_ledger (title, firstname, lastname, sex, 
      address, phone, balance) 
      VALUES ('$t', '$f', '$l', '$sx', '$a', '$p', '$bal')";
    mysql_query($sql, $con) 
     or die("Cannot execute SQL hotel.inc@add_city_ledger:1 " . mysql_query());
    return 1;
  }
}
function delete_city_ledger($id, $con) {
  $result = mysql_query("SELECT balance FROM city_ledger where id=$id", $con)
    or die("Cannot execute SQL hotel.inc@delete_city_ledger:1 " . mysql_error());
  if (mysql_num_rows($result) <= 0) {
    return "There is no such city ledger account";
  } else {
    $row = mysql_fetch_array($result);
    if (($row['balance'] < 0) || ($row['balance'] > 0)) { 
      return "Cannot delete this city ledger account because it still has a 
        balance";
    } elseif ($row['balance'] == 0) {
      mysql_query("delete from city_ledger where id=$id", $con)
       or die("Cannot execute SQL hotel.inc@delete_city_ledger:2 " . mysql_error());;
      return 1;
    }
  }
}
function r_city_ledger($id, $amt, $type, $con){
  $bal = 0;
  $result = mysql_query("SELECT balance FROM city_ledger where id=$id", $con)
   or die("Cannot execute SQL hote.inc@r_city_ledger:1 " .mysql_query());
  if (mysql_num_rows($result) == 0) {
    return "There is no such city ledger account";
  } else {
    $row = mysql_fetch_array($result);
    if ($type == 'Refund') {
      $bal = $row['balance'] - $amt;
    } elseif ($type == 'Receive') {
      $bal = $row['balance'] + $amt;
    }
    mysql_query("UPDATE city_ledger set balance = '$bal' where id=$id", $con)
     or die("Cannot execute SQL hote.inc@r_city_ledger:2 " .mysql_query());
    return 1;
  }
}
function t_city_ledger($id, $aid, $amt, $e, $s, $con) {
  
  $result = mysql_query("select balance from city_ledger where id=$id 
       and balance >= $amt", $con)
     or die("Cannot execute SQL hotel.inc@t_city_ledger:1 " .mysql_query());
  if (mysql_num_rows($result) == 0) {
    return "There was an error: 
     Possble cause could be that the amount to transfer is greater than
      what is available for this city ledger account 
      or this City ledger account doesn't exists"; 
  } else {
    $row = mysql_fetch_array($result);
    $namt = $row['balance'] - $amt;
    j_entry('Debit', $e, $aid, "Transfer from City Ledger", $amt, $con);
   
    mysql_query("update city_ledger set balance = '$namt' where id=$id", $con)
        or die("Cannot execute SQL hotel.inc@t_city_ledger:2 " .mysql_query());
    return 1;
  }
}
function print_account_status ($choice, $id_name, $title, $sd, $ed, $con) {
  $result = mysql_query("SELECT id, name FROM account where 
   id=$id_name", $con)
   or die("Cannot execute SQL hotel.inc@print_account_status:1 " .mysql_query());
  $accounts = mysql_fetch_array($result);
  ?>
  <table border="1" width="100%" rules="rows">
   <tr style="background-color:silver">
    <td><b>Date</b></td>
    <td><b>Description</b></td>
    <td><b>Debit</b></td>
    <td><b>Credit</b></td>
    <td><b>Balance</b></td>
   </tr>
   <?   
   $sql = "SELECT * from journal where acc_id =" . $accounts['id'] . " order by id asc";
   if ((!empty($ed)) && (!empty($sd))) {
     $sql .= " and d_entry  between '$sd' and '$ed' order by id asc";
   } 
   //echo $sql;
   $result = mysql_query($sql, $con)
    or die("Cannot execute SQL hotel.inc@print_account_status:2 "
    .mysql_query());
   while($journal = mysql_fetch_array($result)) {
   ?>
   <tr> 
    <td><?=$journal['d_entry']?></td>
    <td><?=$journal['descr']?></td>
   <?
    if ($journal['t_type'] == 'Debit') {
      echo "<td>" . number_format($journal['amt'], 2) . "</td><td>&nbsp;</td>";
    } else {
      echo "<td>&nbsp;</td><td>" . number_format($journal['amt'], 2) . "</td>";
    }
    echo "<td>" . number_format($journal['bal'], 2) . "</td></tr>";
  }
  echo "</table>";
}
/*
function print_account_status ($unique_guest_name, $sd, $ed, $con) {
  ?>
  <table border="1" width="100%" rules="rows">
   <tr class='class1'>
    <td><b>Date</b></td>
	<td><b>Receipt No.</b></td>
    <td><b>Description</b></td>
    <td><b>Debit</b></td>
    <td><b>Credit</b></td>
    <td><b>Balance</b></td>
   </tr>
   <?   
   $sql = "SELECT dt, doc_number, r_number, t_details, amount, p_type from sales where name_of_guest='$unique_guest_name' 
    and p_type != 'Cash Sales'";
	
   if ((!empty($ed)) && (!empty($sd))) {
     $sql .= " and dt  between '$sd' and '$ed' "; 
   } 
   $sql .= "order by id asc";
   
   $result = mysql_query($sql, $con);
   $debit  = 0;
   $credit = 0;
   while($journal = mysql_fetch_array($result)) {
   ?>
   <tr> 
    <td><?=$journal['dt']?></td>
	<td><?=$journal['doc_number']?></td>
    <td><?php echo "{$journal['r_number']}: {$journal['t_details']}"; ?></td>
	
	
   <?
    if ($journal['p_type'] == 'Credit Sales') {
	  $debit += $journal['amount'];
      echo "<td>".$journal['amount']."</td><td>&nbsp;</td>";
    } else {
	  $credit += $journal['amount'];
      echo "<td>&nbsp;</td><td>".$journal['amount']."</td>";
    }
    echo "<td>" . ($debit - $credit) . "</td></tr>";
   }
  echo "</table>";
}
*/


function add_item_category($n, $con) {
  if (($n == '')  ||
      (strlen($n) < 2) || (strlen($n) > 30)) { 
     return "
          Item category names can only contain letters, 
          digits, dashes and underscores, and should be between 
          2 and 15 characters long. Please choose another";
  } else {
    $result = mysql_query("SELECT * FROM item_category  WHERE name ='$n'", $con)
     or die("Cannot execute SQL hotel.inc@add_item_category:1 " .mysql_query());
      if(mysql_num_rows($result) > 0) {
        return "This Item Category  name is taken. Please choose another";
      } else {
        mysql_query("INSERT INTO item_category (name) VALUES ('$n')", $con)
     or die("Cannot execute SQL hotel.inc@add_item_category:3 " .mysql_query());
         return 1;
      }
  }
}
    
function add_group_company_guests($n, $a, $p, $ad, $con) {
  if(mysql_num_rows(mysql_query("SELECT * FROM 
    group_company_guests WHERE name ='$n'", $con)) > 0) {
    return "This Group/Company Guest's name is taken. Please choose another";
  } else {
    mysql_query("INSERT INTO group_company_guests 
     (name, address, phone, arrival_date)
     VALUES ('$n', '$a', '$p', '$ad')", $con)
    or die("SQL Error hotel.inc@add_group_company_guests:1 ".mysql_error()); 
    $gcid = mysql_insert_id();
    $acc_name = "$n $gcid $ad";
	
    if(!account_exists($acc_name, $con)) {
      //Create a current asset account
      add_account($acc_name, OTHER_CURRENT_ASSETS, '1', $ad, $con);
	
      $acc_id = get_acc_id($acc_name, $con);
      $sql="update group_company_guests set acc_id=$acc_id where id=$gcid";
	  
      mysql_query($sql, $con);
      return 1;
    }
  }
}

function change_rooms($orn, $nrn, $con) { 

  $sql = "select g.id, g.title, g.firstname, g.lastname, 
   g.bill_to, r.acc_id, r.number from guest g join room r on r.id = g.room_id  
   where r.id = $orn";

  $result = mysql_query($sql, $con); 
  $g = mysql_fetch_array($result); 

  $sql = "select number from room where id = '$nrn'"; 

  $result = mysql_query($sql, $con);
  $nr = mysql_fetch_array($result);

  $sql = "update guest set room_id=$nrn where id=".$g['id'];

  mysql_query($sql, $con) 
    or die("SQL Error hotel.inc@change_rooms:1 ".mysql_error());

  $sql = "update room set occupancy = 'Occupied' where id=$nrn";
  mysql_query($sql, $con) 
    or die("SQL Error hotel.inc@change_rooms:2 ".mysql_error());

  $sql = "update room set occupancy = 'Vacant' where id=$orn";
  mysql_query($sql, $con) 
    or die("SQL Error hotel.inc@change_rooms:3 ".mysql_error());

  $acc_name = $nr['number'];
  $acc_name .= ": ".$g['title']. " ".$g['firstname'] . " " . $g['lastname']; 

  $sql = "update account set name = '$acc_name' where id=" . $g['acc_id'];
  mysql_query($sql, $con) 
    or die("SQL Error hotel.inc@change_rooms:4 ".mysql_error());
  
  $sql = "update room set acc_id=" . $g['acc_id'] . " where id=$nrn";
  mysql_query($sql, $con) 
    or die("SQL Error hotel.inc@change_rooms:5 ".mysql_error());

  $sql = "update room set acc_id= 'NULL' where id=$orn";
  mysql_query($sql, $con) 
    or die("SQL Error hotel.inc@change_rooms:6 ".mysql_error());
}

function check_out($rid, $date, $bal, $shift, $uid, $con) {  
   $sql = "select r.id, g.id as 'gid', r.acc_id, a.name, r.number, 
    g.title, g.firstname,  g.lastname, g.phone, g.doc_number, g.sex, 
    g.address, g.arrival_date, g.doc_number, g.grp_cmp_id, g.bill_to, rc.name as    'rc_name' from guest g inner join (room r, account a, room_category rc) 
   on (g.room_id = r.id and r.acc_id = a.id and r.category_id = rc.id) 
   where r.id=$rid";
	
   $p_type='';
   $t_details='';
   if ($bal < 0) {
     $p_type='Cash Refund';
	  $t_details = 'Cash refund of refundable balalnce';
   } else if ($bal == 0) {
     $p_type='Cash Received';
	 $t_details='No Cash refund or outstanding';
   } else if ($bal > 0) {
     $p_type='Cash Received';
	 $t_details = 'Cash Received for outstanding balance';  
   }
   $result = mysql_query($sql, $con);
   $gr = mysql_fetch_array($result); 
   
   if ($gr['bill_to'] == 'individual') {
     $guest_name = get_unique_guest_name($rid, $con);
	 
     #This is an individual guest
     ###We are should back up guest info by check if the data already exists
	 # in guest_checked_out to avoid entering the data
   
     $sql="select * from guest_checked_out where firstname ='". 
     $gr['firstname'] ."' and lastname='". $gr['lastname'] ."'";
     $result2 = mysql_query($sql, $con);
     if (mysql_num_rows($result2) == 0) {
       mysql_query("insert into guest_checked_out 
        (title, firstname, lastname, phone, sex, address) 
        select title, firstname, lastname, phone, sex, address
        from guest where id=" . $gr['gid'], $con)
       or die("SQL Error hotel.inc@check_out:1 ".mysql_error());
     } else {
       $gco = mysql_fetch_array($result2);
       $sql="update guest_checked_out set title='".$gr['title'] 
	   . "', firstname='" . $gr['firstname'] 
	   . "', lastname='" . $gr['lastname']
	   . "', phone='" . $gr['phone']
	   . "', sex='" . $gr['sex']
	   . "', address='" . $gr['address']  . "' where id=" . $gco['id'];
       echo $sql;
       mysql_query($sql, $con);
     }
     mysql_query("delete from guest where room_id =$rid", $con)
       or die("SQL Error hotel.inc@check_out:2 ".mysql_error());
	   
     #### Update room data for guest####
     mysql_query("update room set occupancy = 'Vacant', status='Clean', 
      acc_id = 'NULL' where id=$rid", $con)
     or die("SQL Error hotel.inc@check_out:4 ".mysql_error());

     mysql_query("DELETE FROM account where id=".$gr['acc_id'], $con)
      or die("SQL Error hotel.inc@check_out:6 ".mysql_error());
	  
     ####Record Transaction####
     add_sales($date, $shift, get_user($uid, $con), $guest_name, 
       $gr['doc_number'], get_room($rid, $con), $t_details, 'Room', 
       $p_type, $bal, 
       '','','','','',$con);
   } else {
     $guest_name = get_unique_group_name($rid, $con);
     $sql="select id, title, firstname, lastname,
       phone, sex, address, room_id from guest 
       where grp_cmp_id ={$gr['grp_cmp_id']}";
     $result = mysql_query($sql, $con);
     while($row=mysql_fetch_array($result)) {
       $sql2="select * from guest_checked_out where firstname ='". 
       $row['firstname'] ."' and lastname='". $row['lastname'] ."'";
       $result3 = mysql_query($sql2, $con);
       if (mysql_num_rows($result3) == 0) {
         $sql="insert into guest_checked_out 
           (title, firstname, lastname, phone, sex, address,
           ) 
           select title, firstname, lastname, phone, sex, address,
           from guest where id=" . $row['id'];
         mysql_query($sql, $con) 
          or die("SQL Error hotel.inc@check_out:1 ".mysql_error());
       } else {
         $gco = mysql_fetch_array($result3);
	 $sql="update guest_checked_out set title='".$row['title'] 
	   . "', firstname='" . $row['firstname'] 
	   . "', lastname='" . $row['lastname']
	   . "', phone='" . $row['phone']
	   . "', sex='" . $row['sex']
	   . "', address='" . $row['address'] . "' where id=" . $gco['id'];
	 mysql_query($sql, $con);
       }
        $sql="delete from guest where room_id =".$row['room_id'];
        mysql_query($sql, $con) 
          or die("SQL Error hotel.inc@check_out:2 ".mysql_error());
		  
	####Update Room to Vacant
        $sql="update room set occupancy = 'Vacant', status='Dirty', 
          acc_id = 'NULL' where id=".$row['room_id'];
	mysql_query($sql, $con) 
          or die("SQL Error hotel.inc@check_out:4 ".mysql_error());
		 
	$bal2 = get_balance($guest_name, get_room($row['room_id'], $con), $con);
	/*
	if ($bal2 < 0) 
	  $bal2 = $bal2 * (-1);
	*/
	####Record Transaction. Billed to which room?####
	add_sales($date, $shift, get_user($uid, $con), $guest_name, 
	  $gr['doc_number'], get_room($row['room_id'], $con), 
          $t_details, 'Room', $p_type, $bal2,
	  '','','','','',$con);
     }
	  
     $sql="DELETE FROM account where id=".$gr['acc_id'];
     mysql_query($sql, $con) or 
       die("SQL Error hotel.inc@check_out:6 ".mysql_error());
		
     $sql="delete from group_company_guests where id={$gr['grp_cmp_id']}";
     mysql_query($sql, $con);  
   }	 
   return 1;
}
function add_booking ($f, $l, $t, $p, $rid, $s, $a, $ad, $dd, $doc_no, $amount, $p_type, $c, $c_b, $con) {
  if (($f == '')  || ($l == '') || ($t == '') || ($rid == '') ||
      ($s == '')  || ($a == '') || ($ad == '') ||
      ($dd == '') ||
      (strlen($f) < 2) || (strlen($f) > 30) ||
      (strlen($l) < 2) || (strlen($l) > 30)) {

      if (($c_b == 'Confirmed') && (($amount == '') || ($p_type == ''))) {
          return "Please enter the amount/payment type";
      } else {
       return "
          This Guest's firstname/lastname can only contain letters,
          digits, dashes and underscores, and should be between
          2 and 15 characters long. Please choose another";
     }
  } else {
      if(mysql_num_rows(mysql_query("SELECT * FROM booking 
        WHERE firstname ='$f' and lastname = '$l'")) > 0) {
          return "This Guest's firstname and lastname is taken.
        Please choose another";
      } else {
        mysql_query("INSERT INTO booking (firstname, lastname,
          title, phone, room_id, sex, address, arrival_date,
          departure_date, doc_no, deposit_amount, payment_type, comment)
         VALUES ('$f', '$l', '$t', '$p', '$rid', '$s', '$a',
                 '$ad', '$dd', '$doc_no', '$amount', '$p_type', '$c')", $con)
        or die("SQL Error hotel.inc@add_booking:1 ".mysql_error());

         #Then change the status of the room to 
         #Confirmed or Not Confirmed Booking
         if ($c_b == 'Confirmed') {
           mysql_query("UPDATE room set occupancy = 'Confirmed Booking' where
             id = '$rid'", $con)
           or die("SQL Error hotel.inc@add_booking:2 ". mysql_error());
         } else {
           mysql_query("UPDATE room set occupancy = 'Unconfirmed Booking' where
             id = '$rid'", $con) 
           or die("SQL Error hotel.inc@add_booking:3 ".mysql_error());
         } 
         return 1; 
      }
  }
}


function add_item ($n, $cat_id, $reorder_qty, $did, $con) {
  if (($n == '')  || ($cat_id == '') || 
      ($reorder_qty == '') || 
      (strlen($n) < 2) || (strlen($n) > 30)) { 
     return "
          Item names and its other details can only contain letters, 
          digits, dashes and underscores, and should be between 
          2 and 15 characters long. Please choose another";
  } else {
      if(mysql_num_rows(mysql_query("SELECT * FROM item  WHERE name ='$n'",
         $con)) > 0) {
        return "This Item name is taken. Please choose another";
      } else {
        mysql_query("INSERT INTO item (name, cat_id, reorder_qty, did) 
          VALUES ('$n', '$cat_id', '$reorder_qty', '$did'), $con")
        or die("SQL Error hotel@add_item:1 ".mysql_error());
         return 1;
      }
  }
}
function stock_movement($grn, $td, $rdid, $po, $iid, $qty, $cost, $movt, $con) {
  if (($grn == '')  || ($td == '') || ($rdid == '') || ($po == '') ||
      ($iid == '') ||  
      ($qty == '') || ($cost == '') || ($movt == '')) {
     return "
          Stock movement details can only contain letters, 
          digits, dashes and underscores, and should be between 
          2 and 15 characters long. Please choose another";
  } else {
    mysql_query("INSERT INTO stock_movement (grn, td, rdid, po, item_id, 
     qty, cost, movt) VALUES ('$grn', '$td', '$rdid', '$po', '$iid', 
     '$qty', '$cost', '$movt')", $con) 
   or die("SQL Error hotel.inc@stock_movement:1 ". mysql_error());
    return 1;
  }
}

function add_sales ($dt, $s, $sid, $n, $dn, $rn, $td, $did, $pt, $a, 
 $o1, $o2, $o3, $o4, $o5, $con) {
  $sql = "INSERT INTO sales (dt, shift, staff, name_of_guest, 
      doc_number, r_number, t_details, dept, p_type, amount, other1, 
	  other2, other3, other4, other5)
  VALUES ('$dt', '$s', '$sid', '$n', '$dn', '$rn', '$td', '$did', 
             '$pt', '$a', '$o1', '$o2', '$o3','$o4', '$o5')";
  mysql_query($sql, $con) 
    or die("SQL Error hotel.inc@add_sales:1 ".mysql_error());
  return mysql_insert_id();
}



function line_daily_turnover($sql, $con) { 

  $total = 0;

  $result = mysql_query($sql, $con);
  while($row = mysql_fetch_array($result)) {
    $total += $row['amount'];
  ?>
      <tr>
      
       <td><?=$row['doc_number']?></td>
       <td><?=$row['name_of_guest']?></td>
       <td><?=$row['dt']?></td>
       <td><?=$row['t_details']?></td>
       <td><?=$row['amount']?></td>
      </tr>
  <?
  }
  ?>
    <tr>
     <td>&nbsp;</td>
     <td>&nbsp;</td>
     <td>&nbsp;</td>
     <td>&nbsp;</td>
     <td><b><?=$total?></b></td>
    </tr>
  <?
  return $total;
}

function cashier_shift_report($sddate, $eddate, $shift, $con) {
  $total = 0; $sub_total = 0;
  ?>
        <tr>
         <td colspan="6">
          <table>
           <tr>
            <td><b>Start Date</b></td>
  <?
  if(empty($sddate)) {
    echo "<td>All Transactions</td>";
  } else {
    echo "<td>$sddate</td>";
  }
  echo "<td><b>End Date</b></td>";
  if (empty($eddate)) {
    echo "<td>All Transactions</td>";
  } else {
    echo "<td>$eddate</td>";
  }
  ?>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td><b>Shift</b></td>
            <td> <?=$_SESSION['shift']?> </td>
           </tr>
          </table>
         </td>
        </tr>
        <tr><td>&nbsp;</td></tr>
        <tr class='class1'>
         <td><b>Room</b></td>
         <td><b>Receipt No</b></td>
         <td><b>Name of Guest</b></td>
         <td><b>Date</td></td>
         <td><b>Transaction Details</b></td>
         <td><b>Amount</b></td>
        </tr>
		
  <?
  $dsql;
  if ((!empty($sddate)) && (!empty($eddate))) {
    $dsql = " dt between '$sddate' and '$eddate'";
  } elseif ((!empty($sddate)) && (empty($eddate))) {
    $dsql = " and dt = '$sddate'";
  } elseif ((!empty($eddate)) && (empty($sddate))) {
    $dsql = " and dt = '$eddate'";
  } else {
    $dsql = "";
  } 

  $sh;
  ####Determine the shift####
  if ($shift == 'All') {
    $sh = " shift is not null ";
  } else {
    $sh = " shift = '$shift' ";
  }
  $sql2 = "SELECT r_number, doc_number, name_of_guest, 
    dt, t_details, amount from sales 
    where $sh and dept = 'Accommodation' and 
       (p_type = 'Cash Sales' or p_type='Cash Received' or p_type='Cash Deposit') and $dsql ";
   //echo $sql2;
   
  $result = mysql_query($sql2, $con);
  if (mysql_num_rows($result) > 0) {
    echo "<tr><td><b>Accommodation</b></td></tr>";
	while($r = mysql_fetch_array($result)) {
      $sub_total += $r['amount'];
      ?>
      <tr>
       <td><?=$r['r_number']?>
      
       </td>
       <td><?=$r['doc_number']?></td>
       <td><?=$r['name_of_guest']?></td>
       <td><?=$r['dt']?></td>
       <td><?=$r['t_details']?></td>
       <td><?=$r['amount']?></td>
      </tr>
    <?
    } 
    ?>
    <tr>
     <td>&nbsp;</td>
     <td>&nbsp;</td>
     <td>&nbsp;</td>
     <td>&nbsp;</td>
     <td>&nbsp;</td>
     <td><b><?=$sub_total?></b></td>
    </tr>
    <?
    $total += $sub_total;
    $sub_total = 0;
  }

  $result2 = mysql_query("select dept from sales where dept in 
    (select name from department) group by dept", $con);
  while($dept = mysql_fetch_array($result2)) {
    $sql2 = "SELECT r_number, doc_number, name_of_guest, 
     dt, t_details, amount from sales 
     where $sh and dept = '" . $dept['dept'] . "' 
       and (p_type = 'Cash Sales' or p_type='Cash Received' or p_type='Cash Deposit') and $dsql ";
	echo $sql2;
    $result3 = mysql_query($sql2, $con);
    if (mysql_num_rows($result3) > 0) {
	  ?>
      <tr><td>&nbsp;</td></tr>
      <tr><td><b><?=$dept['dept']?></b></td></tr>
      <?
      while($r = mysql_fetch_array($result3)) {
	    $sub_total += $r['amount'];
        ?>
         <tr>
          <td><?=$r['r_number']?>
		   </td>
          <td><?=$r['doc_number']?></td>
          <td><?=$r['name_of_guest']?></td>
          <td><?=$r['dt']?></td>
          <td><?=$r['t_details']?></td>
          <td><?=$r['amount']?></td>
         </tr>
        <?
      }  
    }
	##Why are we doing this
    #####WE SEARCH AGAIN FOR 'walkin' CASH SALES#####
    $sql3 = "SELECT doc_number, name_of_guest, dt, t_details, amount from
                 sales where r_number = 'walkin' and shift is not null and 
                 dept = '" . $dept['dept'] . "' 
                 and (p_type = 'Cash Sales' or p_type='Cash Received' or p_type='Cash Deposit') 
                 and $dsql ";
    $result4 = mysql_query($sql3, $con);
    if ((mysql_num_rows($result3) == 0) && (mysql_num_rows($result4)>0)) {
      ?>
       <tr><td>&nbsp;</td></tr>
       <tr><td><b><?=$dept['dept']?></b></td></tr>
      <?
    }
    if (mysql_num_rows($result4)>0) {
      ?><tr><td>&nbsp;</td></tr>
      <?
  	  while($r2 = mysql_fetch_array($result4)) {
        $sub_total += $r2['amount'];
        ?>
         <tr>
          <td>Walk In</td>
          <td><?=$r2['doc_number']?></td>
          <td><?=$r2['name_of_guest']?></td>
          <td><?=$r2['dt']?></td>
          <td><?=$r2['details']?></td>
          <td><?=$r2['amount']?></td>
         </tr>
        <?
      }
    }
    if ($sub_total > 0) {
      ?>
       <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
        <td><b><?=$sub_total?></b></td>
       </tr> 
      <?
      $total += $sub_total;
      $sub_total = 0;
    }
  }
  ?>
  <tr><td>&nbsp;</b></td></tr>
  
    <tr>
     <td><h3>Total Received</h3></td>
     <td>&nbsp;</td>
     <td>&nbsp;</td>
     <td>&nbsp;</td>
     <td>&nbsp;</td>
     <td><h3><?=$total?></h3></td>
    </tr>
  </table>
  <?
  main_footer();
}

function summary_report($type, $sddate, $eddate, $room_category, $con) {
  $total_charges = array();
  $total = 0; $sub_total = 0;  $t = 0;
    
  if ($type == 'Room') { 
    ?>
    <!--Title Headings-->
    <tr>
     <td>
      <table style='table-layout:auto;' border='1'>
       <tr>
        <td><b>Start Date</b></td>
        <td><?=$sddate?> </td>
        <td><b>End Date</b></td>
        <td><?=$eddate?></td>
        <td colspan='11'>&nbsp;</td> 
       </tr>
      </table>
     </td>
    </tr>
    <tr>
     <td>
      <table cellpadding='0' border='1' cellspacing='2'>
       <tr class='class1'>
        <td><b>Bill No</b></td>
	<td><b>Guest</b></td>
	<td><b>Depature</b></td>
	<td><b>Phone</b></td>
	<td><b>Room</b></td>
	<td><b>Room Rate</b></td>
	<?php 
	$result_c = mysql_query("select * from charges order by id") 
          or die(mysql_error());
        while ($row_c = mysql_fetch_array($result_c)) {
          $total_charges[$row_c['name']] = 0;
	  echo "<td><b>{$row_c['name']}</b></td>";
	 }
	?>
	<td><b>Actual RR</b></td>
	<td><b>Deposit</b></td>
        <td><b>Payments</b></td>
	<td><b>Refund</b></td>
	<td><b>Balance</b></td>
       </tr>
       <?
       ####Is this room_category=All or room_category=Room Category Type
       $dtotal = 0;
       if ($room_category == 'All') 
         $dsql = " and dt between '$sddate' and '$eddate'";
       else 
         $dsql = "and r_number like '%{$room_category}' 
           and dt between '$sddate' and '$eddate'";

       ####Lets seive out all the Cash receipts and payments from 
       ### Reception by Guest####
       $sql = "select * from sales where dept='Room' 
         $dsql group by name_of_guest order by dt";
       $result1 = mysql_query($sql, $con);
       $total_rr = 0.0;
       $total_cr = 0.0;
       $total_cd = 0.0;
       $total_rf = 0.0;
       $total_actual_rr = 0.0;
  
       while ($row = mysql_fetch_array($result1)) {
         $bill_no = ""; 
         //If this is a group account then store all the bill numbers 
         //for each guest here
	 $room_number = ""; //If a group account, 
          //then this contains rooms that were booked under the group account
	 $number_of_rooms = 0;  //If a group account, 
          //then record the number of rooms booked under the group account
	 $actual_rr = 0; //If a group account, 
          //then add up all the room_rate for all the rooms 
          //booked under this group account
	 $cash_deposit = 0;
	 $cash_received = 0;
         $cash_refund = 0;
	 
	 //Seive out all Credit Sales
         $sql = "SELECT doc_number, name_of_guest, r_number, sum(amount) 
           as 'amount', other1, other2, other3 from sales where  
           dept = 'Room' and p_type = 'Credit Sales' 
           and name_of_guest='{$row['name_of_guest']}' $dsql 
           group by name_of_guest";
	 $result = mysql_query($sql, $con);
         $row2 = mysql_fetch_array($result);
         $rr = 0;	
	 if (mysql_num_rows($result) > 0) {
	   if(is_group_account($row2['name_of_guest'])) {
	     $sql="select * from sales where 
              name_of_guest='{$row2['name_of_guest']}' 
              and p_type='Credit Sales' and dept='Room' 
              $dsql group by r_number";
	     $result = mysql_query($sql, $con);
	     while($row = mysql_fetch_array($result)) {
	       $bill_no .= $row['doc_number'] . ", ";
	       $room_number .= $row['r_number'] . ", ";
	       $actual_rr += $row['amount'];
	       $number_of_rooms++;
	     }
	   } else {
             $bill_no = $row2['doc_number'];
             $room_number = $row2['r_number'];
	     $actual_rr = $row2['amount'];
		
	     //Get the number of rooms occupied, but avoid counting 
             //entries that are 'Room Rate Auto Billing'
	     //May be for individual accounts you can only have them 
             //occupy one room. 
	     //But for extra testing lets just check
		
	     $sql= "select * from sales 
              where name_of_guest='{$row2['name_of_guest']}' 
              and p_type='Credit Sales' and dept='Room' 
	      and t_details != 'Room Rate Auto Billing' $dsql";

            /*
	     $sql= "select * from sales 
              where name_of_guest='{$row2['name_of_guest']}' 
              and p_type='Credit Sales' and dept='Room' $dsql";
             echo "$sql<br>";
             */
	     $result = mysql_query($sql, $con);
	     $number_of_rooms = mysql_num_rows($result);
           }		
           ?>
           <tr>
            <td><?=$bill_no?></td>
	    <td><?=$row2['name_of_guest']?></td>
	    <!--<td><?=$row2['other1']?></td>-->
	    <td><?=$row2['other2']?></td>
	    <td><?=$row2['other3']?></td>
	    <td><?php echo"(" . $number_of_rooms . ")$room_number"; ?></td>
	    <td>
	    <? echo number_format($actual_rr, 2); ?></td>
            <?
	    $total_rr += $actual_rr;
	   
	    //List all charges;
	    $charges = array();
	    $r_temp = mysql_query("select * from charges order by id") 
             or die(mysql_error());	  
            while($row_temp = mysql_fetch_array($r_temp)) 
              $charges[$row_temp['name']] = 0;


	    $result_c = mysql_query("select * from charges order by id") 
             or die(mysql_error());	  
	    while ($row_c = mysql_fetch_array($result_c)) {
              //Get those charges that have entries in Sales table
	      $sql= "select sum(amount) as 'sum' from sales 
               where name_of_guest='{$row2['name_of_guest']}' 
               and p_type='Charges' and dept='Room' 
	       and t_details = '{$row_c['name']}' $dsql";

              $result_s = mysql_query($sql) or die(mysql_error()); 
              $row_s = mysql_fetch_array($result_s); 
	      $charges[$row_c['name']] += $row_s['sum']; 
	      $total_charges[$row_c['name']] += $row_s['sum']; 
	      echo "<td>" . number_format($row_s['sum'], 2) . "</td>";
	    }
	   
	    $actual_rr = ($actual_rr + (array_sum($charges)));
	    $total_actual_rr += $actual_rr;
	    echo "<td>".number_format($actual_rr, 2)."</td>";
	   
            //Cash Deposit
            $sql = "SELECT sum(amount) as 'amount' from sales where  
              dept = 'Room' and p_type = 'Cash Deposit' 
              and name_of_guest='{$row['name_of_guest']}' 
              $dsql group by name_of_guest";
            $result = mysql_query($sql, $con);
	    $row3 = mysql_fetch_array($result);
	  
	    //Is this a group account
	    if(is_group_account($row2['name_of_guest'])) {
	      $sql="select sum(amount) as 'amount' from sales where dept='Room' 
	        and p_type='Cash Deposit' 
                and name_of_guest='{$row2['name_of_guest']}' 
              $dsql group by name_of_guest";	
	      $result = mysql_query($sql, $con);
	      while($row = mysql_fetch_array($result)) {
	        $cash_deposit += $row['amount'];
	      }
	    } else {
              $cash_deposit = $row3['amount'];
            }
	    echo "<td>" . number_format($cash_deposit,2) ."</td>";
	    $total_cd += $cash_deposit;
	
	  
	    //Cash Received
            $sql = "SELECT sum(amount) as 'amount' from sales where  
              dept = 'Room' and p_type = 'Cash Received' 
              and name_of_guest='{$row['name_of_guest']}' 
              $dsql group by name_of_guest";
            $result = mysql_query($sql, $con);
	    $row4 = mysql_fetch_array($result);
	  
	    //Is this a group account
	    if(is_group_account($row2['name_of_guest'])) {
	      $sql="select sum(amount) as 'amount' from sales 
                where dept='Room' and p_type='Cash Received' 
                and name_of_guest='{$row2['name_of_guest']}' 
                $dsql group by name_of_guest";;
	      $result = mysql_query($sql, $con);
	      while($row = mysql_fetch_array($result)) {
	        $cash_received += $row['amount'];
	      }
	    } else {
              $cash_received = $row4['amount'];
            }
	    echo "<td>" . number_format($cash_received,2) ."</td>";
	    $total_cr += $cash_received;
	
            //Get Cash Refund	
	    $sql = "SELECT sum(amount) as 'amount' from sales where  
              dept = 'Room' and p_type = 'Cash Refund' 
              and name_of_guest='{$row['name_of_guest']}' 
              $dsql group by name_of_guest";
            $result = mysql_query($sql, $con);
	    $row5 = mysql_fetch_array($result);
	  
	    //Is this a group account
	    if(is_group_account($row2['name_of_guest'])) {
	      $sql="select sum(amount) as 'amount' from sales where dept='Room' 
	        and p_type='Cash Refund' 
                and name_of_guest='{$row2['name_of_guest']}' 
              $dsql group by name_of_guest";;
	      $result = mysql_query($sql, $con);
	      while($row = mysql_fetch_array($result)) {
	        $cash_refund += $row['amount'];
	      }
	    } else {
              $cash_refund += $row5['amount'];
            }
	  
	    $total_rf += $cash_refund;
	    echo "<td>" . number_format($cash_refund,2) ."</td>";
	  
	    echo "<td>" . 
              number_format(($actual_rr - (($cash_deposit + $cash_received) 
               + $cash_refund)),2) . "</td>";
	    echo "</tr>";
	 }
       } 
       echo "<tr><td><h1>Total<h1></td>
         <td></td>
         <td></td>
         <td></td>
         <td></td>
         <td><b>". number_format($total_rr, 2)       ."</b></td>";
       foreach($total_charges as $name => $amount) {
         echo "<td><b>" . number_format($amount, 2) . "</b></td>";
       }
       echo "
         <td><b>". number_format($total_actual_rr, 2)."</b></td>
	 <td><b>". number_format($total_cd, 2)       ."</b></td>
	 <td><b>". number_format($total_cr, 2)       ."</b></td>
	 <td><b>". number_format($total_rf, 2)       ."</b></td>
	 <td><b>" . 
            number_format($total_actual_rr - (($total_cd + $total_cr) 
            + $total_rf), 2) ."</b></td></tr>";
       echo("</td></table>");
       echo("<tr><td>&nbsp;</td></tr>");
       exit;
  } else if ($type != 'Room') {
  ?>
  <tr>
   <td>
    <table style='table-layout:auto;' border='1'>
     <tr>
      <td><b>Start Date</b></td>
      <td><?=$sddate?> </td>
      <td><b>End Date</b></td>
      <td><?=$eddate?></td>
      <td colspan='11'>&nbsp;</td>
     </tr>
    </table>
   </td>
  </tr>
  <tr>
   <td>
    <table cellpading='0' border='1' cellspacing='2'>
     <tr class='class1'>
      <td><b>Bill No</b></td>
      <td><b>Date</b></td>
      <td><b>Name of Customer</b></td>
      <td><b>Cash Sales</b></td>
      <td><b>Credit Sales</b></td>
      <td><b>Cash Received</b></td>
     </tr>
     <?
     $dtotal = 0;
     $dsql = " and dt between '$sddate' and '$eddate'";
  
     $sql = "select * from sales where dept='$type' 
      $dsql group by name_of_guest order by dt";
     $result1 = mysql_query($sql, $con);
  
     $total_cash_sales = 0;
     $total_credit_sales = 0;
     $total_cash_received = 0;
  
     echo "<tr>";
     while ($row = mysql_fetch_array($result1)) {
       ?>
       <td><?=$row['doc_number']?></td>
       <td><?=$row['dt']?></td>
       <td><?=$row['name_of_guest']?></td>
       <?   
       $sql = "SELECT doc_number, dt, name_of_guest, t_details, 
         sum(amount) as 'amount' from sales where  
         dept = '$type' and p_type = 'Cash Sales' 
         and name_of_guest='{$row['name_of_guest']}' 
       $dsql group by name_of_guest ";
       $result = mysql_query($sql, $con);
       if($result) {
         $rowx = mysql_fetch_array($result);
         if (count($rowx) > 1) {
           echo "<td>" . number_format($rowx['amount'], 2) . "</td>";
	   $total_cash_sales += $rowx['amount'];
         } else {
	   echo "<td>&nbsp;</td>";
         }
       }

       //Credit Sales
       $sql = "SELECT doc_number, dt, name_of_guest, 
         t_details, sum(amount) as 'amount' from sales where  
         dept = '$type' and p_type = 'Credit Sales' 
         and name_of_guest='{$row['name_of_guest']}' $dsql 
         group by name_of_guest";
       $result = mysql_query($sql, $con);
       if ($result) {
         $row3 = mysql_fetch_array($result);
         if (count($row3) > 1) {
           echo "<td>" . number_format($row3['amount'], 2) . "</td>";
           $total_credit_sales += $row3['amount'];
         } else {
           echo "<td>&nbsp;</td>";
         }
       }
	
       //Cash Received
       $sql = "SELECT doc_number, dt, name_of_guest, t_details, 
         sum(amount) as 'amount' from sales where  
         dept = '$type' and p_type = 'Cash Received' 
         and name_of_guest='{$row['name_of_guest']}' 
         $dsql group by name_of_guest";
       $result = mysql_query($sql, $con);
       if ($result) {
         $row4 = mysql_fetch_array($result);
         if (count($row4) > 1) {
           echo "<td>" . number_format($row4['amount'], 2) . "</td>";
	   $total_cash_received += $row4['amount'];
         } else {
           echo "<td>&nbsp;</td>";
         }
       }
       echo "</tr>";
     }
     echo("<tr><td><h1>Total<h1></td>
            <td></td>
	    <td></td>
            <td><b>". number_format($total_cash_sales, 2)."</b></td>
	    <td><b>". number_format($total_credit_sales, 2)."</b></td>
	    <td><b>". number_format($total_cash_received, 2)."</b></td>
	   </tr>");
     echo("</table></td>");
     echo("<tr><td>&nbsp;</td></tr>");
  }
  ?>
     </table>
    </td>
   </tr>
  </table>
  <?
  main_footer();
}

function turnover_report($sddate, $eddate, $con) {
  $total = 0; $sub_total = 0;  $t = 0;
?>
  <tr><td>
      <table>
        <tr>
         <td><b>Start Date</b></td>
         <td><?=$sddate?> </td>
         <td><b>End Date</b></td>
        <td><?=$eddate?></td>
        </tr>
        <tr class='class1'>
         <td><b>Transaction</b></td>
         <td><b>Name of Guest</b></td>
         <td><b>Date</b></td>
         <td><b>Transaction Details</b></td>
         <td><b>Amount</b></td>
        </tr>
<?
  $dtotal = 0;
  $dsql = " and dt between '$sddate' and '$eddate'";
  $sql2 = "SELECT * from sales where  
    dept = 'Accommodation' and p_type = 'Credit Sales' ";
  $sql2 .= $dsql;

  ##Since Accommodation is not officially in a Department 
  ##we have to print Credit and Cash Sales for Accommodation Department

  echo("<tr><td><b>Accommodation</b></td></tr>");

  #Print Credit Sales for Accommodation Department
  //echo("<tr><td><b>Credit</b></td></tr>");
  $sub_total = line_daily_turnover($sql2, $con);
  $dtotal = $sub_total;
  

  $sql2 = "SELECT * from sales where  
    dept = 'Accommodation' and (p_type = 'Cash Sales') ";
  $sql2 .= $dsql;

  //Lets just keep track of the total room rate for
  //all rooms and avoid cash for now
  //echo("<tr><td><b>Cash Sales</b></td></tr>");
  //$sub_total = line_daily_turnover($sql2, $con);
  //$dtotal += $sub_total;
  
  $total += $dtotal;
  echo("<tr><td>Total Accomodation</td><td colspan='4' style='text-align:right'><b>$dtotal</b></td></tr>");
  echo("<tr><td>&nbsp;</td></tr>");
  
  ##Lets now print sales info for other departments
  $sql = "select dept from sales 
   where dept in (select name from department) group by dept"; 
   
  $result = mysql_query($sql, $con);
  while($dept = mysql_fetch_array($result)) {
      $dtotal = 0;
      echo("<tr><td><b>" . $dept['dept'] ."</b></td></tr>");

      $sql2 = "SELECT * from sales where  
       dept = '" . $dept['dept'] . "' and p_type = 'Credit Sales' ";
      $sql2 .= $dsql;
 
      echo("<tr><td><b>Credit</b></td></tr>");
      $sub_total = line_daily_turnover($sql2, $con);
      $dtotal = $sub_total;
      	  
      #Print Cash Sales for each Department
      $sql2 = "SELECT * from sales where  
       dept = '" . $dept['dept'] . "' and p_type = 'Cash Sales' ";
      $sql2 .= $dsql;

      echo("<tr><td><b>Cash</b></td></tr>");
      $sub_total = line_daily_turnover($sql2, $con);
	  $dtotal += $sub_total;
	  $total += $dtotal;
	  
	  echo("<tr><td>" . $dept['dept'] ."</td><td colspan='4' style='text-align:right'><b>$dtotal</b></td></tr>");
      echo("<tr><td>&nbsp;</td></tr>");
    }
?>
        <tr>
         <td><h3>Total Sales</h3></td>
         
         <td colspan='4' style='text-align:right;'><h3><?=$total?></h3></td>
        </tr>
       </table>
      </td>
     </tr>
    </table>
<?
  main_footer();
}

