<?php
require_once('SalesforceSOAPAPIInterface.class.php');

class CwwSalesforceInterface extends SalesforceSOAPAPIInterface
{
	public function __construct($sf_info)
	{
		parent::__construct($sf_info);
	}
	
	/**
	 * Prepare a 'Contact' object from the user supplied array
	 *
	 * @return stdClass
	 *
	 */
	public function prep_contact_obj( $contact, $ext_id_field )
	{
		$contact['meta']['type'] = 'Contact';
		
		$contact['org']['ignore'] = true;
		
		// Set the external id if it is not already set.
		if ( empty( $contact[$ext_id_field] ) ) {
			$contact[$ext_id_field] = array(
				'callback' => array('generate_contact_ext_id'),
				'args' => array($contact),
			);
		}
		
		// TO-DO: $this->validate_contact( $obj ); 
				
		return $this->prep_sf_obj( $contact, true );
	}
	
	/**
	 * Prepare an 'Organization' object from the user supplied array
	 *
	 * @param $org array
	 *
	 * @return stdClass
	 *
	 */
	public function prep_org_obj( array $org )
	{
		$org['meta']['type'] = 'Account';
		// TO-DO: $this->validate_org( $org );
		
		return $this->prep_sf_obj($org, true);
	}
	
	/**
	 * Prepare an Affiliation object from the user supplied array
	 *
	 * @param $affiliation array
	 *
	 * @return string or false
	 *
	 */
	public function prep_affiliation_obj( array $affiliation )
	{
		$meta = array(
			'type'		=> 'Affiliation',
			'prefix'	=> 'npe5',
			'custom'	=> true
		);
		$affiliation['meta'] = empty($affiliation['meta']) ? $meta : array_merge($affiliation['meta'], $meta);
		
		if ( empty( $affiliation['Contact'] ) || empty( $affiliation['Organization'] ) )
			return false;
			
		$affiliation['Contact'] = array(
			'custom'	=> true,
			'prefix'	=> true,
			'value'		=> $affiliation['Contact']
		);

		$affiliation['Organization'] = array(
			'custom'	=> true,
			'prefix'	=> true,
			'value'		=> $affiliation['Organization']
		);
		
		return $this->prep_sf_obj($affiliation);
	}
	
	/**
	 * Prepare an 'Opportunity' object from the user supplied array
	 *
	 * @param $donation array
	 * @param $account_id string
	 *
	 * @return stdClass
	 */
	public function prep_donation_obj( $donation, $contact )
	{
		// TO-DO: $this->validate_donation( $donation, $contact );
	
		// Donation name, category and allocation are the same for donation and recurring donation
		$donation['Name'] = array(
			'callback'	=> array('generate_donation_name'),
			'args'		=> array($contact),
			'required'	=> true
		);
		$donation['Donation_Category'] = array(
			'custom'	=> true,
			'value'		=> $donation['Donation_Category']
		);
		if ( !empty( $donation['Allocation'] ) ) {
			$donation['Allocation'] = array(
				'custom'	=> true,
				'value'		=> $donation['Allocation']
			);
		}

		// If this is an individual donation, relate it to the individual's account,
		// whose name is the contacts first and last name with a space between.
		if ( empty( $contact['org']['meta']['is_donor'] ) )
			$contact['org']['Name'] = $contact['FirstName'] . ' ' . $contact['LastName'];
		
		
		// Prep recurring donation
		if ( !( empty( $donation['meta']['recurring'] ) ) )
			return $this->prep_recurring_donation_obj($donation, $contact);
			
		// META
		$donation['meta']['type'] = 'Opportunity';
		
		// ID DATA
		// - AccountId
		$donation['AccountId'] = array(
			'callback'	=> array('get_org_id'),
			'args'		=> array($contact['org'], false),
			'required'	=> true
		);

		// - RecordTypeId
		if ( empty( $donation['RecordType'] ) )
			return false;
		$donation['RecordTypeId'] = array(
			'callback'	=> array('get_opportunity_record_type_id'),
			'args'		=> array($donation['RecordType']),
			'required'	=> true
		);
		unset($donation['RecordType']);
		
		// - CampaignId
		if ( !empty( $donation['Campaign'] ) ) {
			$donation['CampaignId'] = array(
				'callback'	=> array('get_campaign_id'),
				'args'		=> array($donation['Campaign']),
			);
		}
		unset($donation['Campaign']);
		
		return $this->prep_sf_obj($donation);
	}
	
	/**
	 * Prepare a 'Recurring Donation' object from the user supplied array
	 *
	 * @param $donation array
	 * @param $account_id string
	 *
	 * @return stdClass
	 */
	public function prep_recurring_donation_obj( $donation, $contact )
	{
		// TO-DO: $this->validate_recurring_donation( $donation, $contact );
		
		// META
		$meta = array(
			'type'		=> 'Recurring_Donation',
			'custom'	=> true,
			'prefix'	=> 'npe03'
		);
		$donation['meta'] = array_merge($donation['meta'], $meta);
		// USER PROVIDED DATA
		// - All recurring donations are associated with an organization.
		//   If the donation is from an individual, it's associated with
		//   their Individual type organization.
		$donation['Organization'] = array(
			'custom'	=> true,
			'prefix'	=> true,
			'callback'	=> array('get_org_id'),
			'args'		=> array($contact['org'], false),
		);
		
		$prefixed = array(
			'Date_Established',
			'Amount',
			'Installment_Period',
			'Installments',
			'Open_Ended_Status',
			'Schedule_Type'
		);
		
		foreach( $prefixed as $key ) {
			if ( !empty( $donation[$key] ) ) {
				$donation[$key] = array(
					'custom'	=> true,
					'prefix'	=> true,
					'value'		=> $donation[$key]
				);
			}
		}
		
		if ( !empty( $donation['Initial_Payment_Method'] ) ) {
			$donation['Initial_Payment_Method'] = array(
				'custom'	=> true,
				'prefix'	=> false,
				'value'		=> $donation['Initial_Payment_Method']
			);
		}
		if ( !empty( $donation['Initial_Payment_Method_Expiration'] ) ) {
			$donation['Initial_Payment_Method_Expiration'] = array(
				'custom'	=> true,
				'prefix'	=> false,
				'value'		=> $donation['Initial_Payment_Method_Expiration']
			);
		}
		if ( !empty( $donation['Campaign'] ) ) {
			$donation['Recurring_Donation_Campaign'] = array(
				'custom'	=> true,
				'prefix'	=> true,
				'callback'	=> array('get_campaign_id'),
				'args'		=> array($donation['Campaign']),
			);
		}
		unset($donation['Campaign']);
		if ( !empty( $donation['Subscription_Id'] ) ) {
			$donation['Subscription_Id'] = array(
				'custom'	=> true,
				'value'		=> $donation['Subscription_Id']
			);
		}
		if ( !empty( $donation['Description'] ) ) {
			$donation['Description'] = array(
				'custom'	=> true,
				'value'		=> $donation['Description']
			);
		}
	
		
		return $this->prep_sf_obj($donation);
	}
	
	/**
	 * Prepare a 'Payment' custom object
	 * 
	 * @param $payment array
	 * @param $donation_id string
	 *
	 * @return stdClass or false
	 *
	 */
	public function prep_payment_obj( array $payment, $donation_id )
	{
		// The opportunity array must include payment info, and the opportunity id must be set.
		if ( empty( $donation_id ) )
			return false;
		
		// META
		$meta = array(
			'type' => 'OppPayment',
			'prefix' => 'npe01',
			'custom' => true
		);
		$payment['meta'] = empty($payment['meta']) ? $meta : array_merge($payment['meta'], $meta);
		
		// DATA
		if ( !empty( $payment['Check_Reference_Number'] ) ) {
			$payment['Check_Reference_Number'] = array(
				'custom'	=> true,
				'prefix'	=> true,
				'value'		=> $payment['Check_Reference_Number']
			);
		}
		if ( !empty( $payment['Payment_Method'] ) ) {
			$payment['Payment_Method'] = array(
				'custom'	=> true,
				'prefix'	=> true,
				'value'		=> $payment['Payment_Method']
			);
		}
		
		$obj = $this->prep_sf_obj($payment);
		

		$obj->Id = $this->get_opportunity_payment_id($donation_id);
		
		return $obj;
	}
	
	/**
	 * Prepare a 'Task' object
	 * 
	 * @param $task array
	 * @param $donation_id string
	 *
	 * @return stdClass or false
	 *
	 */
	public function prep_task_obj( array $task, $donation_id )
	{
		// The opportunity id must be set.
		if ( empty( $donation_id ) )
			return false;
		
		$task['meta']['type'] = 'Task';
			
		$task['WhatId'] = array(
			'required'		=> true,
			'callback'		=> array('get_last_donation_of_recurring_donation_id'),
			'args'			=> array($donation_id)
		);
		
		if ( !empty( $task['Owner'] ) ) {
			$task['OwnerId'] = array(
				'callback'	=> array('get_user_id'),
				'args'		=> array($task['Owner'])
			);
			
		}
		unset($task['Owner']);
		
		return $this->prep_sf_obj( $task );
	}
		
	/**
	 * Create a "contact external id" string of the form "John Doe johndoe@example.com".
	 *
	 * @param $contact array
	 *
	 * @return string
	 *
	 */
	function generate_contact_ext_id( $contact )
	{
		return $contact['FirstName'] . ' ' . $contact['LastName'] . ' ' . $contact['Email'];
	}
	
	/**
	 * Returns a string for the donation name in the format: "John Doe 2013-01-15 14:35"
	 * @param $contact array
	 * @return string
	 *
	 */
	function generate_donation_name( $contact )
	{
	 	if ( empty( $contact['org']['meta']['is_donor'] ) ) 
			return $contact['FirstName'] . ' ' . $contact['LastName'] . ' ' . date('Y-m-d H:i:s');
		
		return $contact['org']['Name'] . ' ' . date('Y-m-d H:i:s');
	}
	
	/**
	 *
	 * Lookup an Organization's Id
	 *
	 * @param $org array
	 * @param $create bool Whether to create a new org if no matches are found.
	 *
	 * @return string or false on failure
	 *
	 */
	public function get_org_id( $org, $create = true )
	{	
		error_log(print_r($org,true));
		// Make sure an organization was specified at all.	
		if ( empty( $org['Name'] ) )
			return false;
		// Potentially user data, escape single quotes
		$org_name = addslashes($org['Name']);
		$query = "SELECT Id FROM Account WHERE Name = '" . $org_name . "'";
		
		if ( !( $response = $this->query($query) ) )
			return false;
			
		// If more than one org is found, avoid conflict.
		if ( $response->size > 1 )
			return false;
		// If no orgs are found, check create 
		if ( !$response->size ) {
			error_log("NO RESPONSE SIZE");
			return $create ? $this->create_org( $org ) : false;
		}
		error_log( "ONE ORG FOUND" );
		// One organization was found, return its id.
		return $response->current()->Id;
	}
	
	/**
	 * Look up the RecordType Id based on the 'RecordType' field of the opportunity array.
	 *
	 * @param $record_type string
	 *
	 * @return string or false
	 */
	public function get_opportunity_record_type_id( $record_type )
	{
		// Make sure a RecordType name has been supplied.
		if ( empty ( $record_type ) )
			return false;
		// Potentially user data, escape single quotes
		$record_type = addslashes($record_type);
		$query = "SELECT Id FROM RecordType WHERE Name = '" . $record_type . "'";
		
		$response = $this->query($query);
		return ($response && $response->size == 1) ? $response->current()->Id : false;
	}
	
	/**
	 * Look up a Campaign Id using the campaign name.
	 *
	 * @param $campaign_name string
	 *
	 * @return string or false
	 *
	 */
	public function get_campaign_id( $campaign_name )
	{
		if ( empty( $campaign_name ) )
			return false;
		// Potentially user data, escape single quotes
		$campaign_name = addslashes($campaign_name);
		$query = "SELECT Id FROM Campaign WHERE Name = '" . $campaign_name . "'";
		
		$response = $this->query($query);
		return ($response && $response->size == 1) ? $response->current()->Id : false;
	}
	
	/**
	 * Look up the Payment Id based on the Opportunity Id.
	 *
	 * @param $opportunity_id string
	 *
	 * @return string or false
	 *
	 */
	public function get_opportunity_payment_id( $opportunity_id )
	{
		if ( empty( $opportunity_id ) )
			return false;
		
		$query = "SELECT Id FROM npe01__OppPayment__c WHERE npe01__Opportunity__c = '" . $opportunity_id . "'";
		
		$response = $this->query( $query );
		return ($response && $response->size == 1) ? $response->current()->Id : false;
	}
	
	/**
	 * Look up the last Donation Id based with the .
	 *
	 * @param $opportunity_id string
	 *
	 * @return string or false
	 *
	 */
	public function get_last_donation_of_recurring_donation_id( $recurring_id )
	{
		if ( empty( $recurring_id ) )
			return false;
		
		$query = "SELECT Id FROM Opportunity WHERE npe03__Recurring_Donation__c = '" . $recurring_id . "'";
		
		if ( ( $response = $this->query( $query ) ) && $response->size >= 1 ) {
			// Get the last record
			while ( $response->valid() ) {
				$last = $response->current();
				$response->next();
			}
			return $last->Id;
		}
		
		return false;
	}
	
	/**
	 * Look up a User Id using the user name.
	 *
	 * @param $user_name string
	 *
	 * @return string or false
	 *
	 */
	public function get_user_id( $user_name )
	{
		$user_name = addslashes($user_name);
		$query = "SELECT Id FROM User WHERE Name = '" . $user_name . "'";
		$response = $this->query($query);
		return ($response && $response->size == 1) ? $response->current()->Id : false;
	}
	
	/**
	 * Upserts a 'Contact' record.  Returns false if the external id field
	 * has not been set in the Contact object.
	 *
	 * @param $contact array
	 * @param $ext_id_field string (optional)
	 *
	 * @return string or false
	 *
	 */
	public function upsert_contact( array $contact, $ext_id_field )
	{
		// The external id field for the 'Contact' type must be provided.
		if ( empty( $ext_id_field ) )
			return false;
		
		if ( !( $contact_obj = $this->prep_contact_obj( $contact, $ext_id_field ) ) )
			return false;

		$response = $this->upsert( $ext_id_field, array( $contact_obj ) );
		return ($response && $response[0]->success) ? $response[0]->id : false;
	}
	
	/**
	 * Creates a new 'Organization' record. 
	 *
	 * @param $org array
	 *
	 * @return string or false
	 *
	 */
	public function create_org( array $org )
	{
		if ( !( $obj = $this->prep_org_obj( $org ) ) )
			return false;
		
		$response = $this->create( array( $obj ) );
		return ($response && $response[0]->success) ? $response[0]->id : false;
	}
	
	/**
	 * Creates a new 'Affiliation' custom object record. 
	 *
	 * @param $affiliation array
	 *
	 * @return string or false
	 *
	 */
	public function create_affiliation( array $affiliation )
	{
		$query = "SELECT Id FROM npe5__Affiliation__c WHERE npe5__Contact__c = '" . $affiliation['Contact'] . "' AND npe5__Organization__c = '" . $affiliation['Organization'] . "'";
		
		// If the affiliation already exists, return its Id
		if ( ( $response = $this->query( $query ) ) && ( $response->size > 0 ) )
			return $response->current()->Id;
	
		if ( !( $obj = $this->prep_affiliation_obj( $affiliation ) ) )
			return false;
		
		$response = $this->create( array( $obj ) );
		return ($response && $response[0]->success) ? $response[0]->id : false;
	}
	
	/**
	 * Create a 'Donation' record.
	 *
	 * @param $donation array
	 * @param $contact array
	 *
	 * @return string
	 *
	 */
	public function create_donation( array $donation, array $contact )
	{
		if ( !( $donation_obj = $this->prep_donation_obj( $donation, $contact ) ) )
			return false;
		 	
		$response = $this->create(array($donation_obj));
		return ($response && $response[0]->success) ? $response[0]->id : false;
	}
	 
	/**
	 * Add details to payment related to donation that are not automatically added
	 * when creating a new donation. 
	 *
	 * @return string or false
	 *
	 */
	public function update_donation_payment( array $payment, $donation_id )
	{
		// We need to make sure we have the payment Id to update it.
		if ( !( $payment_obj = $this->prep_payment_obj( $payment, $donation_id ) ) || !( $payment_obj->Id ) )
			return false;
			
		$response = $this->update(array($payment_obj));
		return ($response && $response[0]->success) ? $response[0]->id : false;
	}
	
	/**
	 * Update the single donations (and payments) affiliated with a recurring donation.
	 *
	 * @param $recurring array
	 * @param $payment array
	 *
	 * @return array - an array describing the outcomes.
	 *         array(
	 *             [{id}] => array(
	 *                 [donation] => {response or false},
	 *                 [payment]  => {response or false}
	 *             )
	 *         )
	 *
	 */
	public function update_recurring_donation_donations( array $recurring, array $payment )
	{
		// The recurring donation must have already been created.
		if ( empty( $recurring['Id'] ) )
			return false;
		
		$query = "SELECT Id FROM Opportunity WHERE npe03__Recurring_Donation__c = '" . $recurring['Id'] . "'";
		$response = $this->query($query);
		$results = array();
		// If no records found, return an empty array,
		if ( !$response || !$response->size )
			return $results;
		
		foreach ( $response->records as $record ) {
			$cur_id = $record->Id[0];
			$donation = array(
				'meta' => array('type' => 'Opportunity'),
				'Donation_Category' => array(
					'custom' => true,
					'value' => $recurring['Donation_Category'],
				),
			);
			if ( !empty( $recurring['Allocation'] ) ) {
				$donation['Allocation'] = array(
					'custom' => true,
					'value' => $recurring['Allocation'],
				);
			}
			if ( ( $obj = $this->prep_sf_obj( $donation ) ) && ( $obj->Id = $cur_id ) ) {
				if ( $response = $this->update( array( $obj ) ) )
					$results[$cur_id]['donation'] = $response;
				else
					$results[$cur_id]['donation'] = false;
			} else {
				$results[$cur_id]['donation'] = false;
			}
			
			if ( $response = $this->update_donation_payment( $payment, $cur_id ) )
				$results[$cur_id]['payment'] = $response;
			else
				$results[$cur_id]['payment'] = false;
		}
		
		return $results;
	}  
	
	/**
	 * Create a task to remind the given task assignee (name) to follow up on the given donation.
	 *
	 * @param $task array
	 * @param $donation_id string
	 *
	 * @return string or false
	 *
	 */
	public function create_donation_task( array $task, $donation_id )
	{
		if ( !( $task_obj = $this->prep_task_obj( $task, $donation_id ) ) )
			return false;
		
		$response = $this->create(array($task_obj));
		return ($response && $response[0]->success) ? $response[0]->id : false;
	}
	
} // end class