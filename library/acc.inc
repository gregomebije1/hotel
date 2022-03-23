<?php

####DEBIT BALANCE
define("FIXED_ASSETS", 1);
define("ACCOUNT_RECEIVABLE", 3);
define("OTHER_CURRENT_ASSETS", 5);
define("EXPENSES", 7);
define("SALES_RETURNS_AND_ALLOWANCES", 9);
define("OPENING_STOCK", 11);
define("PURCHASES", 13);
define("CLOSING_STOCK", 15);

####CREDIT BALANCE
define("ACCOUNT_PAYABLE", 2);
define("OTHER_CURRENT_LIABILITIES", 4);
define("LONG_TERM_LIABILITIES", 6);
define("EQUITY", 8);
define("INCOME", 10);
define("PURCHASES_RETURNS_AND_ALLOWANCES", 12);

function format_balance($acc_type_id, $amt) {
  if(($acc_type_id % 2) == 0) {  //Credit Balance
    if ($amt < 0) {
	  $amt = $amt * -1;
	  $amt = "(" . number_format($amt, 2) . ") Dr";
	} else {
	 $amt = number_format($amt, 2) . " Cr";
	}
  } else if (($acc_type_id % 2) != 0) {  //Debit Balance
    if ($amt < 0) {
	  $amt = $amt * -1;
	  $amt = "(" . number_format($amt, 2) . ") Cr";
	} else {
	  $amt = number_format($amt, 2) . " Dr";
	}
  }
  return $amt;
}
function j_entry2 ($t_type, $entity_id, $a_id, $date, $descr, $amt, $con) {
  $p_bal = 0;
  $new_bal = 0;
  $acc_type_id  = 0;
  
  //$p_bal = get_acc_bal($a_id, $entity_id, '0000-00-00', date('Y-m-d'), $con);
  //Get the previous balance
  $sql = "select a.acc_type_id, j.amt, j.t_type from journal j join account a on 
    j.acc_id = a.id 
    where j.acc_id=$a_id and j.entity_id = $entity_id 
    and (d_entry between '0000-00-00' and '$date')
    order by j.id";
  $result = mysql_unbuffered_query($sql, $con) or die(mysql_error());
  $bal = 0;
  
  while($row = mysql_fetch_array($result)) {
    $acc_type_id = $row['acc_type_id'];
    #DEBIT BALANCE
    if(($acc_type_id % 2) == 1) {
      if($row['t_type'] == 'Debit') {
        $p_bal = $bal + $row['amt'];
      }
      else if ($row['t_type'] == 'Credit') {
        $p_bal = $bal - $row['amt']; 
      }
    } 

    #CREDIT BALANCE
    if(($acc_type_id % 2) == 0) {
      if ($row['t_type'] == 'Credit') {
        $p_bal = $bal + $row['amt'];
      }
      else if ($row['t_type'] == 'Debit') {
        $p_bal = $bal - $row['amt'];
      }
    }
  }
  
  
  #Accounts with debit balances have odd acc_type_id
  #Accounts with credit balances have even acc_type_id

  #DEBIT BALANCE
  if(($acc_type_id % 2) == 1) {
    if($t_type == 'Debit') {
        $new_bal = $p_bal + $amt; 
		
     } else if ($t_type == 'Credit') {
       if($p_bal == 0) {
         $new_bal = $p_bal - $amt; //What does this mean, should it matter
       } else {
        $new_bal = $p_bal - $amt;
       }
     }
  #CREDIT BALANCE
  } else if(($acc_type_id % 2) == 0) {
      if ($t_type == 'Credit') {
        $new_bal = $p_bal + $amt; 
      } else if ($t_type == 'Debit') { 
        if($p_bal == 0) { //What does this mean, should it matter
          $new_bal = $p_bal - $amt;
        } else {
          $new_bal = $p_bal - $amt;
        }
      }
  }
  /*$sql="($a_id, '$entity_id', '$date',
     '$descr', '$t_type', '$amt', '$new_bal')";
  */
  $sql="($a_id, '$entity_id', '$date','$descr', '$t_type', '$amt')";
  return $sql;
}



function j_entry ($t_type, $entity_id, $a_id, $date, $descr, $amt, $con) {
  $p_bal = 0;
  $new_bal = 0;
  $p_bal = get_acc_bal($a_id, $entity_id, '0000-00-00', date('Y-m-d'), $con);
  
  #Get the account type
  $result = mysql_query("select acc_type_id from account where id=$a_id
      and entity_id= $entity_id", $con) or die(mysql_error());
  $acc_type = mysql_fetch_array($result);
  $acc_type_id = $acc_type['acc_type_id'];
  
  //$acc_type_id = get_acc_type($a_id, $entity_id, $con);
  
  #Accounts with debit balances have odd acc_type_id
  #Accounts with credit balances have even acc_type_id

  #DEBIT BALANCE
  if(($acc_type_id % 2) == 1) {
    if($t_type == 'Debit') {
        $new_bal = $p_bal + $amt; 
		
     } else if ($t_type == 'Credit') {
       if($p_bal == 0) {
         $new_bal = $p_bal - $amt; //What does this mean, should it matter
       } else {
        $new_bal = $p_bal - $amt;
       }
     }
  #CREDIT BALANCE
  } else if(($acc_type_id % 2) == 0) {
      if ($t_type == 'Credit') {
        $new_bal = $p_bal + $amt; 
      } else if ($t_type == 'Debit') { 
        if($p_bal == 0) { //What does this mean, should it matter
          $new_bal = $p_bal - $amt;
        } else {
          $new_bal = $p_bal - $amt;
        }
      }
  }
  /*
  $sql = "insert into journal (acc_id, entity_id, d_entry, descr, 
     t_type, amt, bal) values  ('$a_id', '$entity_id', '$date',
     '$descr', '$t_type', '$amt', '$new_bal')";
  */
  
  $sql = "insert into journal (acc_id, entity_id, d_entry, descr, 
     t_type, amt) values  ('$a_id', '$entity_id', '$date',
     '$descr', '$t_type', '$amt')";
  
   mysql_query($sql, $con)  or die(mysql_error());
  return mysql_insert_id($con);
}

function del_acc($acc_id, $entity_id, $con) {
 if (account_exists(get_acc_name($acc_id, $con), $con)) {
   $sql= "delete from account where id=$acc_id";
   mysql_query($sql, $con) or die(mysql_error());
 } else {
   die ('This account does not exist');
 }
}

function get_acc_type($acc_id, $entity_id, $con) {
  $result = mysql_query("select acc_type_id from account where id=$acc_id
      and entity_id= $entity_id", $con) or die(mysql_error());
  $acc_type = mysql_fetch_array($result);
  return $acc_type['acc_type_id'];
}
/*
function get_acc_bal($acc_id, $entity_id, $sdate, $edate, $con) {
  $acc_type_id = get_acc_type($acc_id, $entity_id, $con);

  #Accounts with debit balances have odd acc_type_id
  #Accounts with credit balances have even acc_type_id

  $sql = "select j.amt, j.t_type from journal j join account a on 
    j.acc_id = a.id 
    where j.acc_id=$acc_id and j.entity_id = $entity_id 
    and (d_entry between '$sdate' and '$edate')
    order by j.id";
  //echo "$sql<br>";
  $result = mysql_query($sql, $con) or die(mysql_error());
  $bal = 0;

  while($row = mysql_fetch_array($result)) {
    #DEBIT BALANCE
    if(($acc_type_id % 2) == 1) {
      if($row['t_type'] == 'Debit') {
        $bal = $bal + $row['amt'];
      }
      else if ($row['t_type'] == 'Credit') {
        $bal = $bal - $row['amt']; 
      }
    } 

    #CREDIT BALANCE
    if(($acc_type_id % 2) == 0) {
      if ($row['t_type'] == 'Credit') {
        $bal = $bal + $row['amt'];
      }
      else if ($row['t_type'] == 'Debit') {
        $bal = $bal - $row['amt'];
      }
    }
  }
  return $bal;
}
*/


function get_acc_bal($acc_id, $entity_id, $sdate, $edate, $con) {
    
  
  $acc_type_id = get_acc_type($acc_id, $entity_id, $con);

  #Accounts with debit balances have odd acc_type_id
  #Accounts with credit balances have even acc_type_id

  $sql = "select j.amt, j.t_type from journal j join account a on 
    j.acc_id = a.id 
    where j.acc_id=$acc_id and j.entity_id = $entity_id 
    and (d_entry between '$sdate' and '$edate')
    order by j.id";
  //echo "$sql<br>";
  $result = mysql_query($sql, $con) or die(mysql_error());
  $bal = 0;

  while($row = mysql_fetch_array($result)) {
    #DEBIT BALANCE
    if(($acc_type_id % 2) == 1) {
      if($row['t_type'] == 'Debit') {
        $bal = $bal + $row['amt'];
      }
      else if ($row['t_type'] == 'Credit') {
        $bal = $bal - $row['amt']; 
      }
    } 

    #CREDIT BALANCE
    if(($acc_type_id % 2) == 0) {
      if ($row['t_type'] == 'Credit') {
        $bal = $bal + $row['amt'];
      }
      else if ($row['t_type'] == 'Debit') {
        $bal = $bal - $row['amt'];
      }
    }
  }
  if (get_value('account', 'children', 'id', $acc_id, $con) == 0)
    return $bal;
  else {
    $sql="select * from account where parent=$acc_id";
	$result = mysql_query($sql, $con) or die(mysql_error());
	while($row = mysql_fetch_array($result)) 
      $bal += get_acc_bal($row['id'], $entity_id, $sdate, $edate, $con);
	return $bal;
  }
}

function account_delete($acc_id, $entity_id, $con) {
  if (get_value('account', 'children', 'id', $acc_id, $con) == 0) {
    $sql="delete from journal where acc_id=$acc_id";
	mysql_query($sql, $con) or die(mysql_error());
	
	$sql="delete from account where id=$acc_id";
	mysql_query($sql, $con) or die(mysql_error());
    return;
 } else {
    $sql="select * from account where parent=$acc_id";
	$result = mysql_query($sql, $con) or die(mysql_error());
	while($row = mysql_fetch_array($result)) {
	  
      account_delete($row['id'], $entity_id, $con);
	}
	return;
  }
}


function get_acc_id($n, $con) {
  $sql= "select id from account where name='$n'";
  $result = mysql_query($sql) or die(mysql_error(). 'Error here');
  $row = mysql_fetch_array($result);
  return $row['id'];
}
function account_exists($n, $con) {
  $sql="select id from account where name=\"$n\""; 
  $result = mysql_query($sql, $con) or die(mysql_error());
  if (mysql_num_rows($result) > 0) 
    return True;
  else return False;
} 
 
function get_acc_name($id, $con) {
  $sql="select name from account where id=$id";
  $result = mysql_query($sql, $con) or die(mysql_query());
  $row = mysql_fetch_array($result);
  return $row['name'];
}
function update_account($id, $n, $c, $d, $con) {
  mysql_query("Update account set name='$n', code='$c', description='$d'
	 where id=$id", $con) or die(mysql_query());
	return "Successfully Updated";
}
function add_account($n, $c, $d, $t, $entity_id, $date, $parent, $children, $con) {
  $result = mysql_query("SELECT * FROM account WHERE name ='$n'", $con)
    or die("Cannot execute SQL query acc.inc@add_accounts:1 " . mysql_query());
  if (mysql_num_rows($result) > 0) {
    return "This account name is taken. Please choose another";
  } else {
     $sql="INSERT INTO account (acc_type_id, entity_id, name, code, description,
      d_created, parent, children) VALUES ('$t', '$entity_id', '$n', '$c', '$d', '$date', $parent, $children)";
	 //echo "$sql<br>";
 	 mysql_query($sql, $con)or die(mysql_query());
	 $id = mysql_insert_id();
	 
	 // note that our parent now has a child
    $sql = "update account set children = 1 where id=$parent";
    mysql_query($sql, $con) or die(mysql_error());
	
	return $id;

  }
}



function get_accounts($sql, $sdate, $edate, $con) { 
  $total = 0;
  $result = mysql_query($sql, $con) or die(mysql_error());
  
  if (mysql_num_rows($result) > 0) {
    while($rows = mysql_fetch_array($result)) {
      $bal = 0;
      $bal = get_acc_bal($rows['id'], 1,  $sdate, $edate, $con);
        $total += $bal;
    }
  } 
  return $total;
}

function print_accounts($sql, $name, $sdate, $edate, $con) { 
  $total = 0;
  $result = mysql_query($sql, $con) or
   die(mysql_error());
  
  echo("<tr><td><b>$name</b></td></tr>");
  if (mysql_num_rows($result) > 0) {
    while($rows = mysql_fetch_array($result)) {
      $bal = 0;
        $bal = get_acc_bal($rows['id'], 1,  $sdate, $edate, $con);
        echo("<tr><td>" . $rows['name'] ."</td>");
        echo "<td>" . number_format($bal, 2) . "</td></tr>";
        $total += $bal;
    }
    echo "<tr><td>&nbsp;</td><td>&nbsp;</td><td><b>" 
     . number_format($total, 2) . "</b></td></tr>";
  } 
  return $total;
}

function get_acc_type_bal($id, $sdate, $edate, $con) {
  $total = 0;
  $sql = "SELECT * FROM account where acc_type_id=$id";
  $result = mysql_query($sql, $con) or die(mysql_error());
  if (mysql_num_rows($result) > 0) {
    while($rows = mysql_fetch_array($result)) {
      $bal = 0;
      $bal = get_acc_bal($rows['id'], 1,  $sdate, $edate, $con);
      $total += $bal;
    }
  }
  return $total;
}

##########
function profit_and_loss($sdate, $edate, $con) {
  $sales = get_acc_type_bal(INCOME, $sdate, $edate, $con);

  $sql = "SELECT * FROM account where id =4";
  $cogs= get_accounts($sql,$sdate, $edate, $con);
  $gross_profit = $sales - $cogs;
  
  #Process Expenses
  $sql = "SELECT * FROM account where acc_type_id =" . EXPENSES
	 . " and id != 4";
  $expenses = get_accounts($sql, $sdate, $edate, $con);
 
  $net_profit = $gross_profit - $expenses;

  return $net_profit;
}

function profit_and_loss_modified($sdate, $edate, $con) {
  $sql="select * from charges";
  $title = "Less ";
  $sql2 = "(";
  $result = mysql_query($sql) or die(mysql_error());
  $num = mysql_num_rows($result);

  while($row = mysql_fetch_array($result)) {
    --$num;
    $title .= " {$row['name']} ";
    if ($num == 0)
      $sql2 .= "id={$row['acc_id']} ";
    else
     $sql2 .= "id={$row['acc_id']} or ";
  }
  $sql2 .= " ) ";

  #Process Tax and Service and other Charges
  $sql = "SELECT * FROM account where
   acc_type_id=" . INCOME . " and $sql2 ";
  $charges = get_accounts($sql, $sdate, $edate, $con);

  $sales = get_acc_type_bal(INCOME, $sdate, $edate, $con);
  $net_sales = $sales - $charges;


  $opening_stock = get_acc_type_bal(OPENING_STOCK, $sdate, $edate, $con);
  $purchases = get_acc_type_bal(PURCHASES, $sdate, $edate, $con);
  $clo_stock = get_acc_type_bal(CLOSING_STOCK, $sdate, $edate, $con);

  $sum_opn_stck_purc = $opening_stock + $purchases;

  $cogs = $sum_opn_stck_purc - $clo_stock;
  $gross_profit = $net_sales - $cogs;

  $expenses = get_acc_type_bal(EXPENSES, $sdate, $edate, $con);
  $net_profit = $gross_profit - $expenses;

  return $net_profit;
}
?>
