<?php
function cww_df_meta_boxes($mailchimp = false, $highrise = false) {
	$meta_boxes = array();
	$meta_boxes['cww_df_settings'] = array();
	// Back end
	$meta_boxes['cww_df_settings']['cww_df_private_form'] = array(
		'handle'	=> 'cww_df_private_form',
		'title' 	=> __('Private form'),
		'args'		=> array(
			'type' 		=> 'checkbox',
			'desc' 		=> __("Check this box if this form should be used only by administrators to enter cash or check donations."),
			'default' 	=> '',
		)
	);
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
			'desc' 		=> __("Leave blank to use the success post ID above. Please enter the ID of the Wordpress post you'd like to email to the user upon successful completion of this form.  The following tokens are available: %transaction_id, %donation_type, %name, %address, %company, %card_number, and %amount.", 'cww'),
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
	
	if ( $mailchimp ) {
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
	
	if ( $highrise ) {
		$meta_boxes['cww_df_settings']['cww_df_update_hr'] = array(
			'handle'	=> 'cww_df_update_hr',
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