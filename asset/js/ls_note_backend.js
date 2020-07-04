function openTab(evt, cityName) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tabcontent");
    jQuery('.tabcontent').hide();
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    document.getElementById(cityName).style.display = "block";
    evt.currentTarget.className += " active";
}
jQuery(document).ready(function($){

    jQuery('.reply').click(function(){
        jQuery(this).next('.modal-overlay').addClass('active');
        jQuery(this).next('.modal-overlay').find('.modal').addClass('active');
    });

    jQuery('.close-modal').click(function(){
        jQuery(this).closest('.modal-overlay').removeClass('active');
        jQuery(this).closest('.modal').removeClass('active');
    });

    // Settings form submit
    jQuery(document.body).on('change', 'select#allcommentpage', function(e){
        jQuery(this).closest('form').submit();
    });
    jQuery(document.body).on('change', 'select#buttonposition', function(e){
        jQuery(this).closest('form').submit();
    });


    if(jQuery('.jquerydatatable').length){
        jQuery('.jquerydatatable').DataTable();
    }

    
    //image uploder
    var mediaUploader;
  
    $('#upload-button').click(function(e) {
      e.preventDefault();
      // If the uploader object has already been created, reopen the dialog
        if (mediaUploader) {
        mediaUploader.open();
        return;
      }
      // Extend the wp.media object
      mediaUploader = wp.media.frames.file_frame = wp.media({
        title: 'Choose Image',
        button: {
        text: 'Choose Image'
      }, multiple: false });
  
      // When a file is selected, grab the URL and set it as the text field's value
      mediaUploader.on('select', function() {
        attachment = mediaUploader.state().get('selection').first().toJSON();
        $('#image-user-url').val(attachment.url);
      });
      // Open the uploader dialog
      mediaUploader.open();
    });

    $('#upload-admin-button').click(function(e) {
      e.preventDefault();
      // If the uploader object has already been created, reopen the dialog
        if (mediaUploader) {
        mediaUploader.open();
        return;
      }
      // Extend the wp.media object
      mediaUploader = wp.media.frames.file_frame = wp.media({
        title: 'Choose Image',
        button: {
        text: 'Choose Image'
      }, multiple: false });
  
      // When a file is selected, grab the URL and set it as the text field's value
      mediaUploader.on('select', function() {
        attachment = mediaUploader.state().get('selection').first().toJSON();
        $('#image-admin-url').val(attachment.url);
      });
      // Open the uploader dialog
      mediaUploader.open();
    });

    // Settings Update newsfeed form submit
    jQuery(document.body).on('change', 'select#rbc_newsfeed', function(e){
      jQuery(this).closest('form').submit();
    });

}); // End Document ready

window.onload = function () {

  var top_page_title = notesAjax.top_page_title;
  var top_page_num_shoes = notesAjax.top_page_num_shoes;

  var top_page_datapoints = [];
  for(var i in top_page_title){
      var newObject = {};
      newObject['label'] = top_page_title[i];
      newObject['y'] = top_page_num_shoes[i];
      top_page_datapoints.push(newObject);
  }
 
  var top_page_options = {
    title: {
      text: "Top ten posts/pages that comments with numbers"              
    },
    data: [              
    {
      // Change type to "column", "doughnut", "line", "splineArea", etc.
      // type: "doughnut",
      type: "pie",
			startAngle: 45,
			showInLegend: "true",
			legendText: "{label}",
			indexLabel: "{label} ({y})",
			yValueFormatString:"#,##0.#"%"",
      dataPoints: top_page_datapoints
    }
    ]
  };

  jQuery(".top-ten-page").CanvasJSChart(top_page_options);

  var top_users_name = notesAjax.top_users_name;
  var top_users_comment = notesAjax.top_users_comment;
  var top_users_datapoints = [];
  for(var i in top_users_name){
      var newObject = {};
      newObject['label'] = top_users_name[i];
      newObject['y'] = top_users_comment[i];
      top_users_datapoints.push(newObject);
  }

  var top_user_options = {
    title: {
      text: "Top ten users with highest comments"              
    },
    data: [              
    {
      
      // Change type to "doughnut", "line", "splineArea", etc.
      // type: "doughnut",
      type: "pie",
			startAngle: 45,
			showInLegend: "true",
			legendText: "{label}",
			indexLabel: "{label} ({y})",
			yValueFormatString:"#,##0.#"%"",
      dataPoints: top_users_datapoints
    }
    ]
  };
  jQuery(".top-ten-user").CanvasJSChart(top_user_options);

  var user_name = notesAjax.user_name;
  var user_comment = notesAjax.user_comment;
  var user_like_unlike = notesAjax.user_like_unlike;

  var user_name1 = (typeof user_name[0] == 'undefined') ? '' : 'Most liked User Name: ' + user_name[0] + '. User Comment: ' + user_comment[0];
  var user_name2 = (typeof user_name[1] == 'undefined') ? '' : 'Most disliked User Name: ' + user_name[1] + '. User Comment: ' + user_comment[1];

  var user_like_unlike1 = (typeof user_like_unlike[0] == 'undefined') ? '' : user_like_unlike[0];
  var user_like_unlike2 = (typeof user_like_unlike[1] == 'undefined') ? '' : user_like_unlike[1];

  var top_like_unlike_options = {
    title: {
      text: "Most liked and disliked comments"              
    },
    data: [              
    {
      
      // Change type to "doughnut", "line", "splineArea", etc.
      // type: "splineArea",
      type: "pie",
			startAngle: 45,
			showInLegend: "true",
			legendText: "{label}",
			indexLabel: "{label} ({y})",
			yValueFormatString:"#,##0.#"%"",
      dataPoints: [
        { label: user_name1, y: Number(user_like_unlike1) },
        { label: user_name2, y: Number(user_like_unlike2) }
      ]
    }
    ]
  };
  jQuery(".top-ten-like-unlike").CanvasJSChart(top_like_unlike_options);
}