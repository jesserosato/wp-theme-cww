<?php
/************************************************************************************ 
/* Definition and functions for Event custom post type.
/*
/* By Jesse Rosato, 2012 - jesse.rosato@gmail.com
/************************************************************************************/
require_once(ABSPATH . '/wp-content/themes/cww/library/utilities/CwwPostTypeEngine.class.php');
require_once('event_meta_boxes.php');

$cww_event_desc = __('You can use the data entered below via shortcodes. Click the "more info" link on a setting to see an example of its associated shortcode.', 'cww') . '<br />' . __('There are two shortcodes for displaying entire events:', 'cww') . '<br /><strong>Single event shortcode</strong>: [event eventid="1234"]<br /><strong>Multiple event shortcode</strong>: [events category="category_slug"]';

$cww_event_post_type = array(
	'handle'	=> 'cww_event',
	'args'		=>array(
		'labels' => array(
			'name' => __( 'Events' ),
			'singular_name' => __( 'Event' ),
			'all items' => __( 'All Events' ),
			'add_new_item' => __( 'Add New Event' ),
			'edit_item' => __( 'Edit Event' ),
			'new_item' => __( 'New Event' ),
			'view_item' => __( 'View Event' ),
			'search_item' => __( 'Search Events' ),
			'not_found' => __( 'No Events found' ),
			'not_found_in_trash' => __( 'No Events found in trash' )
		),
		'singular_label' => __('event', 'cww'),
		'description' => __( 'Create an event, with start date and time, etc.' ),
		'rewrite' => array('slug' => 'cww_events','with_front' => false),
		'public' => true,
		'publicly_queryable' => true,
		'has_archive' => true,
		'show_in_nav_menus' => false,
		'menu_position' => 20,
		'taxonomies' => array( 'category', 'post_tag' ),
		'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'page-attributes', 'post-formats')
	),
	'meta_box_groups' => array(
		'cww_event_details' => array(
			'handle' => 'cww_event_details',
			'title' => __('Event Details'),
			'desc' => $cww_event_desc,
			'priority' => 'high',
			'context' => 'normal'
		),
	)
);
$cww_event_meta_boxes = cww_event_meta_boxes();
$cww_event_post_type_engine = new CwwPostTypeEngine($cww_event_post_type, $cww_event_meta_boxes);
add_action('init', array(&$cww_event_post_type_engine, 'create_post_type'));
add_action('admin_init', array(&$cww_event_post_type_engine, 'add_meta_boxes'));


/************************************************************************************ 
/* Validate and save post meta data.
/*
/* @param int $post_id
/************************************************************************************/
add_action( 'save_post', 'cww_event_save_post');
function cww_event_save_post( $post_id ) {
	$post = get_post($post_id);
	
	// Make sure this is our post type.
	if ( $post->post_type != 'cww_event' )
		return;
	
	// Verify if this is an auto save routine. 
	// If it is our form has not been submitted, so we dont want to do anything
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
		return;
	// Verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times
	if ( !wp_verify_nonce( isset($_POST['cww_event_nonce']) ? $_POST['cww_event_nonce'] : false, 'cww_nonce_field_cww_event' ) )
		return;
	
	// Get the post type object.
    $post_type = get_post_type_object( $post->post_type );
    // Check if the current user has permission to edit this post-type.
    if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
        return;
	foreach ( $_POST as $key => $value ) {
		if ( preg_match( '/^cww_event_.*/', $key ) ) {
			// Times
			if (is_array($value)) {
				$value = $value[1] . ':' . $value[2] . $value[3];
			}
			update_post_meta( $post_id, $key, trim( $value ) );
		}
	}
}

/************************************************************************************ 
/* Return whether or not an event has occured.
/*
/* @param object $query
/*
/* @return object
/************************************************************************************/
function cww_event_is_over( $event_id = false ) {
	if (!$event_id) {
		$post = $GLOBALS['post'];
		$event_id = $post->ID;
	}
	$end_date = get_post_meta($event_id, 'cww_event_end_date', true);
	$end_time = get_post_meta($event_id, 'cww_event_end_time', true);
	$end = strtotime($end_date . ' ' . $end_time);
	$now = time();
	return $now > $end;
}

/************************************************************************************ 
/* Include the event post type on category and tag pages.
/*
/* @param object $query
/*
/* @return object
/************************************************************************************/
add_filter('pre_get_posts', 'cww_event_display_on_cat_and_tag_pages');
function cww_event_display_on_cat_and_tag_pages( $query ) {
	if( is_category() || is_tag() ) {
		$post_type = get_query_var('post_type');
		$post_type = $post_type ? $post_type : array('post', 'cww_event');
		$query->set('post_type',$post_type);
		return $query;
	}
}

/************************************************************************************ 
/* Display an event (or event excerpt)
/*
/* @param int $event_id
/* @param string $type
/************************************************************************************/
function cww_event_content( $event_id = false, $type = 'single' ) {
	echo cww_event_get_content($event_id, $type);
}

/************************************************************************************ 
/* Return an event's content (or event excerpt)
/*
/* @param int $event_id
/* @param string $type
/*
/* @return string
/************************************************************************************/
function cww_event_get_content( $event_id = false, $type = 'single' ) {
	global $cww_event_type;
	global $post;
	$cww_event_type	= $type;
	$old_post		= $post;
	$post			= $event_id ? get_post($event_id) : $post;
	error_log(get_post_meta($post->ID, 'cww_event_start_date', true));
	error_log(get_post_meta($post->ID, 'cww_event_start_time', true));
	ob_start();
	get_template_part('template', 'event');
	$result = ob_get_contents();
	ob_end_clean();
	$post			= $old_post;
	return $result;
}

/************************************************************************************ 
/* Return an event's content.
/*
/* @param array $atts
/* @param string $content
/*
/* @return string
/************************************************************************************/
add_shortcode( 'event', 'cww_event_single_shortcode_callback' );
function cww_event_single_shortcode_callback( $atts, $content = null ) {
	$event_id = empty($atts['eventid']) ? false : $atts['eventid'];
	return cww_event_get_content($event_id);
}

/************************************************************************************ 
/* Return multiple events' content.
/*
/* @param array $atts
/* @param string $content
/*
/* @return string
/************************************************************************************/
add_shortcode( 'events', 'cww_event_multiple_shortcode_callback' );
function cww_event_multiple_shortcode_callback( $atts, $content = null ) {
	$category = empty($atts['category']) ? false : $atts['category'];
	$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
	$args = array(
		'post_type' => 'cww_event',
		'paged' => $paged,
		'meta_key' => 'cww_event_start_date',
		'orderby' => 'cww_event_start_date',
	);
	if ($category)
		$args['category_name'] = $category;
	$events = get_posts($args);
	$result = '';
	foreach ( $events as $event )
		$result .= cww_event_get_content( $event->ID, 'multi' );
	return $result;
}

/************************************************************************************ 
/* Return an event's start date.
/*
/* @param array $atts
/* @param string $content
/*
/* @return string
/************************************************************************************/
add_shortcode( 'eventstartdate', 'cww_event_startdate_shortcode_callback' );
function cww_event_startdate_shortcode_callback( $atts, $content = null ) {
	$post = empty($atts['eventid']) ? $GLOBALS['post'] : get_post($atts['eventid']);
	$format = empty($atts['format']) ? 'l, F jS, Y' : $atts['format'];
	$start_date = get_post_meta($post->ID, 'cww_event_start_date', true);
	return date($format, strtotime($start_date));
}

/************************************************************************************ 
/* Return an event's start time.
/*
/* @param array $atts
/* @param string $content
/*
/* @return string
/************************************************************************************/
add_shortcode( 'eventstarttime', 'cww_event_starttime_shortcode_callback');
function cww_event_starttime_shortcode_callback( $atts, $content = null ) {
	$post = empty($atts['eventid']) ? $GLOBALS['post'] : get_post($atts['eventid']);
	return (get_post_meta($post->ID, 'cww_event_start_time', true) . 'm');
}

/************************************************************************************ 
/* Return an event's end date.
/*
/* @param array $atts
/* @param string $content
/*
/* @return string
/************************************************************************************/
add_shortcode( 'eventenddate', 'cww_event_enddate_shortcode_callback' );
function cww_event_enddate_shortcode_callback( $atts, $content = null ) {
	$post = empty($atts['eventid']) ? $GLOBALS['post'] : get_post($atts['eventid']);
	$format = empty($atts['format']) ? 'l, F jS, Y' : $atts['format'];
	$end_date = get_post_meta($post->ID, 'cww_event_end_date', true);
	return date($format, strtotime($end_date));
}

/************************************************************************************ 
/* Return an event's end time.
/*
/* @param array $atts
/* @param string $content
/*
/* @return string
/************************************************************************************/
add_shortcode( 'eventendtime', 'cww_event_endtime_shortcode_callback');
function cww_event_endtime_shortcode_callback( $atts, $content = null ) {
	$post = empty($atts['eventid']) ? $GLOBALS['post'] : get_post($atts['eventid']);
	return (get_post_meta($post->ID, 'cww_event_end_time', true) . 'm');
}

/************************************************************************************ 
/* Return an event's location.
/*
/* @param array $atts
/* @param string $content
/*
/* @return string
/************************************************************************************/
add_shortcode( 'eventlocation', 'cww_event_location_shortcode_callback');
function cww_event_location_shortcode_callback( $atts, $content = null ) {
	$post = empty($atts['eventid']) ? $GLOBALS['post'] : get_post($atts['eventid']);
	return get_post_meta($post->ID, 'cww_event_location', true);
}

/************************************************************************************ 
/* Return an event's details.
/*
/* @param array $atts
/* @param string $content
/*
/* @return string
/************************************************************************************/
add_shortcode( 'eventinfo', 'cww_event_info_shortcode_callback');
function cww_event_info_shortcode_callback( $atts, $content = null ) {
	$post = empty($atts['eventid']) ? $GLOBALS['post'] : get_post($atts['eventid']);
	return get_post_meta($post->ID, 'cww_event_info', true);
}

/************************************************************************************ 
/* Return an event's registration button.
/*
/* @param array $atts
/* @param string $content
/*
/* @return string
/************************************************************************************/
add_shortcode( 'eventregbtn', 'cww_event_regbtn_shortcode_callback');
function cww_event_regbtn_shortcode_callback( $atts, $content = null ) {
	$post = empty($atts['eventid']) ? $GLOBALS['post'] : get_post($atts['eventid']);
	$url = get_post_meta($post->ID, 'cww_event_reg_btn_url', true);
	if ( !$url )
		return '';
	
	$class = empty( $atts['class'] ) ? 'button gray small' : $atts['class'];
	$content = empty( $content ) ? 'Register' : $content;
	return '<a href="' . $url . '" class="' . $class . '"><span>' . $content . '</span></a>';
}