<?php
/*
  Plugin Name: trololol
  Plugin URI: http://www.dagen.ifi.uio.no
  Description: Plugin to manage venues for studentersamfundet.no
  Version 0.1
  Author: Sjur Hernes
  Author URI: grey.sjux.net
  License: GPL v2 or later
*/
?>

<?php

if (!class_exists("NeufVenues")) {

  class NeufVenues{

    function NeufVenues(){

      /**
	 Create the fields the post type should have
      */
      function neuf_venue_post_type() {
	register_post_type(
			   'venue',
			   array(
				 'labels' => array(
						   'name'                  =>      __( 'Venues'                       ),
						   'singular_name'         =>      __( 'Venues'                         ),
						   'add_new'               =>      __( 'Add new venue'             ),
						   'add_new_item'          =>      __( 'add new venue'             ),
						   'edit_item'             =>      __( 'Rediger bedrift'                 ),
						   'new_item'              =>      __( 'Legg til ny bedrift'             ),
						   'view_item'             =>      __( 'Vis bedrift'                     ),
						   'search_items'          =>      __( 'Søk etter bedrift'               ),
						   'not_found'             =>      __( 'ingen bedrifter funnet'          ),
						   'not_found_in_trash'    =>      __( 'ingen bedrifter funnet i søppel' )
						   ),
				 'menu_position'       =>  5,
				 'public'              =>  true,
				 'publicly_queryable'  =>  true,
				 'query_var'           =>  'venue',
				 'show_ui'             =>  true,
				 'capability_type'     =>  'post',
				 'supports'            =>  array(
								 'title',
								 'editor',
								 'thumbnail',
								 'administrator',
								 ),
				 'register_meta_box_cb' => 'add_venue_metaboxes',
				 )
			   );
      }

       
      /*******************************************************************************
      ********************************************************************************
      **  Add meta-boxes   ***********************************************************
      ********************************************************************************
      *******************************************************************************/

      function add_venue_metaboxes() {

	add_meta_box(
		     'neuf_venue_type',
		     __('Venue Type'),
		     'neuf_venue_custom_box',
		     'venue',
		     'side',
		     'high'
		     );
      }

      /*******************************************************************************
      ********************************************************************************
      **  Venue metabox  *************************************************************
      ********************************************************************************
      *******************************************************************************/

      function neuf_venue_custom_box(){

	global $post;

	//$venue_type = get_post_meta($post->ID, 'neuf_venues_venuesize', true) ;
	$venue_type = get_post_meta($post->ID, 'neuf_venues_venuesize') ? get_post_meta($post->ID, 'neuf_venues_venuesize', true) : "0";
	echo '<br />Plass (antall mennesker):<br /><input name="neuf_venues_venuesize" type="text" value="'.$venue_type.'"  />';
	
      }

      /**
       *  When the post is saved, saves our custom data
       */ 


      function neuf_venue_save_info( $post_id ) {

	if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE )  return $post_id;

	// Check permissions
	// TODO
	if ( !current_user_can( 'edit_post', $post_id ) ) return $post_id;

	// Get posted data
	$venues_venuesize = $_POST['neuf_venues_venuesize'];
	
	$quicksave = array('venues_venuesize');
	foreach($quicksave as $save){
	  if( !($meta = get_post_meta($post_id, 'neuf_' . $save)))
	    add_post_meta($post_id, 'neuf_' . $save, true);
	  else if($meta != $$save)
	    update_post_meta($post_id, 'neuf_' . $save, $$save);
	}   
	
	return $post_id;
      }

      /** View of the custom page */

      function neuf_venue_list() {
	global $post, $wp_locale;
	
	$venues = new WP_Query( array(
				      'post_type' => 'venue',
				      'posts_per_page' => -1,
				      'meta_key' => 'neuf_venues_venuesize',
				      'orderby' => 'meta_value',
				      'order' => 'ASC'
				      ) 
				);
	$html = '';
	
	if ( $venues->have_posts() ) :
	  $date = "";
	
	$html .= '<table class="venue-table">';
	
	while ( $venues->have_posts() ) :
	  $venues->the_post();
	
	$type  = get_post_meta( $post->ID, 'neuf_venues_venuesize',   true);
	
	$html .= '    <tr>';
	$html .= '        <td class="title" style="padding-right:10px;"><a href="' . get_permalink() . '">' . get_the_title() . '</a></td>';
	$html .= '        <td class="bedtype" style="font-size:smaller;">' . $type . '</td>';
	$html .= '    </tr>';
	endwhile;
	
	$html .= '</table><!-- .venue-table -->';
	endif;

	return $html;
	
      }
    }
  }
}

if (class_exists("NeufVenues")) {
  $neuf_venue_object = new NeufVenues();
}  

if ( isset($neuf_venue_object)){

  /** 
      Register the bedrift post type
  */
  add_action(    'init',                 'neuf_venue_post_type');
  add_action(    'save_post',            'neuf_venue_save_info');
  add_shortcode( 'neuf_venues',   'neuf_venue_list'  );

}

?>
