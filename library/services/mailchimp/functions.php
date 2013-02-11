<?php
// Mailchimp interface
require_once 'MailchimpCww.php';

add_filter('cww_df_options_page_sections', 'cww_df_add_mailchimp_options_page_section');
/**
 * Add Mailchimp section to donate form options page.
 *
 * @param $settings array()
 *
 */
function cww_df_add_mailchimp_options_page_section( $sections )
{
	$sections['cww_df_mailchimp_setting_section'] = __('Mailchimp', 'cww');
	return $sections;
}

add_filter('cww_df_options_page_fields', 'cww_df_add_mailchimp_options_page_fields');
/**
 * Add Mailchimp fields to donate form options page.
 *
 * @param $options array
 *
 */
function cww_df_add_mailchimp_options_page_fields( $options )
{
	$options['cww_df_mailchimp_setting_api_token'] = array(
		'section' 	=> 'cww_df_mailchimp_setting_section',
		'id'		=> 'cww_df_mailchimp_setting_api_token',
		'title'		=> __('API token', 'cww'),
		'desc'		=> __('The Mailchimp API token to use to add donate form users to your Mailchimp lists.', 'cww'),
		'type'		=> 'password',
		'std'		=> __('API token', 'cww')
	);
	return $options;
}

add_filter('cww_df_meta_boxes', 'cww_df_add_mailchimp_meta_boxes');
/**
 * Add Mailchimp meta boxes to edit donate form page
 *
 * @param $meta_boxes array
 *
 */
function cww_df_add_mailchimp_meta_boxes( $meta_boxes )
{
	if ( cww_df_mailchimp_is_enabled() ) {
		// Mailchimp List ID
		$meta_boxes['cww_df_settings']['cww_df_mc_list_id'] = array(
			'handle'	=> 'cww_df_mc_list_id',
			'title' 	=> __('Mailchimp List ID'),
			'args'		=> array(
				'type' 		=> 'text',
				'desc' 		=> __("Please enter the ID of the Mailchimp list to which you'd like to add users who complete this form.  Leave blank to disable Mailchimp sign-up on this form.", 'cww'),
				'default' 	=> '',
			)
		);
	}
	return $meta_boxes;
}

add_action('cww_df_post_process', 'cww_df_submit_data_to_mailchimp', 1, 3);
/************************************************************************************ 
/* Submit data to Mailchimp (if settings and meta data call for it).
/*
/* @param $data array or bool
/*
/* @return bool
/************************************************************************************/
function cww_df_submit_data_to_mailchimp( $data, $meta_data, $settings ) 
{
	if ( empty( $data ) || !is_array( $data ) )
		return false;
	if ( empty( $meta_data ) || !is_array( $meta_data ) )
		return false;
	if ( empty( $settings ) || !is_array( $settings ) )
		return false;
	if ( !cww_df_mailchimp_is_enabled() )
		return false;
		
	$meta_data['mc_list_id'] = get_post_meta($meta_data['post_id'], 'cww_df_mc_list_id', true);
	
	if ( empty( $meta_data['mc_list_id'] ) || empty( $data['donor']['subscribe'] ) )
		return false;
	if ( empty( $data['donor']['email'] ) )
		return false;
		
	syncMailchimpContact(
		$data['donor'], 
		$settings['cww_df_mailchimp_setting_api_token'], 
		$meta_data['mc_list_id']
	);
	return true;
} // end submit_data_to_mailchimp()

/**
 * Returns true if the settings to enable Mailchimp have been set.
 *
 * @return bool
 *
 */
function cww_df_mailchimp_is_enabled() {
	$options = cww_df_options_page_fields();
	return (!empty($options['cww_df_mailchimp_setting_api_token']) && cww_df_option_is_set($options['cww_df_mailchimp_setting_api_token']));
}