<?php
// Highrise interface
require_once 'CwwHighriseInterface.class.php';

add_filter('cww_df_options_page_sections', 'cww_df_add_highrise_options_page_section');
/**
 * Add Highrise section to donate form options page
 *
 * @param $sections array
 *
 */
function cww_df_add_highrise_options_page_section( $sections )
{
	$sections['cww_df_highrise_setting_section']     	= __('Highrise', 'cww');
	return $sections;
}

add_filter('cww_df_options_page_fields', 'cww_df_add_highrise_options_page_fields');
/**
 * Add Highrise fields to donate form options page
 *
 * @param $options array
 *
 */
function cww_df_add_highrise_options_page_fields( $options )
{
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
		'type'		=> 'password',
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
	
	return $options;
}

add_filter('cww_df_meta_boxes', 'cww_df_add_highrise_meta_boxes');
/**
 * Add Highrise meta boxes to edit donate form page.
 *
 * @param $meta_boxes array
 *
 */
function cww_df_add_highrise_meta_boxes( $meta_boxes )
{
	
	if ( cww_df_highrise_is_enabled() ) {
		$meta_boxes['cww_df_settings']['cww_df_hr_update'] = array(
			'handle'	=> 'cww_df_hr_update',
			'title'		=> __('Update Highrise'),
			'args'		=> array(
				'type'		=> 'checkbox',
				'desc'		=> __("Choose whether or not to update the Highrise database with the user and donation data upon completion of this form.", 'cww'),
				'default'	=> '1',
			)
		);
		
		// Highrise Deals Admin Group ID
		$meta_boxes['cww_df_settings']['cww_df_hr_deals_admin_id'] = array(
			'handle'	=> 'cww_df_hr_deals_admin_id',
			'title' 	=> __('Highrise Deals Administrator ID'),
			'args'		=> array(
				'type' 		=> 'text',
				'desc' 		=> __("Please enter the ID of the Highrise user responsible for Deals made using this form.  Leave blank to use the general donation form setting.", 'cww'),
				'default' 	=> '',
			)
		);
	}
	return $meta_boxes;
}

add_action('cww_df_post_process', 'cww_df_submit_data_to_highrise', 1, 3);
/************************************************************************************ 
/* Submit data to Highrise (if settings and meta data call for it).
/*
/* @return bool
/************************************************************************************/
function cww_df_submit_data_to_highrise( $data, $meta_data, $settings )
{
	if ( empty( $data ) || !is_array( $data ) )
		return false;
	if ( empty( $meta_data ) || !is_array( $meta_data ) )
		return false;
	if ( empty( $settings ) || !is_array( $settings ) )
		return false;
	if ( !cww_df_highrise_is_enabled() )
		return false;
	if ( empty( $meta_data['hr_update'] ) )
		return false;
	
	// Process Highrise transaction data.		
	$type = $data['donation']['type_code'];
	if ($type == 'monthly')
		$duration = $meta_data['monthly_duration'];
	if ($type == 'annual')
		$duration = $meta_data['annual_duration'];

	if (empty($duration))
		$duration = 0;
		
	if ($type != 'onetime')
		$start = $data['donation']['start_date'];
	$tags = array();
	$tag_arr = wp_get_post_tags($meta_data['post_id']);
	foreach ($tag_arr as $tag_obj) {
		$tags[] = $tag_obj->name;
	}
	$hr_transaction	= array(
		'source' 		=> $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
		'tags' 			=> $tags,
		'products' 		=> array(
			0 => array(
				'amount' => $data['donation']['amount'],
				'type' => $type,
				'duration' => $duration
			),
		),
	);
	

	if ( !empty( $data['donation']['transaction_id'] ) )
		$hr_transaction['id'] = $data['donation']['transaction_id'];
	else
		$hr_transaction['id'] = $data['donation']['subscription_id'];
		
	$hr_transaction['account']		= 'AUTH.NET';
	$hr_transaction['pay_method'] 	= $data['card']['type'];
	$hr_transaction['card_exp']		= $data['card']['exp'];
	
	if (isset($start))
		$hr_transaction['products'][0]['start_date'] = $start;
	
	if ( empty( $settings['cww_df_highrise_setting_account'] ) )
		return false;
	$hr_account = $settings['cww_df_highrise_setting_account'];
	if ( empty( $settings['cww_df_highrise_setting_api_token'] ) )
		return false;
	$hr_token	= $settings['cww_df_highrise_setting_api_token'];	
		
	$hr_config_fields = array(
		'admin_user_id' => 'cww_df_highrise_setting_admin_user_id',
		'admin_group_id' => 'cww_df_highrise_setting_admin_group_id',
		'deals_admin_user_id' => 'cww_df_highrise_setting_deals_admin_user_id',
		'task_delay' => 'cww_df_highrise_setting_task_delay',
		'general_onetime_deal_category_id' => 'cww_df_highrise_setting_onetime_category_id',
		'general_monthly_deal_category_id' => 'cww_df_highrise_setting_monthly_category_id',
		'general_annual_deal_category_id' =>'cww_df_highrise_setting_annual_category_id'
	);
	$hr_config = array();
	foreach ( $hr_config_fields as $index => $key ) {
		if ( empty( $settings[$key] ) )
			return false;
		$hr_config[$index] = $settings[$key];
	}
	
	$hr_accepts = array(
		'first_name', 
		'last_name',  
		'company',
		'address',
		'city',
		'state',
		'zip',
		'country',
		'phone',
		'email',
		'notes',
	); 
	foreach( $data['donor'] as $key => $val ) {
		if ( !in_array($key, $hr_accepts) )
			unset($data['donor'][$key]);
	}
	
	try {
		$hr = new CwwHighriseInterface($hr_config, $hr_account, $hr_token);
		$person = $hr->syncContact($data['donor']);
		$hr->addTransaction($hr_transaction, $person);
	} catch(Exception $e) {
		if( WP_DEBUG === true ) {
			error_log(__FILE__ . ": " . $e->getMessage());
		}
		return false;
	}
	return true;
} // end submit_data_to_highrise()

/**
 * Returns true if the settings to enable Highrise have been set.
 *
 * @return bool
 *
 */
function cww_df_highrise_is_enabled() {
	$options = cww_df_options_page_fields();
	$required = array(
		'cww_df_highrise_setting_account',
		'cww_df_highrise_setting_api_token',
		'cww_df_highrise_setting_admin_user_id',
		'cww_df_highrise_setting_admin_group_id',
		'cww_df_highrise_setting_deals_admin_user_id',
		'cww_df_highrise_setting_task_delay',
		'cww_df_highrise_setting_onetime_category_id',
		'cww_df_highrise_setting_monthly_category_id',
		'cww_df_highrise_setting_annual_category_id',
	);
	foreach ( $required as $id ) {
		if ( empty( $options[$id]) || !cww_df_option_is_set( $options[$id] ) )
			return false;
	}
	return true;
}