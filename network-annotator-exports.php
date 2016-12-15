<?php

function  eas_annotator_export_gexf( ) 
{
    $nodes = array();
    $edges = array();

    $args = array( 'posts_per_page' => -1,'post_type'=> 'post','post_status'      => 'publish');
    $posts = get_posts($args);
    $returnStr =  '<?xml version="1.0" encoding="UTF-8"?>
<gexf xmlns="http://www.gexf.net/1.2draft" version="1.2" xmlns:viz="http://www.gexf.net/1.2draft/viz" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.gexf.net/1.2draft http://www.gexf.net/1.2draft/gexf.xsd">
  <meta lastmodifieddate="2013-03-19">
    <creator>Gephi 0.8.1</creator>
    <description></description>
  </meta>
  <graph defaultedgetype="undirected" mode="static">
    <attributes class="node" mode="static"><attribute id="url" title="url" type="string"/></attributes>
    <nodes>';
    foreach($posts as $post) {
	$annotations = get_post_meta( $post->ID, 'annotations', true );
	// Check if the custom field has a value.
	if ( ! empty( $annotations ) ) {
	    $annoArr = explode(',',$annotations);
	    foreach($annoArr as $anno) {
	      $parts = explode(':',$anno);
	      $id = str_replace('page','',$parts[0]);
	      $target_post = get_post($id);
	      $nodes[] = $post;
	      $nodes[] = $target_post;
	      $edges[] = array($post->ID,$target_post->ID);
	      //echo "'".$post->post_title."','".$target_post->post_title."'\n";
	    }
	}
    }

    foreach($nodes as $node) {
      $returnStr .= '      <node id="'.$node->ID.'" label="'.$node->post_title.'">
        <attvalues>
          <attvalue for="url" value="'.get_permalink($node->ID).'"></attvalue>
        </attvalues>
      </node>';
    }
    $returnStr .= '    </nodes>
    <edges>';
    foreach($edges as $edge) {
      $returnStr .= '      <edge source="'.$edge[0].'" target="'.$edge[1].'"></edge>';
    }

  $returnStr .= '    </edges>
  </graph>
</gexf>';
  header('Content-type: text/xml');
  header('Content-disposition: attachment; filename="export.gexf"');
  echo $returnStr;
  die();
}

if(isset($_GET['exportgexf'])) { eas_annotator_export_gexf(); }


?>
