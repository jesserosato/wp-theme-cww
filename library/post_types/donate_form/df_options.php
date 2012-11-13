<?php
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
    $sections['cww_df_mailchimp_setting_section']    	= __('Mailchimp', 'cww');
    $sections['cww_df_highrise_setting_section']     	= __('Highrise', 'cww');
      
    return $sections;     
}

function cww_df_options_page_fields() {
	$options['cww_df_authorizenet_setting_api_login_id'] = array(
		'section' 	=> 'cww_df_authorizenet_setting_section',
		'id'		=> 'cww_df_authorizenet_setting_api_login_id',
		'title'		=> __('API login ID', 'cww'),
		'desc'		=> __('The Authorize.net API login ID to use for processing donate form donations.', 'cww'),
		'type'		=> 'text',
		'std'		=> __('API login ID', 'cww'),
		'req'		=> true
	);
	
	$options['cww_df_authorizenet_setting_transaction_key'] = array(
		'section' 	=> 'cww_df_authorizenet_setting_section',
		'id'		=> 'cww_df_authorizenet_setting_transaction_key',
		'title'		=> __('Transaction key', 'cww'),
		'desc'		=> __('The Authorize.net transaction key to use for processing donate form donations.', 'cww'),
		'type'		=> 'text',
		'std'		=> __('Token', 'cww'),
		'req'		=> true
	);
	
	$options['cww_df_mailchimp_setting_api_token'] = array(
		'section' 	=> 'cww_df_mailchimp_setting_section',
		'id'		=> 'cww_df_mailchimp_setting_api_token',
		'title'		=> __('API token', 'cww'),
		'desc'		=> __('The Mailchimp API token to use to add donate form users to your Mailchimp lists.', 'cww'),
		'type'		=> 'text',
		'std'		=> __('API token', 'cww')
	);
	
	$options['cww_df_highrise_setting_account'] = array(
		'section' 	=> 'cww_df_highrise_setting_section',
		'id'		=> 'cww_df_highrise_setting_account',
		'title'		=> __('Account', 'cww'),
		'desc'		=> __('The Highrise account to update.', 'cww'),
		'type'		=> 'text',
		'std'		=> __('Account', 'cww')
	);
	
	$options['cww_df_highrise_setting_api_token'] = array(
		'section' 	=> 'cww_df_highrise_setting_section',
		'id'		=> 'cww_df_highrise_setting_api_token',
		'title'		=> __('API token', 'cww'),
		'desc'		=> __('The API token of the Highrise account to update.', 'cww'),
		'type'		=> 'text',
		'std'		=> __('API token', 'cww')
	);
	
	$options['cww_df_highrise_setting_admin_user_id'] = array(
		'section' 	=> 'cww_df_highrise_setting_section',
		'id'		=> 'cww_df_highrise_setting_admin_user_id',
		'title'		=> __('Administrator ID', 'cww'),
		'desc'		=> __('The ID of the Highrise account administrator.', 'cww'),
		'type'		=> 'text',
		'std'		=> __('Admin ID', 'cww'),
		'class'		=> 'numeric'
	);
	
	$options['cww_df_highrise_setting_admin_group_id'] = array(
		'section' 	=> 'cww_df_highrise_setting_section',
		'id'		=> 'cww_df_highrise_setting_admin_group_id',
		'title'		=> __('Administrator group ID', 'cww'),
		'desc'		=> __('The ID of the Highrise administrator group.', 'cww'),
		'type'		=> 'text',
		'std'		=> __('Admin group ID', 'cww'),
		'class'		=> 'numeric'
	);
	
	$options['cww_df_highrise_setting_deals_admin_user_id'] = array(
		'section' 	=> 'cww_df_highrise_setting_section',
		'id'		=> 'cww_df_highrise_setting_deals_admin_user_id',
		'title'		=> __('Deals administrator ID', 'cww'),
		'desc'		=> __('The ID of the Highrise administrator in charge of deals.', 'cww'),
		'type'		=> 'text',
		'std'		=> __('Deals admin ID', 'cww'),
		'class'		=> 'numeric'
	);
	
	$options['cww_df_highrise_setting_task_delay'] = array(
		'section' 	=> 'cww_df_highrise_setting_section',
		'id'		=> 'cww_df_highrise_setting_task_delay',
		'title'		=> __('Task delay', 'cww'),
		'desc'		=> __('Time after donation to set reminder.', 'cww'),
		'type'		=> 'text',
		'std'		=> __('e.g. 6 months', 'cww')
	);
	
	$options['cww_df_highrise_setting_onetime_category_id'] = array(
		'section' 	=> 'cww_df_highrise_setting_section',
		'id'		=> 'cww_df_highrise_setting_onetime_category_id',
		'title'		=> __('One time category ID', 'cww'),
		'desc'		=> __('Highrise Deals category ID for one time donations.', 'cww'),
		'type'		=> 'text',
		'std'		=> __('One time ID', 'cww'),
		'class'		=> 'numeric'
	);
	
	$options['cww_df_highrise_setting_monthly_category_id'] = array(
		'section' 	=> 'cww_df_highrise_setting_section',
		'id'		=> 'cww_df_highrise_setting_monthly_category_id',
		'title'		=> __('Monthly category ID', 'cww'),
		'desc'		=> __('Highrise Deals category ID for monthly donations.', 'cww'),
		'type'		=> 'text',
		'std'		=> __('Monthly ID', 'cww'),
		'class'		=> 'numeric'
	);
	
	$options['cww_df_highrise_setting_annual_category_id'] = array(
		'section' 	=> 'cww_df_highrise_setting_section',
		'id'		=> 'cww_df_highrise_setting_annual_category_id',
		'title'		=> __('Annual category ID', 'cww'),
		'desc'		=> __('Deals category ID for annual donations.', 'cww'),
		'type'		=> 'text',
		'std'		=> __('Annual ID', 'cww'),
		'class'		=> 'numeric'
	);
	
	$options['cww_df_highrise_setting_business_category_id'] = array(
		'section' 	=> 'cww_df_highrise_setting_section',
		'id'		=> 'cww_df_highrise_setting_business_category_id',
		'title'		=> __('Business category ID', 'cww'),
		'desc'		=> __('Deals category ID for business donations.', 'cww'),
		'type'		=> 'text',
		'std'		=> __('Business ID', 'cww'),
		'class'		=> 'numeric'
	);
	
	return $options;
}