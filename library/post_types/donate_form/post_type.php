<?php
// Customize the following with the theme or plugin parent.
define('DF_PARENT_ROOT', get_theme_root().'/cww');
require_once( DF_PARENT_ROOT . '/library/utilities/CwwPostTypeEngine.class.php');
require_once('meta_boxes.php');
require_once('df_options.php');

$cww_df_post_type = array(
	'handle'	=> 'cww_donate_form',
	'args'		=>array(
		'labels' => array(
			'name' => __( 'Donate Forms' ),
			'singular_name' => __( 'Donate Form' ),
			'all items' => __( 'All Donate Forms' ),
			'add_new_item' => __( 'Add New Donate Form' ),
			'edit_item' => __( 'Edit Donate Form' ),
			'new_item' => __( 'New Donate Form' ),
			'view_item' => __( 'View Donate Form' ),
			'search_item' => __( 'Search Donate Forms' ),
			'not_found' => __( 'No Donate Forms found' ),
			'not_found_in_trash' => __( 'No Donate Forms found in trash' )
		),
		'description' => __( 'Use this post type to create new donate forms.' ),
		'rewrite' => array('slug' => 'give','with_front' => false),
		'public' => true,
		'has_archive' => false,
		'show_in_nav_menus' => false,
		'menu_position' => 20,
		'taxonomies' => array('post_tag'),
		'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'page-attributes', 'post-formats')
	),
	'meta_box_groups' => array(
		'cww_df_settings' => array(
			'handle' => 'cww_df_settings',
			'title' => __('Donate Form Settings'),
			'priority' => 'high',
			'context' => 'normal'
		),
	)
);
$cww_df_meta_boxes = cww_df_meta_boxes();
$cww_df_post_type_engine = new CwwPostTypeEngine($cww_df_post_type, $cww_df_meta_boxes);
add_action('init', array(&$cww_df_post_type_engine, 'create_post_type'));
add_action('admin_init', array(&$cww_df_post_type_engine, 'add_meta_boxes'));

add_action( 'save_post', 'cww_df_save_post' );
/**
 * Save or update a donate form post
 *
 * @param $post_id string
 *
 */
function cww_df_save_post( $post_id ) {
	$post = get_post($post_id);
	$meta_boxes = cww_df_meta_boxes(true, true);
	// Make sure this is our post type.
	if ( $post->post_type != 'cww_donate_form' )
		return;
	// verify if this is an auto save routine. 
	// If it is our form has not been submitted, so we dont want to do anything
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
		return;
	// verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times
	if ( !wp_verify_nonce( ( isset( $_POST['cww_donate_form_nonce'] ) ? $_POST['cww_donate_form_nonce'] : '' ), 'cww_nonce_field_cww_donate_form' ) )
		return;
	
	// Get the post type object.
    $post_type = get_post_type_object( $post->post_type );
    
    // Check if the current user has permission to edit the post.
    if ( !current_user_can( $post_type->cap->edit_post, $post_id ) )
        return;
	
	// OK, we're authenticated: we need to find and save the data
	foreach ( $meta_boxes['cww_df_settings'] as $key => $meta_box ) {
		if ( $meta_box['args']['type'] == 'checkbox' ) {
			$_POST[$key] = !empty( $_POST[$key] ) ? 1 : 0;
		}	
	}
	
	foreach ( $_POST as $key => $value ) {
		if ( preg_match( '/^cww_df_.*/', $key ) ) {
			$value = trim( $_POST[$key] );
			update_post_meta( $post_id, $key, $value );
		}
	}
}

add_action( 'admin_notices','cww_df_admin_notice' );
/**
 * Sets an admin notice if the Donate Form post type has not been configured.
 *
 */
function cww_df_admin_notice(){
	if ( !current_user_can('manage_options') )
		return;
	
	if ( cww_df_required_options_not_set() ) {
	?>
		<div class="error">
		  <p>
		  	<strong><?php _e('Courage Worldwide Notice'); ?></strong><br />
		  </p>
		  <p>
		    <?php _e( "Donate forms are not enabled.  You must first supply your sitewide information in 'Settings >> Donate forms'." ); ?>
		  </p>
		</div>
	<?php
	}
}
 
add_filter( 'custom_menu_order', 'cww_df_required_options_not_set' );
/**
 * Returns a boolean indicating if the required post type options are not set
 *
 * @return bool
 *
 */
function cww_df_required_options_not_set() {
	return !cww_df_required_options_are_set();
}

/**
 * Returns a boolean indicating if the required post type options are set
 *
 * @return bool
 *
 */
function cww_df_required_options_are_set() {
 	$options = cww_df_options_page_fields();
	foreach ( $options as $option ) {
		if ( isset( $option['req'] ) && $option['req'] && !cww_df_option_is_set($option) )
			return false;
	}
    return true;
 }

/**
 * Returns a boolean indicating if a given post type option is set
 *
 * @param $option string - The ID (see df_meta_boxes.php) of a meta box.
 *
 * @return bool
 *
 */
function cww_df_option_is_set( $option ) {
	static $settings = false;
	$settings = $settings ? $settings : get_option( 'cww_df_options' );
	$setting = isset( $settings[$option['id']] ) ? $settings[$option['id']] : false;
	return ( $setting && $option['std'] != $setting );
}

add_filter( 'menu_order', 'cww_df_hide_post_type' );
/**
 * Remove the edit donate form menu from the menu (triggered by action 'custom_menu_order')
 *
 * @param $menu_order array
 *
 * @return array
 *
 */
function cww_df_hide_post_type($menu_order) {		
	global $menu;
	foreach ( $menu as $key => $array ) {
		if ( in_array( 'edit.php?post_type=cww_donate_form', $array ) ) 
			$unset_key = $key;
	}
	unset($menu[$unset_key]);
	return $menu_order;
}