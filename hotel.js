function get_profile_reserv (ch) {
  var id, url;
  //Please clear fields to be used

  if (ch == 'profile') {
    /*id = document.myform.profile.options
   [document.myform.profile.selectedIndex].text;
    */
    id = document.myform.profile.options
     [document.myform.profile.selectedIndex].value;
    url = "http://" + window.location.hostname + "/hotel/profile_reserv.php?id=" + id + "&rand=" + Math.random();
     //alert(url);
  } else if (ch == 'reserv') {
    id = document.myform.reserv.options
     [document.myform.reserv.selectedIndex].value;
    url = "http://localhost/cgi-bin/hotel/profile_reserv?cmd=reserv&id=" + id;
	//alert(url);
	
  }
  if (window.XMLHttpRequest) {
    agax = new XMLHttpRequest();
  } else if (window.ActiveXObject) {
    agax = new ActiveXObject('Microsoft.XMLHTTP');
  }
  if (agax) {
    agax.open('GET', url, true);
    agax.onreadystatechange = function () {
      if (agax.readyState == 4 && agax.status == 200) {
        var xmlDoc = agax.responseXML;
        f = xmlDoc.getElementsByTagName("found")[0].childNodes[0].nodeValue;
        if (f == 'True') {
          //Clear all fields first before populating it

          document.getElementById('t').value = "";
          document.getElementById('f').value = "";
          document.getElementById('l').value = "";
          document.getElementById('p').value = "";
          document.getElementById('a').value = "";

          document.getElementById('t').value = 
          xmlDoc.getElementsByTagName('title')[0].childNodes[0].nodeValue;
          document.getElementById('f').value = 
           xmlDoc.getElementsByTagName('firstname')[0].childNodes[0].nodeValue;
          document.getElementById('l').value = 
           xmlDoc.getElementsByTagName('lastname')[0].childNodes[0].nodeValue;
          document.getElementById('p').value = 
           xmlDoc.getElementsByTagName('phone')[0].childNodes[0].nodeValue;
          document.getElementById('sx').options[0].value=
	  xmlDoc.getElementsByTagName('sex')[0].childNodes[0].nodeValue;
          document.getElementById('a').value = 
           xmlDoc.getElementsByTagName('address')[0].childNodes[0].nodeValue;
        }
      }
    };
    agax.send(null);
  } else {
    alert("Error in Connecting to server");
  }
}
function get_month(month) {
  switch (month) {
    case '01': return 'January';
    case '02': return 'February';
    case '03': return 'March';
    case '04': return 'April';
    case '05': return 'May';
    case '06': return 'June';
    case '07': return 'July';
    case '08': return 'August';
    case '09': return 'September';
    case '10': return 'October';
    case '11': return 'November';
    case '12': return 'December';
  }
}

function choose_guest_search() {
  var ch = document.myform.filter.options
   [document.myform.filter.selectedIndex].text;
  alert(ch);
}
function fetch_rate() {
   /*
   var room_number = 
	document.myform.rid.options[document.myform.rid.selectedIndex].text;
	alert(room_number);
   */
   var agax = null;
   var rn = document.getElementById("rid").value;

   var host = window.location.hostname;
   var url = "http://" + host + "/hotel/rate.php?rn=" + rn;
   //var url = "http://" + host + "/rate.php?rn=" + rn;
   //alert(url); 
   
   if(window.XMLHttpRequest) {
	 agax = new XMLHttpRequest();
   } else if (window.ActiveXObject) {
	 agax = new ActiveXObject("Microsoft.XMLHTTP");
   }
   agax.onreadystatechange = function() {
	 if(agax.readyState == 4) {
	   if(agax.status == 200) {
		   var r = agax.responseText;
		   document.getElementById("rr").value = r;
		} else {
		alert("No Rate Defined " + agax.status + " " + agax.statusText);
		}
	 }
   };
   agax.open("GET", url, true);
   /*req.setRequestHeader("Content-Type", 
	"application/x-www-form-urlencoded");
   */
   agax.send(null);
 }
         function make_choice() {
           alert(document.getElementById("choice").value);
         }
         function display_rooms() {
           document.getElementById("r1").style.display='table-row';
           document.getElementById("r2").style.display='table-row';
           document.getElementById("r3").style.display='table-row';

           document.getElementById("d1").style.display='none';
           document.getElementById("d2").style.display='none';
           document.getElementById("d3").style.display='none';

           document.getElementById("o1").style.display='none';
           document.getElementById("o2").style.display='none';
           document.getElementById("o3").style.display='none';
         }
         function display_departments() {
           document.getElementById("r1").style.display='none';
           document.getElementById("r2").style.display='none';
           document.getElementById("r3").style.display='none';

           document.getElementById("d1").style.display='table-row';
           document.getElementById("d2").style.display='table-row';
           document.getElementById("d3").style.display='table-row';

           document.getElementById("o1").style.display='none';
           document.getElementById("o2").style.display='none';
           document.getElementById("o3").style.display='none';
        }
         function display_others() {
           document.getElementById("o1").style.display='table-row';
           document.getElementById("o2").style.display='table-row';
           document.getElementById("o3").style.display='table-row';

           document.getElementById("r1").style.display='none';
           document.getElementById("r2").style.display='none';
           document.getElementById("r3").style.display='none';

           document.getElementById("d1").style.display='none';
           document.getElementById("d2").style.display='none';
           document.getElementById("d3").style.display='none';
        }
        function display_element(id) {
          document.getElementById(id).style.display='inline';
        }
        function hide_element(id) {
          document.getElementById(id).style.display='none';
        }
