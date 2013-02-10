<?php

if(!empty($_GET['df'])) {
	$df_passed_data = get_transient($_GET['df']);
	// Check for expired transient.
	if ($df_passed_data) {
		$df_passed_data = unserialize($df_passed_data);
		$post = get_post($df_passed_data['conf_post_id']);
		$df_content = $df_passed_data['conf_content'];
	} else if( !empty( $_GET['amount'] ) || !empty( $_GET['type'] ) ) {
		// Allow values for type and amount to be passed via query string
		require_once('library/utilities/FormProcessor.class.php');
		$_GET['type'] = strtolower($_GET['type']);
		$df_query_processor = new FormProcessor('GET');
		$df_clean_query_data = $df_query_processor->get_sanitized_data();
		if( in_array( $df_clean_query_data['type'], array("onetime", "monthly", "annual", "business") ) ) {
			$df_clean['df_type'] = $df_clean_query_data['type'];
			if ( !empty( $df_clean_query_data['amount'] )
				 && $df_query_processor->validate_currency( $df_clean_query_data['amount'] ) )
				$df_clean['df_amount_' . $df_clean['df_type']] = $df_clean_query_data['amount'];
		}
	}
}