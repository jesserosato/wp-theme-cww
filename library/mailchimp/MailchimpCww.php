<?php
/*****************************************************************************
 * Functions for syncing new donors with Mailchimp subscriber list.
 * Author: Jesse Rosato
 * Date:   6-18-12
 *
 * Version: 0.1
 *
 * Uses MCAPI.class.php
 *
 * Probably don't mess with this if you're not sure what you're doing :)
 *
 ******************************************************************************/
require_once 'lib/MCAPI.class.php';

function syncMailchimpContact($data, $token, $list) {
	$api = new MCAPI($token);
	$merge_vars['FNAME'] = $data['first_name'];
	$merge_vars['LNAME'] = $data['last_name'];
	$retval = $api->listSubscribe($list, $data['email'], $merge_vars, 'html', FALSE);
	/* DEBUG
	if ($api->errorCode){
		echo "Unable to load listSubscribe()!\n";
		echo "\tCode=".$api->errorCode."\n";
		echo "\tMsg=".$api->errorMessage."\n";
	} else {
		echo "Subscribed - look for the confirmation email!\n";
	}
	*/
}