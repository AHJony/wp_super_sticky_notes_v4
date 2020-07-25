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
      newObject['number'] = top_page_num_shoes[i];
      top_page_datapoints.push(newObject);
  }
  

  var top_users_name = notesAjax.top_users_name;
  var top_users_comment = notesAjax.top_users_comment;
  var top_users_datapoints = [];
  for(var i in top_users_name){
      var newObject = {};
      newObject['label'] = top_users_name[i];
      newObject['y'] = top_users_comment[i];
      top_users_datapoints.push(newObject);
  }


  var user_name = notesAjax.user_name;
  var user_comment = notesAjax.user_comment;
  var user_like_unlike = notesAjax.user_like_unlike;

  var user_name1 = (typeof user_name[0] == 'undefined') ? '' : 'Most liked User Name: ' + user_name[0] + '. User Comment: ' + user_comment[0];
  var user_name2 = (typeof user_name[1] == 'undefined') ? '' : 'Most disliked User Name: ' + user_name[1] + '. User Comment: ' + user_comment[1];

  var user_like_unlike1 = (typeof user_like_unlike[0] == 'undefined') ? '' : user_like_unlike[0];
  var user_like_unlike2 = (typeof user_like_unlike[1] == 'undefined') ? '' : user_like_unlike[1];

  am4core.ready(function() {

    // Themes begin
    am4core.useTheme(am4themes_animated);
    // Themes end
    
    // Create chart instance
    var chart = am4core.create("chartContainer", am4charts.PieChart);
    
    // Add data
    chart.data = top_page_datapoints;
    
    // Add and configure Series
    var pieSeries = chart.series.push(new am4charts.PieSeries());
    pieSeries.dataFields.value = "number";
    pieSeries.dataFields.category = "label";
    pieSeries.slices.template.stroke = am4core.color("#fff");
    pieSeries.slices.template.strokeOpacity = 1;
    
    // This creates initial animation
    pieSeries.hiddenState.properties.opacity = 1;
    pieSeries.hiddenState.properties.endAngle = -90;
    pieSeries.hiddenState.properties.startAngle = -90;
    
    chart.hiddenState.properties.radius = am4core.percent(0);
    
    


    // Create chart instance
    var chart = am4core.create("topTenUser", am4charts.PieChart);
    
    // Add data
    chart.data = top_users_datapoints;
    
    // Add and configure Series
    var pieSeries = chart.series.push(new am4charts.PieSeries());
    pieSeries.dataFields.value = "y";
    pieSeries.dataFields.category = "label";
    pieSeries.slices.template.stroke = am4core.color("#fff");
    pieSeries.slices.template.strokeOpacity = 1;
    
    // This creates initial animation
    pieSeries.hiddenState.properties.opacity = 1;
    pieSeries.hiddenState.properties.endAngle = -90;
    pieSeries.hiddenState.properties.startAngle = -90;
    
    chart.hiddenState.properties.radius = am4core.percent(0);




    // Create chart for toptenLikeUnlike
    var chart = am4core.create("toptenLikeUnlike", am4charts.PieChart);
    
    
    // Add data d
    chart.data = [
      { label: user_name1, y: Number(user_like_unlike1) },
      { label: user_name2, y: Number(user_like_unlike2) }
    ];
    
    // Add and configure Series
    var pieSeries = chart.series.push(new am4charts.PieSeries());
    pieSeries.dataFields.value = "y";
    pieSeries.dataFields.category = "label";
    pieSeries.slices.template.stroke = am4core.color("#fff");
    pieSeries.slices.template.strokeOpacity = 1;
    
    // This creates initial animation
    pieSeries.hiddenState.properties.opacity = 1;
    pieSeries.hiddenState.properties.endAngle = -90;
    pieSeries.hiddenState.properties.startAngle = -90;
    
    chart.hiddenState.properties.radius = am4core.percent(0);
    
    
    }); // end am4core.ready()



  










}