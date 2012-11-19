<?php
/************************************************************************************ 
/* Definition and functions for Associate custom post type.
/*
/* By Jesse Rosato, 2012 - jesse.rosato@gmail.com
/************************************************************************************/
require_once(ABSPATH . '/wp-content/themes/cww/library/utilities/CwwPostTypeEngine.class.php');
require_once('associate_meta_boxes.php');


$cww_associate_desc = 'The associate post type provides two shortcodes (the curly brackets and their contents in the examples that follow should be replaced with the appropriate information).  To display a single associate in "excerpt" view, use: <br /><br /><strong style="display:block; text-align: center;">[associate associateid={associate post id} excerpt={true or false}]</strong><br /> Note: the associate ID is required for the single associate shortcode.<br /> Display multiple associates as follows (all of the parameters are optional):<br /><br /><strong style="display:block; text-align: center;">[associates relationship={relationship slug} category={category slug} order={ASC or DESC} number={# to display}]</strong><br />.  For parameter "order", "ASC" (the default) displays the oldest posts first and "DESC" displays the newest posts first.  For parameter "number" (default = 5), use "-1" to display all posts within the relationship and category of your choosing.';
$cww_associate_post_type = array(
	'handle'	=> 'cww_associate',
	'args'		=>array(
		'labels' => array(
			'name' => __( 'Associates' ),
			'singular_name' => __( 'Associate' ),
			'all items' => __( 'All Associates' ),
			'add_new_item' => __( 'Add New Associate' ),
			'edit_item' => __( 'Edit Associate' ),
			'new_item' => __( 'New Associate' ),
			'view_item' => __( 'View Associate' ),
			'search_item' => __( 'Search Associates' ),
			'not_found' => __( 'No Associates found' ),
			'not_found_in_trash' => __( 'No Associates found in trash' )
		),
		'singular_label' => __('associate', 'cww'),
		'description' => __( 'Create an associate, with first name, last name, etc.' ),
		'rewrite' => array('slug' => 'people','with_front' => false),
		'public' => true,
		'publicly_queryable' => true,
		'taxonomies' => array( 'category', 'post_tag' ),
		'has_archive' => true,
		'show_in_nav_menus' => false,
		'menu_position' => 20,
		'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'page-attributes', 'post-formats')
	),
	'meta_box_groups' => array(
		'cww_associate_details' => array(
			'handle' => 'cww_associate_details',
			'title' => __('Associate Details'),
			'desc' => $cww_associate_desc,
			'priority' => 'high',
			'context' => 'normal'
		),
	)
);
$cww_associate_meta_boxes = cww_associate_meta_boxes();
$cww_associate_post_type_engine = new CwwPostTypeEngine($cww_associate_post_type, $cww_associate_meta_boxes);
add_action('init', array(&$cww_associate_post_type_engine, 'create_post_type'));
add_action('admin_init', array(&$cww_associate_post_type_engine, 'add_meta_boxes'));

// Add relationship taxonomy
add_action('init', 'cww_associate_register_taxonomy');
function cww_associate_register_taxonomy( ) {
	register_taxonomy('cww_associate_relationships','cww_associate',array(
		'hierarchical' => true,
		'labels' => array(
			'name' => _x( 'Associate Relationships', 'taxonomy general name', 'cww' ),
			'singular_name' => _x( 'Associate Relationship', 'taxonomy singular name', 'cww' ),
			'search_items' =>  __( 'Search Associate Relationships', 'cww' ),
			'popular_items' => __( 'Popular Associate Relationships', 'cww' ),
			'all_items' => __( 'All Associate Relationships', 'cww' ),
			'parent_item' => null,
			'parent_item_colon' => null,
			'edit_item' => __( 'Edit Associate Relationship', 'cww' ), 
			'update_item' => __( 'Update Associate Relationship', 'cww' ),
			'add_new_item' => __( 'Add New Associate Relationship', 'cww' ),
			'new_item_name' => __( 'New Associate Relationship Name', 'cww' ),
			'separate_items_with_commas' => __( 'Separate Associate Relationships with commas', 'cww' ),
			'add_or_remove_items' => __( 'Add or remove Associate Relationship', 'cww' ),
			'choose_from_most_used' => __( 'Choose from the most used Associate Relationships', 'cww' ),
			'menu_name' => __( 'Relationships', 'cww' ),
		),
		'public' => false,
		'show_in_nav_menus' => false,
		'show_ui' => true,
		'show_tagcloud' => false,
		'query_var' => false,
		'rewrite' => false,
		
	));
}

/************************************************************************************ 
/* Display associate post type HTML
/*
/* @param int $associate_id
/* @param str $type
/************************************************************************************/
function cww_associate_content( $associate_id = false, $type = 'single' ) {
	echo cww_associate_get_content($associate_id, $type);
}

/************************************************************************************ 
/* Return associate post type HTML
/*
/* @param int $associate_id
/* @param str $type
/************************************************************************************/
function cww_associate_get_content($associate_id = fale, $type = 'single') {
	global $post;
	$old_post = $post;
	$post = $associate_id ? get_post( $associate_id ) : $post;
	global $cww_associate_type;
	$cww_associate_type = $type;
	
	$result = '' . get_template_part('template', 'associate');
	$post = $old_post;
	return $result;
}

/************************************************************************************ 
/* Validate and save post meta data.
/*
/* @param int $post_id
/************************************************************************************/
add_action( 'save_post', 'cww_associate_save_post');
function cww_associate_save_post( $post_id ) {
	$post = get_post($post_id);
	// Make sure this is our post type.
	if ( $post->post_type != 'cww_associate' )
		return;
	// verify if this is an auto save routine. 
	// If it is our form has not been submitted, so we dont want to do anything
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
		return;
	// verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times
	if ( !wp_verify_nonce( isset( $_POST['cww_associate_nonce'] ) ? $_POST['cww_associate_nonce'] : false, 'cww_nonce_field_cww_associate' ) )
		return;
	
	// Get the post type object.
    $post_type = get_post_type_object( $post->post_type );
    // Check if the current user has permission to edit this post-type.
    if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
        return;
	foreach ( $_POST as $key => $value ) {
		if ( preg_match( '/^cww_associate_.*/', $key ) )
			update_post_meta( $post_id, $key, trim( $value ) );
	}
}

/************************************************************************************ 
/* Return an associate's content.
/*
/* @param array $atts
/* @param string $content
/*
/* @return string
/************************************************************************************/
add_shortcode( 'associate', 'cww_associate_single_shortcode_callback' );
function cww_associate_single_shortcode_callback( $atts, $content = null ) {
	global $post;
	// If the post is 
	if ( empty( $atts['associateid'] ) || $post->post_type == 'cww_associate' )
		return '<p class="error">Oops!  The associate shortcode requires an associate ID, and/or cannot be used within an associate post.</p>';
	$type = empty($atts['excerpt']) || strtolower($atts['excerpt']) == 'false' ? false : 'multi';
	return cww_associate_get_content( $atts['associateid'], $type );
}

/************************************************************************************ 
/* Return an multiple associates' content.
/*
/* @param array $atts
/* @param string $content
/*
/* @return string
/************************************************************************************/
add_shortcode( 'associates', 'cww_associate_multiple_shortcode_callback' );
function cww_associate_multiple_shortcode_callback( $atts, $content = null ) {
	global $post;
	if ( $post->post_type == 'cww_associate' )
		return '<p class="error">Oops!  The associates shortcode cannot be used within an associate post.</p>';
	$defaults_array = array(
		'relationship' => false,
		'category' => false,
		'order' => 'ASC',
		'number' => 5
	);
	extract(shortcode_atts( $defaults_array, $atts ));
	$category = get_category_by_slug($category);
	$args = array(
		'post_type' => 'cww_associate',
		'posts_per_page' => $number,
		'posts_per_archive_page' => $number,
		'order' => $order,
	);
	if ($category)
		$args['cat'] = $category->term_id;
	if ($relationship) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'cww_associate_relationships',
				'field' => 'slug',
				'terms' => $relationship
			)
		);
	}
	$result = '';
	$associates_query = new WP_Query( $args );

	while ( $associates_query->have_posts() ) {
		$associates_query->the_post();
		$result .= cww_associate_get_content( get_the_ID(), 'multi');
	}
	return $result;
}