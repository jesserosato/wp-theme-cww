<?php
// TO-DO: LOAD SELECT OPTIONS FOR SALESFORCE:
// - task_user
// - campaign
// - category

function cww_df_meta_boxes() {
	$meta_boxes = array();
	$meta_boxes['cww_df_settings'] = array();
	// Confirmation post ID
	$meta_boxes['cww_df_settings']['cww_df_conf_post_id'] = array(
		'handle'	=> 'cww_df_conf_post_id',
		'title' 	=> __('Success Post ID'),
		'args'		=> array(
			'type' 		=> 'text',
			'class' 	=> 'numeric',
			'desc' 		=> __("Please enter the ID of the Wordpress post to which you'd like users to be redirected upon successful completion of this form. The following tokens are available: %transaction_id, %donation_type, %name, %address, %company, %card_number, and %amount.", 'cww'),
			'default' 	=> 'e.g. 1293',
		)
	);
	// Confirmation email post ID
	$meta_boxes['cww_df_settings']['cww_df_conf_mail_post_id'] = array(
		'handle'	=> 'cww_df_conf_mail_post_id',
		'title' 	=> __('Success Email Post ID'),
		'args'		=> array(
			'type' 		=> 'text',
			'class' 	=> 'numeric',
			'desc' 		=> __("Please enter the ID of the Wordpress post you'd like to email to the user upon successful completion of this form.  Leave blank to not send a confirmation email. The following tokens are available: %transaction_id, %donation_type, %name, %address, %company, %card_number, and %amount.", 'cww'),
			'default' 	=> 'e.g. 1293',
		)
	);
	// Monthly donation duration
	$meta_boxes['cww_df_settings']['cww_df_monthly_duration'] = array(
		'handle'	=> 'cww_df_monthly_duration',
		'title' 	=> __('Monthly Donation Duration'),
		'args'		=> array(
			'type' 		=> 'text',
			'class' 	=> 'numeric',
			'desc' 		=> __("Number of months to repeat monthly (and business partner) donations.", 'cww'),
			'default' 	=> '12',
		)
	);
	// Annual donation duration
	$meta_boxes['cww_df_settings']['cww_df_annual_duration'] = array(
		'handle'	=> 'cww_df_annual_duration',
		'title' 	=> __('Annual Donation Duration'),
		'args'		=> array(
			'type' 		=> 'text',
			'class' 	=> 'numeric',
			'desc' 		=> __("Number of years to repeat annual donations.", 'cww'),
			'default' 	=> '5',
		)
	);
	
	return apply_filters('cww_df_meta_boxes', $meta_boxes);
}