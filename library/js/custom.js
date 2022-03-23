$(document).ready(function() {

  //attach autocomplete
  $("#student_name").autocomplete({
    //source: availableTags
	source: function(req, add){
	  $url = "get_students_as_json.php?class_id=" + document.form1.class_id.value + "&school_id=" + document.form1.school_id.value + "&callback=?";
	  //pass request to server
	  $.getJSON($url, req, function(data) {
	    //create array for response objects
	    var suggestions = [];

		//alert(data);							
	    //process response
	    $.each(data, function(i, val){								
	      suggestions.push(val.name);
	    });

	    //pass array to callback
	    add(suggestions);
	  });
	}
  });  
  
  
  //attach autocomplete
  $("#subject_name").autocomplete({
    //source: availableTags
	source: function(req, add){
	$url = "get_subjects_as_json.php?class_type_id=" + document.form1.class_type_id.value + "&school_id=" + document.form1.school_id.value + "&callback=?";
	  //pass request to server
	  $.getJSON($url, req, function(data) {
	    //create array for response objects
	    var suggestions = [];

		//alert(data);							
	    //process response
	    $.each(data, function(i, val){								
	      suggestions.push(val.name);
	    });

	    //pass array to callback
	    add(suggestions);
	  });
	}
  });  

  $("#expense_name").autocomplete({
    source: function(req, add){
	  //pass request to server
	  $.getJSON("get_schools_as_json.php?callback=?", req, function(data) {
	    //create array for response objects
	    var suggestions = [];
							
	    //process response
	    $.each(data, function(i, val){								
	      suggestions.push(val.name);
	    });
							
	    //pass array to callback
	    add(suggestions);
	  });
	}
  });
});
