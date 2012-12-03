<?php
/************************************************************************************ 
/* Procedural code for processing donate form entries.
/* By Jesse Rosato, 2012 - jesse.rosato@gmail.com
/************************************************************************************/
// Load Donate Form processor class
require_once 'CwwDonateFormProcessor.class.php';
require_once 'text/post_process_error.inc'; 
// For Authorize.net - use test mode if WP_DEBUG is on
defined('WP_DEBUG') && WP_DEBUG ? define('AUTHORIZENET_SANDBOX', true) : define('AUTHORIZENET_SANDBOX', false);

if ( !empty( $_POST['df_post_id'] ) ) {
	// Process form
	$df_processor = new CwwDonateFormProcessor();
	$is_priv_form = $df_processor->get_meta_data('is_private_form');
	
	$df_ok = $is_priv_form ? $df_processor->validate_data() : $df_processor->validate_data() && $df_processor->process_donation();
	
	if ( $df_ok ) {
		$df_processor->error_msgs = $df_post_process_error_msgs;
		$df_processor->submit_data_to_highrise();
		$df_processor->submit_data_to_mailchimp();
		$df_processor->send_confirmation_mail();
		$df_processor->redirect();
	} else {
		global $df_errors;
		global $df_clean;
		$df_errors = $df_processor->get_errors();
		$df_clean  = $df_processor->get_sanitized_data();
		if (empty($df_errors['form']))
			$df_errors['form'] = count($df_errors) > 1 ? 'multiple' : 'single';
	}
}