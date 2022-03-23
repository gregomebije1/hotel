
function display_element(id) {
  document.getElementById(id).style.display='inline';
}
function hide_element(id) {
  document.getElementById(id).style.display='none';
}

function get_permissions_in_s_permissions() {
  var element = document.form1.s_permissions;
  var value="";
  for(var i = 0; i < element.options.length; i++) {
    //if (element.options[i].selected) 
    if (i == (element.options.length - 1)) {
      value += element.options[i].value;
    } else {  
      value += element.options[i].value + "|";
    }
  }
  document.form1.s_permissions_members.value = value;
}
function hello() {
   //Get the subject selected
   var sIndex = document.form1.class_subject_id.selectedIndex;
   var len = document.form1.class_subject_id.options.length;
   if ((sIndex < 0) || (sIndex >= len)) {
     alert('Please choose a Subject to add');
     return;
   }
   var class_subject = document.form1.class_subject_id.options[sIndex].text;
   var class_subject_num = document.form1.class_subject_id.options[sIndex].value;

   //Create a new Option Object
   var new_class_subject = new Option(class_subject, //The text property
                               class_subject_num, //The value property
                               false,   // The defaultSelected property 
                               false);  // The selected property

   //Display it in s_permissions element by appending it to the options array
   var s_permissions = document.form1.s_permissions;
   s_permissions.options[s_permissions.options.length]=new_class_subject;

   //Remove the subject from class_subject element
   document.form1.class_subject_id.options[sIndex] = null;
  
   get_permissions_in_s_permissions();
}
function hello2() {
   //Get the student in the class 
   var sIndex = document.form1.s_permissions.selectedIndex;
   var len = document.form1.s_permissions.options.length;
   if ((sIndex < 0) || (sIndex >= len)) {
     alert('Please choose a subject to remove');
     return;
   }
   var class_subject = document.form1.s_permissions.options[sIndex].text;
   var class_subject_num = document.form1.s_permissions.options[sIndex].value;

   //Create a new Option Object
   var new_class_subject = new Option(class_subject, //The text property
                               class_subject_num, //The value property
                               false,   // The defaultSelected property 
                               false);  // The selected property

   //Display it in class_subject element by appending it to the options array
   var cs_id = document.form1.class_subject_id;
   cs_id.options[cs_id.options.length]=new_class_subject;

   //Remove the student from student element
   document.form1.s_permissions.options[sIndex] = null;

   get_permissions_in_s_permissions();
}

function get_student_fees() {
   var sIndex = document.form1.class_id.selectedIndex;
   var class_id = document.form1.class_id.options[sIndex].value;

   var term_id=document.form1.term_id.options[document.form1.term_id.selectedIndex].value;
   var session_id=document.form1.session_id.value;
   var student_id=document.form1.student_id.options[document.form1.student_id.selectedIndex].value;

   var host = window.location.hostname;
   var url = "http://" + host + "/acadbase/school/get_student_fees.php?session_id=" + session_id + "&term_id=" + term_id + "&class_id=" + class_id + "&student_id=" + student_id + "&rand=" + Math.random();

   if (window.XMLHttpRequest) {
    agax = new XMLHttpRequest();
   } else if (window.ActiveXObject) {
    agax = new ActiveXObject('Microsoft.XMLHTTP');
   }
   if (agax) {
     //alert(url);
     agax.open('GET', url, true);
     agax.onreadystatechange = function () {
       if (agax.readyState == 4 && agax.status == 200) {
         var xmlDoc = agax.responseText;
         document.getElementById('fees').innerHTML = xmlDoc;
       }};
     agax.send(null);
   } else {
    alert("Error in Connecting to server");
  }
}
function get_students() {
   var sIndex = document.form1.class_id.selectedIndex;
   var class_id = document.form1.class_id.options[sIndex].value;
   var host = window.location.hostname;
   var url = "http://" + host + "/acadbase/school/get_students.php?class_id=" + class_id + "&rand=" + Math.random();
   

   if (window.XMLHttpRequest) {
    agax = new XMLHttpRequest();
   } else if (window.ActiveXObject) {
    agax = new ActiveXObject('Microsoft.XMLHTTP');
   }
   if (agax) {
     agax.open('GET', url, true);
     agax.onreadystatechange = function () {
       if (agax.readyState == 4 && agax.status == 200) {
         var xmlDoc = agax.responseText;
         document.getElementById('students').innerHTML = xmlDoc;
		 //alert(xmlDoc);
       }};
     agax.send(null);
   } else {
    alert("Error in Connecting to server");
  }
}

function get_subject() {
   var sIndex = document.form1.class_id.selectedIndex;
   var class_id = document.form1.class_id.options[sIndex].value;
   var host = window.location.hostname;
   var uid = document.form1.uid.value;
   var url = "http://" + host + "/acadbase/school/get_subject.php?class_id=" + class_id + "&rand=" + Math.random();;
   url += "&uid="+uid;

   if (window.XMLHttpRequest) {
    agax = new XMLHttpRequest();
   } else if (window.ActiveXObject) {
    agax = new ActiveXObject('Microsoft.XMLHTTP');
   }
   if (agax) {
     agax.open('GET', url, true);
     agax.onreadystatechange = function () {
       if (agax.readyState == 4 && agax.status == 200) {
         var xmlDoc = agax.responseText;
         document.getElementById('subjects').innerHTML = xmlDoc;
       }};
     agax.send(null);
   } else {
    alert("Error in Connecting to server");
  }
}
function get_student_in_class() {
  var element = document.form1.sclass;
  var value="";
  for(var i = 0; i < element.options.length; i++) {
    //if (element.options[i].selected) 
    if (i == (element.options.length - 1)) {
      value += element.options[i].value;
    } else {  
      value += element.options[i].value + "|";
    }
  }
  document.form1.class_members.value = value;
}



function remove_student_from_class() {
   //Get the student in the class 
   var sIndex = document.form1.sclass.selectedIndex;
   var len = document.form1.sclass.options.length;
   if ((sIndex < 0) || (sIndex >= len)) {
     alert('Please choose a student to remove from the class');
     return;
   }
   var student = document.form1.sclass.options[sIndex].text;
   var student_num = document.form1.sclass.options[sIndex].value;

   //Create a new Option Object
   var newStudent = new Option(student, //The text property
                               student_num, //The value property
                               false,   // The defaultSelected property 
                               false);  // The selected property

   //Display it in student element by appending it to the options array
   var student_elem = document.form1.student_id;
   student_elem.options[student_elem.options.length]=newStudent;

   //Remove the student from student element
   document.form1.sclass.options[sIndex] = null;
   get_student_in_class();
}
function add_student_to_class() {
   //Get the student selected
   var sIndex = document.form1.student_id.selectedIndex;
   var len = document.form1.student_id.options.length;
   if ((sIndex < 0) || (sIndex >= len)) {
     alert('Please choose a student to add to the class');
     return;
   }
   var student = document.form1.student_id.options[sIndex].text;
   var student_num = document.form1.student_id.options[sIndex].value;

   //Create a new Option Object
   var newStudent = new Option(student, //The text property
                               student_num, //The value property
                               false,   // The defaultSelected property 
                               false);  // The selected property

   //Display it in class element by appending it to the options array
   var sclass = document.form1.sclass;
   sclass.options[sclass.options.length]=newStudent;

   //Remove the student from student element
   document.form1.student_id.options[sIndex] = null;
  
   //Remove all options
   //document.form1.student.options.length=0;

   get_student_in_class();
  
}
function get_terms(source_id, target_id) {
  var sIndex = document.form1.session_id.selectedIndex;
  var session_id = document.form1.session_id.options[sIndex].value;
  var host = window.location.hostname;
  var url = "http://" + host + "/acadbase/school/get_terms.php?session_id=" + session_id + "&rand=" + Math.random();
  get_objects(url, target_id);
}
function get_students_with_size(size) {
   var sIndex = document.form1.class_id.selectedIndex;
   var class_id = document.form1.class_id.options[sIndex].value;
   var host = window.location.hostname;
   var url = "http://" + host + "/acadbase/school/get_students.php?class_id=" + class_id + "&rand=" + Math.random() + "&size=" + size;
   get_objects(url, 'students');
}
function get_students_with_all() {
   var sIndex = document.form1.class_id.selectedIndex;
   var class_id = document.form1.class_id.options[sIndex].value;
   var host = window.location.hostname;
   var url = "http://" + host + "/acadbase/school/get_students.php?class_id=" + class_id + "&rand=" + Math.random() + "&all=all";
   get_objects(url, 'students');
}  
function get_objects(url, target_id) {
   if (window.XMLHttpRequest) {
    agax = new XMLHttpRequest();
   } else if (window.ActiveXObject) {
    agax = new ActiveXObject('Microsoft.XMLHTTP');
   }
   if (agax) {
     agax.open('GET', url, true);
     agax.onreadystatechange = function () {
       if (agax.readyState == 4 && agax.status == 200) {
         var agaxText = agax.responseText;
         document.getElementById(target_id).innerHTML = agaxText;
       }};
     agax.send(null);
   } else {
    alert("Error in Connecting to server");
  }
}
function check_and_show_permissions() {
   var sIndex = document.form1.permissions_id.selectedIndex;
   var value = document.form1.permissions_id.options[sIndex].text;
   if (value == 'Exams') {
     document.getElementById('ok').style.display='inline';
   } else {
     document.getElementById('ok').style.display='none';
   }
}

