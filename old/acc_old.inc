<?php

####DEBIT BALANCE
define("FIXED_ASSETS", 1);
define("ACCOUNT_RECEIVABLE", 3); 
define("ACCOUNT_LIABILITIES", 2); //Same thing

define("OTHER_CURRENT_ASSETS", 5);
//define("EXPENSES", 5);
define("EXPENSES", 25);
define("SALES_RETURNS_AND_ALLOWANCES", 9);
define("COST_OF_GOODS_SOLD", 7);
define("OPENING_STOCK", 11);
define("PURCHASES", 13);
define("CLOSING_STOCK", 15);

####CREDIT BALANCE
define("ACCOUNT_PAYABLE", 2);

define("OTHER_CURRENT_LIABILITIES", 2);
define("LONG_TERM_LIABILITIES", 4);
define("EQUITY", 6);
define("INCOME", 8);
define("PURCHASES_RETURNS_AND_ALLOWANCES", 10);


function j_entry ($t_type, $entity_id, $a_id, $date, $descr, $amt, $con) {
  $p_bal = 0;
  $new_bal = 0;
  $p_bal = get_acc_bal($a_id, $entity_id, '0000-00-00', date('Y-m-d'), $con);
  
  #Get the account type
  $acc_type_id = get_acc_type($a_id, $entity_id, $con);
  
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
  $sql = "insert into journal (acc_id, entity_id, d_entry, descr, 
     t_type, amt, bal) values  ('$a_id', '$entity_id', '$date',
     '$descr', '$t_type', '$amt', '$new_bal')";

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

/*
function get_acc_bal($acc_id, $entity_id, $sdate, $edate, $con) {
  $journal_bal = array();
  $sql = "select j.bal from journal j join account a on j.acc_id = a.id 
    where j.acc_id=$acc_id and j.entity_id = $entity_id 
    and (d_entry between '$sdate' and '$edate')
    order by j.id desc";
  $result = mysql_query($sql, $con) or die(mysql_error());
  if (mysql_num_rows($result) > 0) {
    $journal_bal = mysql_fetch_row($result);
    return $journal_bal[0];
  } else { 
    return 0;
  }
}
*/

function get_acc_type($acc_id, $entity_id, $con) {
  $result = mysql_query("select acc_type_id from account where id=$acc_id
      and entity_id= $entity_id", $con) or die(mysql_error());
  $acc_type = mysql_fetch_array($result);
  return $acc_type['acc_type_id'];
}

function get_acc_bal($acc_id, $entity_id, $sdate, $edate, $con) {
  $acc_type_id = get_acc_type($acc_id, $entity_id, $con);

  #Accounts with debit balances have odd acc_type_id
  #Accounts with credit balances have even acc_type_id

  $sql = "select j.amt, j.t_type from journal j join account a on 
    j.acc_id = a.id 
    where j.acc_id=$acc_id and j.entity_id = $entity_id 
    and (d_entry between '$sdate' and '$edate')
    order by j.id";
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


function get_acc_id($n, $con) {
  $sql= "select id from account where name='$n'";
  $result = mysql_query($sql) or die(mysql_error(). 'Error here');
  $row = mysql_fetch_array($result);
  return $row['id'];
}
function account_exists($n, $con) {
  $sql="select id from account where name='$n'"; 
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

function add_account($n, $t, $entity_id, $date, $con) {
  $result = mysql_query("SELECT * FROM account WHERE name ='$n'", $con)
    or die("Cannot execute SQL query acc.inc@add_accounts:1 " . mysql_query());
  if (mysql_num_rows($result) > 0) {
    return "This account name is taken. Please choose another";
  } else {
     mysql_query("INSERT INTO account (acc_type_id, entity_id, name, 
      d_created) VALUES ('$t', '$entity_id', '$n', '$date')",
     $con) or die(mysql_query());
     return mysql_insert_id();

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
  $result = mysql_query($sql, $con) or die(mysql_error());
  
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

  $opening_stock = get_acc_type_bal(OPENING_STOCK, $sdate, $edate, $con);
  $purchases = get_acc_type_bal(PURCHASES, $sdate, $edate, $con);

  $sum_opn_stck_purc = $opening_stock + $purchases;

  $cogs = $sum_opn_stck_purc - $clo_stock;
  $gross_profit = $sales - $cogs;

  $expenses = get_acc_type_bal(EXPENSES, $sdate, $edate, $con);
  $net_profit = $gross_profit - $expenses;

  return $net_profit;
}

function profit_and_loss_modified($sdate, $edate, $con) {
  #Process Sales
   $sql = "SELECT * FROM account where
     acc_type_id=" . INCOME . " and (id != 5  and id != 6) ";
   $sales = get_accounts($sql, $sdate, $edate, $con);

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

  $net_sales = $sales - $charges;
  
  #Opening Stock
  $sql  = "SELECT * FROM account where acc_type_id =" . OPENING_STOCK;
  $opening_stock= get_accounts($sql,$sdate, $edate, $con);

  #Add Purchases
  $sql  = "SELECT * FROM account where acc_type_id =" . PURCHASES;
  $purchases = get_accounts($sql, $sdate, $edate, $con);

  $sum_opn_stck_purc = $opening_stock + $purchases;

  $sql  = "SELECT * FROM account where acc_type_id =" . CLOSING_STOCK;
  $clo_stock = get_accounts($sql,$sdate,$edate,$con);

  $sum_opn_stck_purc = $opening_stock + $purchases;
  $cogs = $sum_opn_stck_purc - $clo_stock;

  $gross_profit = $net_sales - $cogs;

  #Process Expenses
  $sql = "SELECT * FROM account where acc_type_id =" . EXPENSES;
  $expenses = get_accounts($sql, $sdate, $edate, $con);

  $net_profit = $gross_profit - $expenses;
  return $net_profit;
}
?>
