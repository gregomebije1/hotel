<?php 
session_start();
unset($_SESSION['uid']);
session_destroy();

include_once('../config.inc');
global $dbserver, $dbusername, $dbpassword, $database; 
$back = "<a href='' onClick='history.back();'>Back</a>";
if ($_REQUEST['action'] == 'Install') {
 if (empty($_REQUEST['dbname']) || empty($_REQUEST['dbusername'])
    || empty($_REQUEST['dbpassword1'])) {
   echo "Please enter correct Database Information details $back_link";
   exit;
  } else {   
    if ($_REQUEST['dbpassword1'] !== $_REQUEST['dbpassword2']) {
	  echo "Passwords are not equal $back_link";
	}
	//Try connecting to database host
    mysql_connect($_REQUEST['dbhost'], $_REQUEST['dbusername'], $_REQUEST['dbpassword1']) 
	  or die("Cannot connect to Database Host $back_link");
	
	//Create remaining tables
	mysql_select_db($_REQUEST['dbname'])
	  or die("Cannot select database $back_link");
	
	####Store the values in the config file####
	if (!file_exists("../config.inc")) {
	  unlink("../config.inc");
	}
    $fp = fopen("../config.inc", "w");
	$stuff="<?php
	  \$dbserver = '{$_REQUEST['dbhost']}';\n
      \$dbusername='{$_REQUEST['dbusername']}';\n
      \$dbpassword='{$_REQUEST['dbpassword1']}';\n
      \$database= '{$_REQUEST['dbname']}';\n
	 ?>";
	fwrite($fp, "$stuff\n");
	fclose($fp);
	
	echo "Database configuration stored in config.inc<br><br>";
    	
	echo "Droping existing tables<br>";
    $tables = array('audit_trail', 'account', 'account_type', 'journal', 
	 'user', 'permissions', 'user_permissions', 
	 'city_ledger', 'hotel_info', 'sync_settings', 'sales', 'company', 
	 'room_category', 'room', 'guest', 'guest_checked_out', 'reservation',
	 'group_company_guests', 'charges', 'entity');

	/*
    foreach($tables as $table) {
      mysql_query("drop table if exists $table", $con) or die(mysql_error());
    } 
	*/
    unset($tables); 

	$tables['city_ledger'] = "
	  create table city_ledger (
	    id int(11) auto_increment primary key, 
		title varchar(100),
		firstname varchar(100),
		lastname varchar(100), 
		address varchar(100), 
		phone varchar(100), 
		sex varchar(100), 
		balance varchar(100)
	  )";
	
	$tables['hotel_info'] = "
	 create table hotel_info (
	  id int(11) auto_increment primary key,
	  name varchar(100),
	  address text, 
	  phone varchar(100),
	  email varchar(100),
	  web varchar(100) 
	)";
	
	$tables['sync_settings'] = "
	 create table sync_settings (
	  id int(11) auto_increment primary key,
	  s_host varchar(100),
	  s_path varchar(100),
	  s_username varchar(100),
	  s_password varchar(100)
	)";
	
	$tables['sales'] = "
	 create table sales (
	 id int(11) auto_increment primary key, 
	 dt date, 
	 shift varchar(100), 
	 staff varchar(100), 
	 p_type varchar(100), 
	  /*
		Cash Sales - Cash sales made by restaurant, bar or laundry
		Credit Sales - Sales made from checking in a guest into a room. 
					 - Sales made from selling things from any other department(Restaurant, Laundry) on credit
					   using record expenses 
		Cash Received - When a customer pays for the debt he is owing
		Cash Deposit - Initial deposit by the guest when his checking into a room
		Cash Refund - Cash refunded to the guest
	 */
	 amount varchar(100),
	 name_of_guest varchar(100), 
	 doc_number varchar(100), /* Doc number e.g., receipt number */
	 r_number varchar(100), /* Room Number and category*/
	 t_details varchar(100), /* Transaction Details */
	 dept varchar(100), /* Department type (Accommodation, Restaurant, Laundry, Bar)*/
	 other1 varchar(100), /*Contain any other special info */
	 other2 varchar(100), /*e.g, Depature date, arrival date */
	 other3 varchar(100), /*e.g., phone number */
	 other4 varchar(100), 
	 other5 varchar(1000)
	)";
	
	$tables['audit_trail'] = "
	create table audit_trail(
	 id int(11) auto_increment primary key, 
	 dt datetime, 
	 staff_id varchar(100), 
	 descr text,   /* Description of the transaction */
	 shift varchar(100),   /* Morning or Night */
	 ot varchar(100),  /* Can contain journal ID, or room ID*/
	 dt2 date 
	)";
	
    $tables['permissions'] = "
	 create table permissions (
	  id int(11) auto_increment primary key, 
	  name varchar(100) 
	 )";
	 
	$tables['user_permissions'] = "
	create table user_permissions (
	  id int(11) auto_increment primary key, 
	  uid int(11),
	  pid int(11)
	)";
	
    $tables['user'] = "	
	CREATE TABLE user (
	  id integer(11) NOT NULL AUTO_INCREMENT primary key,
	  username varchar(16) NOT NULL,
	  password char(40) NOT NULL
	)";
    
	$tables['company'] = "
	create table company (
	  id int(11) auto_increment primary key, 
	  name varchar(100), 
	  address1 text, 
	  address2 text
	)";

	$tables['room_category'] = "
	create table room_category (
	  id int(11) auto_increment primary key,
	  name varchar(100), 
	  rate varchar(100), 
	  deposit varchar(100)
	)";
	
	$tables['room'] = "
	create table room (
	  id int(11) auto_increment primary key, 
	  number varchar(100), 
	  room_category_id integer,
	  occupancy enum('Occupied', 'Vacant'),
	  status enum('Clean', 'Dirty'),
	  comment text,
	  picture1 varchar(100),
	  picture2 varchar(100),
	  account_id varchar(100)
	)";
	
	$tables['department'] = "
	create table department(
	  id int(11) auto_increment primary key, 
	  name varchar(100)
	)";
	
    $tables['guest'] = "
	create table guest (
	  id int(11) auto_increment primary key, 
	  title varchar(100),
	  firstname varchar(100), 
	  lastname varchar(100), 
	  phone varchar(100),
	  room_id int(11),
	  sex varchar(100),
	  address text, 
	  bill_to varchar(100), /* Self, Company, Group */
	  grp_cmp_id varchar(100),
	  
	  arrival_date date, 
	  departure_date date, 
	  notification text, /*Comments that could pop up at service points*/
	  comment text, /*comment to appear on the final bill*/
	  doc_number varchar(100)

	)";

	$tables['guest_checked_out'] = "
	create table guest_checked_out (
	  id int(11) auto_increment primary key,
	  title varchar(100),
	  firstname varchar(100),
	  lastname varchar(100),
	  phone varchar(100),
	  room_id int(11),
	  sex varchar(100),
	  address text
	)";

	$tables['reservation'] = "
	create table reservation (
	  id int(11) auto_increment primary key, 
	  title varchar(100),
	  firstname varchar(100), 
	  lastname varchar(100), 
	  phone varchar(100),
	  sex varchar(100),
	  address text, 
	  arrival_date date, 
	  departure_date date, 
	  room varchar(100)
	)";
	  
	$tables['group_company_guests'] = "
	create table group_company_guests (
	  id int(11) auto_increment primary key, 
	  name varchar(100), 
	  address text, 
	  phone varchar(100),
	  arrival_date date, 
	  acc_id int
	)";
	
	
   $tables['journal'] = 
   "CREATE TABLE `journal` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `acc_id` int(11) NOT NULL,
  `entity_id` int(11)  NOT NULL,
  `d_entry` DATE NOT NULL,
  `descr` text NOT NULL,
  `t_type` varchar(100) NOT NULL,
  `amt` varchar(100) NOT NULL,
  `bal` varchar(100) NOT NULL,
  PRIMARY KEY (`id`))";

  $tables['account'] = 
  "CREATE TABLE account (
  id int(11) NOT NULL AUTO_INCREMENT primary key,
  acc_type_id int(11) NOT NULL,
  entity_id int(11) NULL,
  name varchar(100) NULL,
  code varchar(100),
  description varchar(100),
  d_created DATE NULL,
  parent integer,
  children integer
  )";
  
  $tables['account_type'] = 
   "CREATE TABLE `account_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
  )";
  
  $tables['charges'] = 
   "CREATE TABLE charges (
    id int(11) auto_increment primary key, 
	name varchar(100) NOT NULL, 
	type varchar(100), 
	amount varchar(100),
	acc_id int(11)
   )";
  

  $tables['entity'] = 
  "CREATE TABLE `entity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email`  varchar(100),
  PRIMARY KEY (`id`))";


  foreach($tables as $name => $sql) {
    mysql_query($sql) or die(mysql_error());
    echo "Created $name<br>";
  }
 
 
    unset($tables);

    $tables[] = "insert into permissions(name) values
      ('Manager'), 
      ('Accountant'), 
      ('Front Office Cashier'),
      ('Restaurant Cashier'),
      ('Reception')";
	
    $tables[] = "insert into user_permissions(uid, pid) values ('1', '1')";
    $tables[] = "insert into user(username, password) values ('admin', sha1('password'))";

    $tables[] = "insert into department(name) values
      ('Room'),
      ('Restaurant'),
      ('Laundry')";

    #Accounts with debit balances have odd ID
    #Accounts with credit balances have even ID
    $tables[] = "INSERT INTO `account_type` (id, name) VALUES 
      (1, 'Fixed Assets'),
      (2, 'Account Payable'),
      (3, 'Account Receivable'),
      (4, 'Other Current Liabilities'), 
      (5, 'Other Current Assets'), 
      (6, 'Long Term Liabilities'),
      (7, 'Expenses'),
      (8, 'Equity'),
      (9, 'Sales Retuns and Allowances'),
      (10, 'Income'), 
      (11, 'Opening Stock'), 
      (12, 'Purchases Retuns and Allowances'),
      (13, 'Purchases'),
      (15, 'Closing Stock')";
  
    //Create accounts	
    $tables[] = "insert into account(name, acc_type_id, entity_id, d_created) 
      values 
      ('Cash', 5, 1, CURDATE()),
      ('Room Sales', 10, 1, CURDATE()),
      ('Restaurant Sales', 10, 1, CURDATE()),
      ('Laundry Sales', 10, 1, CURDATE()),
      ('5% Tax', 10, 1, CURDATE()),
      ('10% Service Charge', 10, 1, CURDATE())";

    //Create charges
    $tables[] = "insert into charges(name, type, amount, acc_id) values 
     ('5% Tax', 'percentage_of_room_rate', '5', 5),
     ('10% Service Charge', 'percentage_of_room_rate', '10', 6)";

    //Create hotel_info
    $tables[] = "insert into hotel_info(name, address, phone, email, web) 
    values ('', '', '', '', '')";

    
    foreach($tables as $sql)
      if (!mysql_query($sql)) {
        echo "Problem inserting data into table";
        exit;
      }
      unset($tables);

    echo "<h3>Installtion successfully completed</h3>";
    echo "Continue to <a href='../index.php'>HomePage</a>";
  }
 }
?>
