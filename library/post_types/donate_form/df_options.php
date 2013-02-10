<?php
// File violates naming convention and uses df_ prefix to avoid conflict with wp-admin/options.php

/** 
 * Define our settings sections 
 * 
 * array key=$id, array value=$title in: add_settings_section( $id, $title, $callback, $page ); 
 * @return array 
 */  
function cww_df_options_page_sections() {  
    $sections = array();  
    // $sections[$id]       = __($title, 'wptuts_textdomain');  
    $sections['cww_df_authorizenet_setting_section']    = __('Authorize.net', 'cww');
    return apply_filters('cww_df_options_page_sections', $sections);
}

function cww_df_options_page_fields() {
	$options['cww_df_authorizenet_setting_api_login_id'] = array(
		'section' 	=> 'cww_df_authorizenet_setting_section',
		'id'		=> 'cww_df_authorizenet_setting_api_login_id',
		'title'		=> __('API login ID', 'cww'),
		'desc'		=> __('The Authorize.net API login ID for the account to use when processing donations.', 'cww'),
		'type'		=> 'password',
		'std'		=> __('API login ID', 'cww'),
		'req'		=> true
	);
	
	$options['cww_df_authorizenet_setting_transaction_key'] = array(
		'section' 	=> 'cww_df_authorizenet_setting_section',
		'id'		=> 'cww_df_authorizenet_setting_transaction_key',
		'title'		=> __('Transaction key', 'cww'),
		'desc'		=> __('The Authorize.net transaction key for the account to use when processing donations.', 'cww'),
		'type'		=> 'password',
		'std'		=> __('Token', 'cww'),
		'req'		=> true
	);
	
	
	return apply_filters('cww_df_options_page_fields', $options);
}