<?php
/*
Plugin Name: Network Annotator
Plugin URI: http://www.princeton.edu
Description: The Network Annotator plugin includes the [anno] (and [annotation]) shortcode.  When added to a page, this inserts a link that allows frontend users to create links between posts.
Version: 1.0
Author: Ben Johnston
Author URI: http://www.princeton.edu
License: GPL2
*/


require_once(plugin_dir_path( __FILE__ ) . "network-annotator-exports.php");


/* ***************************************************** 
These are for inserting the comment data into meta fields
****************************************************** */
add_action('init','eas_annotator_init');
add_filter( 'the_content', 'eas_annotator_filter' ); // add the autocomplete javascript

function eas_annotator_init() {
    wp_enqueue_script( 'eas_annotator_js', plugins_url( '/js/eas_annotator.js', __FILE__ ), array('jquery'));
   //wp_enqueue_script('jquery-ui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/jquery-ui.min.js', array('jquery'), '1.11.1');
    wp_enqueue_script('jquery-ui', plugins_url( '/js/jquery-ui.min.js', __FILE__ ), array('jquery'), '1.11.1');
    wp_register_style( 'eas_annotator_css', plugins_url( '/css/eas_annotator.css', __FILE__ ));
    wp_enqueue_style('eas_annotator_css');
    wp_register_style( 'eas_annotator_ui', plugins_url( '/css/jquery-ui.css', __FILE__ ));
    wp_enqueue_style('eas_annotator_ui');
}


// iterates each time a shortcode is used so that we can set ids

function anno_counter() {  
  static $formfield_count=0; $formfield_count++; return $formfield_count;
}


function eas_annotator_shortcode() {
  $cnt = anno_counter();
  return annotate('anno-'.$cnt);
}
add_shortcode("anno", "eas_annotator_shortcode");
add_shortcode("annotate", "eas_annotator_shortcode");




/* ***************************************************** 

****************************************************** */

function eas_annotator_filter($content) {


  // first we need to create some javascript in the page that will populate the autocomplete form fields
  $args = array( 'post_status' => 'publish', 'numberposts' => -1);
  $myposts = get_posts( $args );
  $postArr = array();

  foreach($myposts as $p) {
    $cat_id_arr = wp_get_post_categories( $p->ID );
    $catObj = get_category( $cat_id_arr[0] );
    $cat = $catObj->name;
    $postArr[] = " {'id':'".$p->ID."' , 'label':'".$cat.": ".addslashes($p->post_title)."'} ";
  }

  $postlist = implode(',',$postArr);

echo " <script>
    jQuery('document').ready(function() {
    var availableTags = [".$postlist."];

    jQuery('.tag').autocomplete({
    source: availableTags,
    select: function(event, availableTags) {
      jQuery('.target').val(availableTags.item.id);
  }
});
});
    </script>";


  // check to see if form input has been submitted

  if ( isset($_POST['eas_linkto']) ) {
    if( $_POST['eas_linkto'] != "" ){
	global $post;
	$target_id = $_POST['target'];
	$anno_id = $_POST['anno_id'];
	$page_id = $post->ID;
	$tag = 'page'.$page_id.":".$anno_id;

        eas_annotator_save_postdata($target_id,$_POST['eas_linkto'], $tag);
     }
  }


return $content;
}



/********************** POST DATA *************************/




/* ***************************************************** 
  this is the callback for the preg_replace_callback in the filter function above.
  it is triggered for every paragraph on the page that has an annoid attribute
****************************************************** */

function annotate($anno_id) {
  global $post;
  $page_id = get_the_ID();

  $args = array( 'post_status' => 'publish', 'numberposts' => -1);
  $allposts = get_posts( $args );

  $returnStr .= "<a name='".$anno_id."'></a>\n";

  // check every post and within each post check every tag for a match
   $returnStr .= "<div class='eas_annotation_links'>\n";
   $cnt = 0;
   $icons = false;

  foreach($allposts as $p)
    {
    $annotations = get_post_meta( $p->ID, 'annotations', $single );
    if(count($annotations) > 0) { 
    $annoArr = explode(',',$annotations[0]);

     foreach($annoArr as $t) {

	   if(trim($t) == 'page'.$page_id.':'.$anno_id) { 

		// the following gets the first category for the post that is targeted and uses that to decide which icon to use.
		$cat_id_arr = wp_get_post_categories( $p->ID );
		$catObj = get_category( $cat_id_arr[0] );
		$cat = $catObj->name;
		$returnStr .= "<a href='?p=".$p->ID."' title='".$p->post_title."' class='link_uncategorized preview' rel='preview-".$anno_id."-".$cnt."'>".$cat."</a>";
		$returnStr .= "<br />";


	       $preview = "<h4>".$p->post_title."</h4>".get_the_post_thumbnail( $p->ID, 'thumbnail',array('class' => 'alignleft') ).strip_tags(substr($p->post_content,0,240))."...";
	       $returnStr .= "<div class='postpreview' id='preview-".$anno_id."-".$cnt."'>".$preview."<br clear='all'/></div>";
	       $cnt++; 
	   } // end if

     } // foreach anno
    } // end if
  } // foreach post
  $returnStr .= "<div class='annotation_marker'><a href=\"#\" id=\"{$anno_id}\" onClick=\"t('".$anno_id."'); return false;\">+</a></div>";

  $returnStr .= "<div class='info-window' id='info-window-".$anno_id."'>";
  $returnStr .= "  <div class='info-window-head'><a href=\"javascript:t('".$anno_id."')\"><img src='".plugins_url( 'images/close-window-icon.png' , __FILE__ )."' /></a></div>\n";
  $returnStr .= "  <div class='info-window-body'>\n";
  $returnStr .= "  <h3>Link to this resource</h3>\n";
  $returnStr .= "    <form name='link' method='POST' action=''>\n";
  $returnStr .= "      <div class='ui-widget'>Enter title of post to link to:<br /><input id='tag' class='tag' name='eas_linkto'>\n";
  $returnStr .= "      <input type='hidden' name='anno_id' value='".$anno_id."'>\n";
  $returnStr .= "      <input type='hidden' name='target' class='target' value=''>\n";
  $returnStr .= "      <input type='submit' value='Link'>\n";
  $returnStr .= "      </div>\n";
  $returnStr .= "    </form>\n";
  $returnStr .= "  </div>\n";
  $returnStr .= "    <div style='text-align:right;'><a href='#".$anno_id."'><img src='".plugins_url( 'images/permalink-icon.png' , __FILE__ )."'/><!--<input type='button' value='link here'/>--></a></div>\n";
  $returnStr .= "</div>\n";
  $returnStr .= "</div>\n";

  return $returnStr;

}


/* ***************************************************** 

****************************************************** */


function  eas_annotator_save_postdata( $target_id, $post_title, $tag ) 
{
    $meta = get_post_meta( $target_id, 'annotations' );
    if(count($meta)>0 && $meta[0] != '') { 
	$str = $meta[0].",".$tag;
	update_post_meta( $target_id, 'annotations', $str );
	}
    else { 
	add_post_meta( $target_id, 'annotations', $tag );
	}
    return;
}






function  eas_annotator_export_csv( ) 
{
    $args = array( 'posts_per_page' => -1,'post_type'=> 'post','post_status'      => 'publish');
    $posts = get_posts($args);
    $returnStr = "Source,Target\n";
    foreach($posts as $post) {
	$annotations = get_post_meta( $post->ID, 'annotations', true );
	// Check if the custom field has a value.
	if ( ! empty( $annotations ) ) {
	    $annoArr = explode(',',$annotations);
	    foreach($annoArr as $anno) {
	      $parts = explode(':',$anno);
	      $id = str_replace('page','',$parts[0]);
	      $target_post = get_post($id);
	      $returnStr .= "'".$post->post_title."','".$target_post->post_title."'\n";
	    }
	}
    }
  header('Content-type: text/plain');
  header('Content-disposition: attachment; filename="export.csv"');
  echo $returnStr;
  die();
}


if(isset($_GET['exportcsv'])) { eas_annotator_export_csv(); }




?>
