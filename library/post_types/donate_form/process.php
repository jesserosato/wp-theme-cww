<?php
/************************************************************************************ 
/* Procedural code for processing donate form entries.
/* By Jesse Rosato, 2012 - jesse.rosato@gmail.com
/************************************************************************************/
// Load Donate Form processor class
require_once 'CwwDonateFormProcessor.class.php';
require_once 'text/post_process_error.inc'; 

if ( !empty( $_POST['df_post_id'] ) ) {
	do_action('cww_df_pre_process');
	// Process form
	$df_processor = new CwwDonateFormProcessor();
	if ( $df_processor->validate_data() && $df_processor->process_donation() ) {
		// Set the error message strings
		$df_processor->error_msgs = $df_post_process_error_msgs;
		// Deal with any third party services
		$df_data		= $df_processor->get_organized_data();
		$df_meta_data	= $df_processor->get_meta_data();
		$df_settings	= $df_processor->get_settings();
		do_action('cww_df_post_process', $df_data, $df_meta_data, $df_settings);
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