<?php 
include_once 'util.inc';

function tr($arr) {
  $output = "<tr>";
  foreach($arr as $td) {
    $output .= "<td>$td</td>";
  }
  $output .= "</tr>";
  return $output;
}
function textfield() {
  $numargs = func_num_args();
  if ($numargs < 2) die("textfield second argument is missing");
 
  $output = "<input type='text' ";
  $arg_list = func_get_args();
  for ($i = 0; $i < $numargs; $i+=2) {
    $output .= " {$arg_list[$i]}='{$arg_list[$i+1]}' ";
  }
  $output .= ">";
  return $output;
}

function main_menu($uid, $con) {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
      <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
       <head>
	    <title>Mangital Hotel Software</title>
        <script language="javascript" src="hotel.js"> </script>
        <link rel="stylesheet" type="text/css" href="index.css">
       </head>
       <body>
       <div class="header">
        <p>Mangital Hotel Software</p>
        <p>Welcome <?php echo get_value('user', 'name', 'id', $uid, $con);?></p> 
        <p><a style='color:red;' href='faq.html' >FAQ</a></p>
        <a style='color:red;float:right;' href="index.php?action=logout"> Sign Out</a>
       </div>
       <div class="bar"></div>
       <div class="navigation">
        <ul>
<?php 
  if (user_type($uid, 'Reception', $con) 
	 || user_type($uid, 'Manager', $con)
	 || user_type($uid, 'Front Office Cashier', $con)) {
    $menu = array('guest.php?action=List' => 'Check In', 
     'room_status.php?action=Form' => 'Room Status', 
     'account_status.php?action=Form'=> 'Guest Account Status', 
     'auto_billing.php?action=Form' => 'Auto Billing', 
     'check_out.php?action=Form' => 'Check Out', 
     'group_company_guests.php?action=List'=> 'Group/Company', 
     'reservation.php?action=List' => 'Reservation',
     );
     generate_menu("Front Office", $menu);
  } 

  if (user_type($uid, 'Front Office Cashier', $con) 
    || user_type($uid, 'Restaurant Cashier', $con) 
    || user_type($uid, 'Manager', $con) 
    || user_type($uid, 'Accountant', $con)) {
    $menu = array(
     'receive_payment.php?action=Form'   => 'Cash Transactions', 
     'record_expenses.php?action=Form'     => 'Credit Transactions', 		     'summary_report.php'	=> 'Summary'); 

    if (user_type($uid, 'Manager', $con) || 
      (user_type($uid, 'Accountant', $con))) {
       $menu['charge.php'] = 'Charges';
       $menu['audit_log.php']='Void Transactions';		
       $menu['chart_of_accounts.php'] = 'Charts Of Accounts';
       $menu['journal_entry.php'] = 'Journal Entry';
       $menu['trial_balance.php'] = 'Trial Balance';
       $menu['general_ledger.php'] = 'General Ledger';
       $menu['profit_and_loss.php']='Profit and Loss';
       $menu['balance_sheet.php']='Balance Sheet';
    }
    generate_menu("Financials", $menu);
  }
  if (user_type($uid, 'Manager', $con)) {
    $menu = array('users.php?action=List'  => 'Users', 
    'room_category.php?action=List' => 'Room Category',
    'room.php?action=List' => 'Rooms', 
    'department.php?action=List' => 'Department',    
    'hotel_info.php' => 'Hotel Information', 
    'backup_restore.php' => 'Backup and Restore'
    ); 
    generate_menu("Maintenance", $menu);
   }
   ?>
   <!--
	  </table>
         </td><td colspan="1" style="background-color:white;" valign="top" width="100%">
   -->
    </ul>
   </div>
   <div class='content'>
  <?
  
}


function main_footer() {
?>
 <!--
   </td></tr>
   <tr><td colspan="5" class="menu">
     &copy; Copyright Mangital Ltd 2010.</td></tr>
  </td></tr>
  </table>
   </body>
  </html>
 -->
   </div> <!-- End of content -->
 </div> <!-- End of container -->
</body>
</html>
<?php 
}


function msg_box($msg, $url, $urlname) {
  return "<table>
             <tr><td><h3>$msg <a style='color:orange; text-decoration:none'
             href='$url'>$urlname</a></h3></td></tr></table>";
}


function welcome_screen($type, $con) {
  $welcome = array('Records'=>'student.php', 'Exams'=>'class_result.php', 
   'Accounts'=>'profit_and_loss.php', 'Expenditure'=>'expense_report.php', 
   'Administrator'=>'users.php');
  foreach($welcome as $user_right => $url) {
    if (user_type($_SESSION['uid'], $user_right, $con)) {
      if ($type == 'location')
        header("Location: $url");
      else 
        include_once($url); 
    }
  }
}

function textarea($name, $value, $rows='5', $cols='30') {
  $numargs = func_num_args();
  if ($numargs < 2) die("textarea second argument is missing");
  return "<textarea name='$name' rows='$rows' cols='$cols'>$value</textarea>";
}

function selectfield($arr, $name, $default_id) {
  $output = "<select name='$name' id='$name'>";
  foreach($arr as $id => $value) {
    if ($id == $default_id)
      $output .= "<option value='$id' selected='selected'>$value</option>";
    else
      $output .= "<option value='$id'>$value</option>";
  }
  $output .="</select>";
  return $output;
}

function calculate_fee($fee_name, $con) {
  $sql="SELECT sum(f.amount) as 'amount' from fee f join fee_type ft 
   on f.fee_type_id = ft.id where ft.name = '$fee_name' group by ft.name";
  $result = mysql_query($sql, $con);
  if (mysql_num_rows($result) > 0) {
    $row = mysql_fetch_array($result);
	return $row['amount'];
  } else {
    return 0;
  }
}
function get_row_value_by_name($table, $name, $con) {
  $sql="select id from $table where name='$name'";
  $result = mysql_query($sql, $con);
  $row = mysql_fetch_array($result);
  return $row['id'];
}
function get_row_value_by_id($table, $id, $con) {
  $sql="select name from $table where id=$id";
  $result = mysql_query($sql, $con);
  $row = mysql_fetch_array($result);
  return $row['name'];
}
function get_table_data($table, $con) {  
  $sql="SELECT * FROM $table";
  $result = mysql_query($sql, $con);
  echo "<select name='{$table}_id' id='{$table}_id'>";
  echo "<!--<option>--Select--</option>-->";
  while ($row = mysql_fetch_array($result)) {
    echo "<option value='{$row['id']}'>{$row['name']} </option>";
  } 
  echo "</select>";
}


function is_table_empty($table, $con) {
  $sql="select * from $table";
  $result = mysql_query($sql, $con);
  if (mysql_num_rows($result) <= 0) 
    return true;
   else 
     return false;
}	 
