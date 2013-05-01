<?php
// Salesforce interface
require_once 'CwwSalesforceInterface.class.php';
require_once 'JmrDateTimeTools.php';

add_action('cww_df_post_process', 'cww_df_submit_data_to_salesforce', 1, 3);
/************************************************************************************ 
/* Submit data to Salesforce (if settings and meta data call for it).
/*
/* @param $data array or bool
/*
/* @return bool
/************************************************************************************/
function cww_df_submit_data_to_salesforce( $data, $meta_data, $settings ) {
	if ( empty( $data ) || !is_array( $data ) )
		return false;
	if ( empty( $meta_data ) || !is_array( $meta_data ) )
		return false;
	if ( empty( $settings ) || !is_array( $settings ) )
		return false;	
	
	$sf_meta_fields = array(
		'sf_update'					=> 'cww_df_sf_update',
		'sf_campaign'				=> 'cww_df_sf_campaign',
		'sf_category'				=> 'cww_df_sf_category',
		'sf_record_owner'			=> 'cww_df_sf_record_owner',
		'sf_task_user'				=> 'cww_df_sf_task_user',
		'sf_donation_exp_task_desc'	=> 'cww_df_sf_donation_exp_task_desc',
		'sf_card_exp_task_desc'		=> 'cww_df_sf_card_exp_task_desc'
	);
	
	foreach ( $sf_meta_fields as $key => $field )
			$meta_data[$key] = get_post_meta($meta_data['post_id'], $field, true);
	
	error_log(print_r($meta_data, true));
	
	if ( !cww_df_salesforce_is_enabled() || empty( $meta_data['sf_update'] ) )
		return false;
	
	// Unencode data for Salesforce
	foreach ( $data as $class => $arr ) {
		if (!is_array($arr))
			continue;
		foreach( $arr as $key => $val ) {
			if ( !is_array( $val ) )
				$data[$class][$key] = htmlspecialchars_decode($val, ENT_QUOTES);
		}
	}
	
	
	$sf_info = array(
		'username'			=> $settings['cww_df_salesforce_setting_username'],
		'password'			=> $settings['cww_df_salesforce_setting_password'],
		'security_token'	=> $settings['cww_df_salesforce_setting_security_token'],
	);
	if ( !empty( $settings['cww_df_salesforce_setting_sandbox'] ) )
		$sf_info['sandbox'] = $settings['cww_df_salesforce_setting_sandbox'];
	
	$contact = array(
		'Salutation'							=> $data['donor']['salutation'],
		'FirstName'								=> $data['donor']['first_name'],
		'LastName'								=> $data['donor']['last_name'],
		'Email'									=> $data['donor']['email'],
		'MailingCity'							=> $data['donor']['city'],
		'MailingState'							=> $data['donor']['state'],
		'MailingCountry'						=> $data['donor']['country'],
		'MailingPostalCode'						=> $data['donor']['zip'],
		'MailingStreet'							=> $data['donor']['address'],
		'Phone'									=> $data['donor']['phone'],
		'org'									=> array(
			'meta' => array(
				'is_donor' => ($data['donor']['type'] != 'Individual')
			),
			'Name' => $data['donor']['company'],
			'Type' => $data['donor']['type']
		),
	);
	// If the donation is from an individual, but a company is provided, set the company
	// type to none.
	if ( !( $contact['org']['meta']['is_donor'] == 'Individual' ) && !empty( $contact['org']['Name'] ) )
		$contact['org']['Type'] = 'Unknown';	 
	
	$payment = array('Payment_Method' => 'Credit Card');
	
	if ( $data['donation']['recurring'] ) {
		$card_exp_date = '20' . substr($data['card']['exp'], 2, 2) . '-' . substr($data['card']['exp'], 0, 2) . '-23';
		$card_exp_date = date('Y-m-t', strtotime($card_exp_date));
		$donation = array(
			'meta'								=> array(
				'recurring'	=> true,
			),
			'Campaign'							=> $meta_data['sf_campaign'],
			'Amount'							=> $data['donation']['amount'],
			'Schedule_Type'						=> 'Multiply By',
			'Initial_Payment_Method'			=> 'Credit Card',
			'Initial_Payment_Method_Expiration' => $card_exp_date,
			'Subscription_Id'					=> $data['donation']['subscription_id'],
			'Description'						=> $data['donor']['notes'],
			'Donation_Category'					=> $meta_data['sf_category'],
			'Date_Established'					=> $data['donation']['start_date'],
			'Installment_Period'				=> $data['donation']['period'],
			'Installments'						=> $data['donation']['installments'],
			'Open_Ended_Status'					=> 'None',
		);
		
		$donation_exp_task_due_date = recurrence_end_date($donation['Date_Established'], $donation['Installment_Period'], $donation['Installments']);
		$donation_exp_task_due_date = date('Y-m-d', strtotime('-1 month', strtotime($donation_exp_task_due_date)));
		$donation_exp_task = array(
			'ActivityDate'						=> $donation_exp_task_due_date,
			'Description'						=> $meta_data['sf_donation_exp_task_desc'],
			'Owner'								=> $meta_data['sf_task_user'],
			'ReminderDateTime'					=> $donation_exp_task_due_date . 'T09:00:00' . timezone_offset_str(),
			'Subject'							=> 'Call',
			'Status'							=> 'Not Started',
		);
		
		if ( ( $card_exp_date = strtotime( $card_exp_date ) ) < strtotime( $donation_exp_task_due_date ) ) {
			$card_exp_task_due_date = date('Y-m-d', strtotime("-1 month", $card_exp_date));
			$card_exp_task = array(
				'ActivityDate'						=> $card_exp_task_due_date,
				'Description'						=> $meta_data['sf_card_exp_task_desc'],
				'Owner'								=> $meta_data['sf_task_user'],
				'ReminderDateTime'					=> $card_exp_task_due_date . 'T09:00:00' . timezone_offset_str(),
				'Status'							=> 'Not Started',
				'Subject'							=> 'Call'
			);
		}
	} else {
		$donation = array(
			'CloseDate'							=> date('Y-m-d'),
			'Amount'							=> $data['donation']['amount'],
			'Donation_Category' 				=> $meta_data['sf_category'],
			'Description'						=> $data['donor']['notes'],
			'StageName'							=> 'Posted',
			'Campaign'							=> $meta_data['sf_campaign'],
			'RecordType'						=> 'Donation',
		);
		$payment['Check_Reference_Number']		= $data['donation']['transaction_id'];
	}
	
	// Set the owner if provided
	if ( !empty( $meta_data['sf_record_owner'] ) ) {
		$contact['Owner'] = $meta_data['sf_record_owner'];
		$contact['org']['Owner'] = $meta_data['sf_record_owner'];
		// Payment?
		$donation['Owner'] = $meta_data['sf_record_owner'];
	}

	// Initialize Salesforce interface (ESSENTIAL)
	$sf_interface = new CwwSalesforceInterface($sf_info);

	// Save the contact (ESSENTIAL)
	$sf_errors = $sf_interface->get_errors();
	if ( $sf_interface && empty( $sf_errors ) )
		$contact_id	= $sf_interface->upsert_contact($contact, "Name_and_Email_Ext_Id__c" );
	else
		$contact_id = false;
	
	// Add Organization and affiliation between Organizatin and Contact if necessar
	if ( $contact_id && !empty( $contact['org']['Name'] ) && ( $org_id = $sf_interface->get_org_id( $contact['org'], true ) ) )
		$sf_interface->create_affiliation(array('Contact' => $contact_id, 'Organization' => $org_id));
	
	// Save the donation (ESSENTIAL)
	if ( $contact_id )
		$donation['Id'] = $sf_interface->create_donation( $donation, $contact );
		
	// Add tasks and custom fields to single donations associated with recurring donation
	// if necessary.
	if ( !empty( $donation['Id'] ) ) {
		if ( $data['donation']['recurring'] ) {
			$sf_interface->create_donation_task( $donation_exp_task, $donation['Id'] );
			if ( !empty( $card_exp_task ) )
				$sf_interface->create_donation_task( $card_exp_task, $donation['Id'] );
			$sf_interface->update_recurring_donation_donations($donation, $payment);
		} else {
			$sf_interface->update_donation_payment($payment, $donation['Id']);
		}
	}
	
	$sf_errors = $sf_interface->get_errors();
	$sf_responses = $sf_interface->get_responses();
	if ( empty( $sf_errors ) ) {
		return true;
	} else {
		foreach( $sf_errors as $error ) {
			error_log(var_export($error['backtrace'], true));
			error_log($error['error']->getMessage());
		}
		// error_log(print_r($sf_interface->get_responses(), true));
		return false;
	}
}



/**
 * Returns true if the settings to enable Salesforce have been set.
 *
 * @return bool
 *
 */
function cww_df_salesforce_is_enabled() {
	$options = cww_df_options_page_fields();
	$required = array(
		'cww_df_salesforce_setting_username',
		'cww_df_salesforce_setting_password',
		'cww_df_salesforce_setting_security_token'
	);
	foreach ( $required as $id ) {
		if ( empty( $options[$id] ) || ! cww_df_option_is_set( $options[$id] ) )
			return false;
	}
	return true;
}

add_filter('cww_df_options_page_sections', 'cww_df_add_salesforce_options_page_section', 1);

/**
 * Add the Salesforce setting section to the donate form options page.
 *
 * @param $sections array
 *
 */
function cww_df_add_salesforce_options_page_section( $sections ) {
	$sections['cww_df_salesforce_setting_section'] = __('Salesforce', 'cww') ;
	return $sections;
}

add_filter('cww_df_options_page_fields', 'cww_df_add_salesforce_options_page_fields', 1);
/**
 * Add the Salesforce setting fields to the donate form options page.
 *
 * @param $options array
 *
 */
function cww_df_add_salesforce_options_page_fields( $options )
{
	$options['cww_df_salesforce_setting_username'] = array(
		'section' 	=> 'cww_df_salesforce_setting_section',
		'id'		=> 'cww_df_salesforce_setting_username',
		'title'		=> __('User name', 'cww'),
		'desc'		=> __('The Salesforce user name to use when adding donation records to Salesforce.', 'cww'),
		'type'		=> 'text',
		'std'		=> __('username', 'cww')
	);
	
	$options['cww_df_salesforce_setting_password'] = array(
		'section' 	=> 'cww_df_salesforce_setting_section',
		'id'		=> 'cww_df_salesforce_setting_password',
		'title'		=> __('Password', 'cww'),
		'desc'		=> __('The password for the Salesforce user.', 'cww'),
		'type'		=> 'password',
		'std'		=> __('password', 'cww')
	);
	
	$options['cww_df_salesforce_setting_security_token'] = array(
		'section' 	=> 'cww_df_salesforce_setting_section',
		'id'		=> 'cww_df_salesforce_setting_security_token',
		'title'		=> __('Security token', 'cww'),
		'desc'		=> __('The security token for the Salesforce user.', 'cww'),
		'type'		=> 'password',
		'std'		=> __('Security token', 'cww')
	);
	
	$options['cww_df_salesforce_setting_sandbox'] = array(
		'section' 	=> 'cww_df_salesforce_setting_section',
		'id'		=> 'cww_df_salesforce_setting_sandbox',
		'title'		=> __('Sandbox', 'cww'),
		'desc'		=> __('The salesforce sandbox account to use when processing donations.  CAUTION: Leave this blank on production sites.', 'cww'),
		'type'		=> 'text',
		'std'		=> __('', 'cww')
	);
	return $options;
}

add_filter('cww_df_meta_boxes', 'cww_df_add_salesforce_meta_boxes', 1);
/**
 * Add the Salesforce meta boxes to the edit Donate Form page
 *
 * @param $meta_boxes
 *
 */
function cww_df_add_salesforce_meta_boxes( $meta_boxes ) 
{
	if ( !cww_df_salesforce_is_enabled() )
		return $meta_boxes;
		
	$meta_boxes['cww_df_settings']['cww_df_sf_update'] = array(
		'handle'	=> 'cww_df_sf_update',
		'title'		=> __('Add donation to Salesforce'),
		'args'		=> array(
			'type'		=> 'checkbox',
			'desc'		=> __("Choose whether or not to update the Salesforce database with the user and donation data upon completion of this form.", 'cww'),
			'default'	=> '1',
		)
	);
	
	$meta_boxes['cww_df_settings']['cww_df_sf_record_owner'] = array(
		'handle'	=> 'cww_df_sf_record_owner',
		'title'		=> __('Salesforce record owner'),
		'args'		=> array(
			'type'		=> 'text',
			'desc'		=> __("The full name of the user (e.g. Jane Doe) to use as the owner of the contact, donation and affiliated records in Salesforce (copy and paste the name from Salesforce). This defaults to the Salesforce account used to create new donations (set in 'Settings >> Donate forms').", 'cww'),
			'default'	=> ''
		)
	);
	
	$meta_boxes['cww_df_settings']['cww_df_sf_campaign'] = array(
		'handle'	=> 'cww_df_sf_campaign',
		'title'		=> __('Salesforce campaign'),
		'args'		=> array(
			'type'		=> 'text',
			'desc'		=> __("The name of Salesforce campaign with which to associate donations made using this form (copy and paste from Salesforce).", 'cww'),
			'default'	=> '',
		)
	);
	
	$meta_boxes['cww_df_settings']['cww_df_sf_category'] = array(
		'handle'	=> 'cww_df_sf_category',
		'title'		=> __('Salesforce donation category'),
		'args'		=> array(
			'type'		=> 'text',
			'desc'		=> __("The value of the 'Donation Category' picklist to give to donation made using this form (copy and paste from Salesforce).", 'cww'),
			'default'	=> ''
		)
	);
	
	$meta_boxes['cww_df_settings']['cww_df_sf_task_user'] = array(
		'handle'	=> 'cww_df_sf_task_user',
		'title'		=> __('Salesforce task assignee'),
		'args'		=> array(
			'type'		=> 'text',
			'desc'		=> __("The full name of the user (e.g. Jane Doe) to whom tasks regarding recurring donations should be assigned (copy and paste from Salesforce). This defaults to the Salesforce account used to create new donations (set in 'Settings >> Donate forms').", 'cww'),
			'default'	=> ''
		)
	);
	
	$meta_boxes['cww_df_settings']['cww_df_sf_donation_exp_task_desc'] = array(
		'handle'	=> 'cww_df_sf_donation_exp_task_desc',
		'title'		=> __('Salesforce recurring donation expiration task description'),
		'args'		=> array(
			'type'		=> 'textarea',
			'desc'		=> __("The description of the task that will be created in Salesforce to remind the Salesforce task assignee that a recurring donation expires in one month.", 'cww'),
			'default'	=> 'A recurring donation expires in one month.'
		)
	);
	
	$meta_boxes['cww_df_settings']['cww_df_sf_card_exp_task_desc'] = array(
		'handle'	=> 'cww_df_sf_card_exp_task_desc',
		'title'		=> __('Salesforce recurring donation credit card expiration task description'),
		'args'		=> array(
			'type'		=> 'textarea',
			'desc'		=> __("The description of the task that will be created in Salesforce to remind the Salesforce task assignee that the credit card associated with a recurring donation expires at the end of the month.", 'cww'),
			'default'	=> 'The credit card associated with a recurring donation will expire at the end of this month.'
		)
	);

	return $meta_boxes;
}