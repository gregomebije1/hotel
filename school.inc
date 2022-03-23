<?php
include_once('util.inc');

function get_admission_number($text){
  $data = explode("_", $text);
  return $data[0];
}
  
function check_session_variables($referer, $con) {
  $extra_input = "<input type='hidden' name='action' value='Add'>
    <input type='hidden' name='REFERER' value='$referer'>";
  //my_redirect("term.php?action=Add&REFERER=$referer",'');
  
  if (mysql_num_rows(mysql_query("select * from session where school_id={$_SESSION['school_id']}", $con)) < 1) {
    my_redirect("session.php", $extra_input);
    exit;
  }

  if (mysql_num_rows(mysql_query("select * from term where school_id={$_SESSION['school_id']}", $con)) < 1) {
    my_redirect("term.php", $extra_input);
    exit;
  }
  
  if (mysql_num_rows(mysql_query("select * from class where school_id={$_SESSION['school_id']}", $con)) < 1) {
	my_redirect("class.php", $extra_input);
    exit;
  }
  
  if ((!isset($_SESSION['session_id'])) || (!isset($_SESSION['term_id'])) 
    || (!isset($_SESSION['class_id']))){
	my_redirect("choose_session_term_class.php", $extra_input);
    exit;
  }
}

	
function get_subject_id($subject_name, $class_id, $school_id, $con) { 
  $sql="select s.id from subject s join class c on s.class_type_id = c.class_type_id
   where s.name='$subject_name' and s.school_id=$school_id and c.id={$class_id}";
  
  $result = mysql_query($sql) or die(mysql_error());
  $row = mysql_fetch_array($result);
  return $row['id'];
}
function decode_student_id($text, $con) {
  $data = explode("_", $text);
  $sql="select * from student where admission_number='{$data[0]}'
    and firstname='{$data[1]}' and lastname='{$data[2]}'";
  $result = mysql_query($sql) or die(mysql_error());
  if (mysql_num_rows($result) > 0) {
    $row = mysql_fetch_array($result);
	return $row['id'];
  } else 
    return 0;
}

 
 function grade($grade) {
  $sql="select * from grade_settings where 
   low <= $grade and high >= $grade";
  $result = mysql_query($sql) or die(mysql_error());
  if (mysql_num_rows($result) == 0) {
    return 'Unknown Grade';
  } else {
    $row = mysql_fetch_array($result);
	return $row['name'];
  }
}
function grade_non_academic($score) {
  for($i=1; $i <= 5; $i++) {
    if ($i == $score) 
	  echo "<td><span class='style4'>X</span></td>";
	else 
	  echo "<td><span class='style4'></span></td>";
  }
}
function get_row_test_exam() {
  $result_test_exam = mysql_query("select * from test_exam_settings") or 
   die(mysql_error());
  return mysql_fetch_array($result_test_exam);
}
function get_school($id) {
  $school = array();
  $sql="select * from school where id={$id}";
  $result = mysql_query($sql) or die(mysql_error());
  if (mysql_num_rows($result) > 0) {
    $row = mysql_fetch_array($result);
    $school['name'] = $row['name'];
    $school['address'] = $row['address'];
    $school['phone'] = $row['phone'];
    $school['email'] = $row['email'];
    $school['web'] = $row['web'];
    $school['logo'] = $row['logo'];
  }
  return $school;
}

function get_term_info($id) {
  $term = array();
  $sql="select * from term where id=$id";
  
  $result = mysql_query($sql) or die(mysql_error());
  if (mysql_num_rows($result) <= 0) {
    $term['name'] = "";
	$term['begin_date'] = "";
	$term['end_date'] = "";
	$term['session_id'] = "";
	$term['times_school_open'] = "";
  } else {
    $row = mysql_fetch_array($result);
	$term['name'] = $row['name'];
	$term['begin_date'] = $row['begin_date'];
	$term['end_date'] = $row['end_date'];
	$term['session_id'] = $row['session_id'];
	$term['times_school_open'] = $row['times_school_open'];
  }
  return $term;
}

function get_terms($session_id, $term_id) {
  $sql_term="select * from term where session_id=$session_id";
  if ($term_id != '0') {   
    $sql_term .= " and id=$term_id";
  }
  $sql_term .= " order by id asc";
  
  $result_term = mysql_query($sql_term) or die(mysql_error());
  $term_array = array();
  while ($row_term = mysql_fetch_array($result_term)) {
    $term_array[$row_term['id']] = $row_term['name'];
  }
  
  return $term_array;
}
function get_total_subjects_per_student($class_id, $student_id) {
  $sql="select count(*) as 'count' from student s join student_subject ss 
   on (s.id = ss.student_id) where ss.class_id=$class_id
   and s.id=$student_id";
	 
   $result_count = mysql_query($sql) or die(mysql_error());
   $subject_count = mysql_fetch_array($result_count);
   return $subject_count['count'];
}
   
function get_marks_obtained($class) {
  $marks_obtained = array();
  if ($class != '0') 
    $sql="select * from student where class_id=$class";
  else 
    $sql="select * from student";
  $result = mysql_query($sql) or die(mysql_error());
  while($row = mysql_fetch_array($result)) {
    $marks_obtained["{$row['admission_number']}"] = 0;
  }
  return $marks_obtained;
}
function get_fee_by_name($name) {
  $sql="select amount from fee_class fc join fee f
   on fc.fee_id = f.id where f.name='$name'";
  $result = mysql_query($sql) or die(mysql_error());
  $row = mysql_fetch_array($result);
  return $row['amount'];
}
function get_all_fees($class_id) {
  $sql="select sum(amount) as 'sum' from fee_class fc join 
   fee f on fc.fee_id = f.id where fc.class_id=$class_id";
  $result = mysql_query($sql) or die(mysql_error());
  $row = mysql_fetch_array($result);
  return $row['sum'];
}

function get_user_type($perm, $con) {
 $result = mysql_query("select * from permissions where id=$perm", $con);
 $row = mysql_fetch_array($result);
 return $row['name'];
}

function user_type($uid, $users, $con) {
  
  foreach($users as $perm) {
    $sql="select * from permissions p left join 
    (user u, user_permissions up) on (u.id = up.uid and up.pid = p.id) 
    where u.id =$uid and p.name='$perm'";
	
    $result = mysql_query($sql, $con) or die ("Cannot execute SQL query" . mysql_error());
	if (mysql_num_rows($result) > 0)
	  return true;
  }
  return false;
}

?>
