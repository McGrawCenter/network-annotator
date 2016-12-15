
function preview(posturl, imgurl) {

	$html = '<a href="'+posturl+'"><img src="'+imgurl+'" width="100%" /></a>';
	jQuery('#info-window-preview').html($html);
}





function t(para_id) {
  console.log(jQuery("#anno-1").position());

  var p = '#info-window-'+para_id;
  jQuery(p).toggle();
  jQuery('.target').val('');
}


jQuery( document ).ready(function() {


    jQuery('.preview').mouseover( function(e){
      e.stopPropagation();
      var target = jQuery(this).attr('rel');
      jQuery('#'+target).show();
    });
    jQuery('.preview').mouseout( function(e){
      e.stopPropagation();
      var target = jQuery(this).attr('rel');
      jQuery('#'+target).hide();
    });  


    jQuery('.postpreview').mouseover( function(e){
      e.stopPropagation();
      jQuery(this).show();
    });
  
    jQuery('.postpreview').mouseout( function(e){
      e.stopPropagation();
      jQuery(this).hide();
    });

});





function pop(page_id, para_id) {
    //var handle = '#'+para_id;
    var button_id = '#'+para_id+'add';
    jQuery('#para_id').val(para_id);

    jQuery('#info-window').toggle();

    var pos=jQuery(button_id).offset();

    //var h=jQuery('#para'+para_id).height();
    //var w=jQuery('#para'+para_id).width();

    jQuery('#info-window').css({ left: pos.left-340, top: pos.top-100 });

}


function pShow(theid) {
    console.log(theid);
    jQuery('#'+theid).show();
}
function pHide(theid) {
    console.log(theid);
    jQuery('#'+theid).hide();
}

