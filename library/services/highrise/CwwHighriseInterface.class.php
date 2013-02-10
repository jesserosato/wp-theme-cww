<?php
/****************************************************************************************
 * Simplified Wordpress/Highrise interface designed for Courage Worldwide (CWW).
 * Author: Jesse Rosato
 * Date:   6-15-12
 *
 * Version: 0.1
 *
 * Uses AppSaloon Highrise PHP API wrapper.
 *  - https://github.com/AppSaloon/Highrise-PHP-Api
 *
 * Note: Probably don't mess with this if you're not sure what you're doing :)
 *
 /***************************************************************************************/
class CwwHighriseInterface {
	protected $_hr; // Highrise API object
	protected $_config; // Configuration array.
	
	public function __construct($config, $hr_account = false, $hr_token = false)
	{	
		// Load required files
		require_once('lib/HighriseAPI.class.php');
		// Set up Highrise credentials
		if (!$hr_account)
			$hr_account = HIGHRISE_ACCOUNT;
		if (!$hr_token)
			$hr_token = HIGHRISE_TOKEN;
		if (!$hr_account || !$hr_token)
			throw new Exception('This Highrise interface requires a Highrise account and token.');
			
		// Initialize API
		$this->_hr = new HighriseAPI();
		$this->_hr->debug = false;
		$this->_hr->setAccount($hr_account);
		$this->_hr->setToken($hr_token);
		
		if (is_array($config))
			$this->_config = $config;
		else
			throw new Exception('This Highrise interface requires a configuration array. (This is the Wordpress version.)');
	}
	
	/***************************************************************************************
	 * syncContact($data)
	 * Sync contact to Highrise, create new contact if unable to sync.
	 * params
	 * - $data	Associative array with some or all of the following keys (* = required):
	 * 		- first_name*
	 *		- last_name*
	 *		- email*
	 *		- company
	 *		- phone
	 *		- address
	 *		- city
	 *		- state
	 *		- zip
	 *		- country
	 *		- notes
	 * returns
	 * - The person object synced or created.
	/***************************************************************************************/
	public function syncContact($data) {
		// Move array into variables
		extract($data);
		
		// Create and populate a 'person' object.
		$email = empty($email) ? false : trim(strtolower($email));
		$person = $this->loadPerson($first_name, $last_name, $email );
		if ( empty($person) ) {
			$found = false;
			$person = new HighrisePerson($this->_hr);
			$person->setFirstName(trim($first_name));
			$person->setLastName(trim($last_name));
		} else {
			$found = true;
			$person->author_id = '';
		}
		
		// Check whether email address is unique
		if ( !empty( $email ) && ( !$found || $this->isNewEmailAddress( $email, $person ) ) )
			$person->addEmailAddress($email, 'Home');
		
		// Check whether optional user-entered fields exist.
		$company = !empty($company) ? trim($company) : false;
		if ($company)
			$person->setCompanyName($company);
			
		// Check whether address and phone number are unique.
		if ( !empty( $phone ) && ( !$found || $this->isNewPhoneNumber( $phone, $person ) ) )
			$person->addPhoneNumber($phone, 'Home');
		
		$has_address = !empty($address) && !empty($city) && !empty($state) && !empty($zip) && !empty($country);
		if ( $has_address && ( !$found || $this->isNewAddress( $address, $zip, $person ) ) )
			$person->addAddress($address, $city, $state, $zip, $country, 'Home');

		// Save person object (must be done before adding notes to person).
		try {
			$person->save();
		} catch( Exception $e ) {
			if ( defined(WP_DEBUG) && WP_DEBUG )
				error_log(__FILE__ . ": " . $e->getMessage());
		}
		
		// Add user comments as a note.
		$notes = !empty($notes) ? trim($notes) : false;
		if ($notes) {
			$note = "Note from " . $person->getFirstName() . " " . $person->getLastName() . ": " . $notes;
			$this->addNote($note, $person);
		}
		return $person;
	} // end syncContact
	
	/****************************************************************************************
	 * addTransaction($transaction, HighrisePerson($person)
	 * Add Highrise 'transaction', consisting of:
	 * - Deal: Deal describing the transaction.
	 * - Task: Sets a reminder Task one year from the date of the donation.
	 * - Note: Adds a note describing the transaction.
	 * params
	 * - transaction	Associative array describing an online transaction (* = required):
	 *		- id* (payment gateway transaction id)
	 *		- source* (e.g. http://transactionsite.com)
	 *		- pay_method* (e.g. VISA, MC, AMEX, etc...)
	 *		- account* (account the payment is going to, i.e. 'Auth.net')
	 *		- card_exp (card expiration date, for recurring donations task)
	 *		- products
	 *			- amount
	 *			- name
	 *			- type ('onetime', 'monthly', or 'annual')
	 *			- category ('rescue', 'restore', 'business' or unset)
	 *			- duration (in months for monthly, in years for annual, or unset for onetime)
	 *			- quantity
	 *			- start_date
	 * - person			HighrisePerson object.
	 * returns
	 * An array for each product containing deal, note and task objects.
	 * - index
	 * 		- deal
	 * 		- note
	 * 		- task
	/***************************************************************************************/
	public function addTransaction($transaction, HighrisePerson $person) {
		// Move transaction data into variables
		extract($transaction);
		// To store results
		$result = array();
			
		// Create deal, task and note for each product.
		$i = 0;
		foreach ( $products as $product ) {
			// - Deal
			// Not all transactions actually have an associated product
			$product_name = empty($product['name']) ? false : $product['name'];
			$product_quantity	= empty($product['quantity']) ? false : 'QTY: ' . $product['quantity'];
			// Strip query from URL
			$source = preg_replace('/[?].*/', '', $source);
			$deal_name_parts = array(
				'Donation form',
				$source, 
				$pay_method, 
				$account, 
				$id,
				$product_name,
				$product_quantity,
			);
			// Remove empty parts from deal name
			foreach ($deal_name_parts as $key => $val) {
				if (!$val)
					unset($deal_name_parts[$key]);
			}
			$deal_name = implode(' | ', $deal_name_parts);
			$result[$i]['deal'] = $this->addDeal($deal_name, $product, $person);
			$deal = $result[$i]['deal'];
			
			// - Tags
			if ( !empty( $tags ) && is_array( $tags ) ) {
				foreach ( $tags as $tag ) {
					$this->addTag($tag, $person->id, 'Person');
					$this->addTag($tag, $deal->id, 'Deal');
				}
			}
			
			// - Note 
			$name 				= $person->getFirstName() . ' ' . $person->getLastName();
			$product_type 		= ucfirst($product['type']) . ' Donation';
			$product_category 	= empty($product['category']) ? 'General donation' : $product['category'];
			$datetime			= date('n-d-Y H:i');
			if ( empty( $product['start_date'] ) ) {
				if ( $product['type'] == 'onetime' )
					$start_date = false;
				else
					$start_date = date('n-d-Y');
			} else {
				$start_date = date('m-d-Y', strtotime($product['start_date']));
			}
			$deal_link = '<a href="https://' . $this->_hr->account . '.highrisehq.com/deals/' . $deal->id . '">' . $deal->id . '</a>';
			$note_body_parts  = array(
				"Site" => $source,
				"Name" => $name,
				"Type" => $product_type,
				"Category" => $product_category,
				"Date and Time" => $datetime,
				"Start date" => $start_date,
				"Item" => $product_name,
				"Quantity" => $product_quantity,
				"Deal" => $deal_link,
			);
			$note_body = array("Donation form");
			foreach ( $note_body_parts as $key => $val ) {
				if ( $val )
					$note_body[] = "$key: $val";
			}
			$result[$i]['note'] = $this->addNote(implode(' | ', $note_body), $person);
			
			// - Task
			$task_delay	= $this->_config['task_delay'];
			if ($product['type'] == 'monthly' || $product['type'] == 'annual') {
				// Set reminder task about deal expiration.
				$start_date = empty($product['start_date']) ? date('Y-m-d') : $product['start_date'];
				if ( $product['type'] == 'monthly' )
					$exp_timestamp = strtotime($start_date . "+" . $product['duration'] . " months");
				if ( $product['type'] == 'annual' )
					$exp_timestamp = strtotime($start_date . "+" . $product['duration'] . " years");	
				$exp_date = date('n/d/Y', $exp_timestamp);
				$due_date = date('Y-m-d', strtotime($start_date . "+" . $task_delay)) . 'T10:00:00-08:00';
				$task_body = 'Follow up on recurring donation, made ';
				$task_body .= $task_delay;
				$task_body .= " ago.  Donation expires on $exp_date.";
				$result[$i]['tasks'][] = $this->addTask($task_body, $due_date, $result[$i]['deal']);
				
				// Set reminder task if card expires before deal does.
				// - Use the 1st, because most cards expire on last day of expiration month.
				if ( !empty( $transaction['card_exp'] ) ) {
					$card_exp  = '20' . substr($transaction['card_exp'], 2, 2) . '/';
					$card_exp .= substr($transaction['card_exp'], 0, 2) . '/01';
					$card_exp_timestamp = strtotime($card_exp);
					if ($card_exp_timestamp < $exp_timestamp) {
						$task_body = 'The credit card used for recurring donation expires this month.';
						$due_date = date('Y-m-d', $card_exp_timestamp) . 'T10:00:00-08:00';
						$result[$i]['tasks'][] = $this->addTask($task_body, $due_date, $result[$i]['deal']);
					}
				}
			}
			
			// Increment counter
			$i++;
			
		} // end foreach($products as $product)
		
		// Return an array containing the created Highrise objects.
		return $result;
		
	} // end addTransaction
	
	/****************************************************************************************
	 * Search by email if possible, by full name otherwise.  If people are found, check first 
	 * name and last name.  If match, return Highrise Person Object, else return false.
	/****************************************************************************************/
	public function loadPerson($first_name, $last_name, $email) {
		if ( $email ) {
			$email = trim(strtolower($email));
			$people = $this->_hr->findPeopleByEmail($email);
		} 
		if ( !$email || empty( $people ) )
			$people = $this->_hr->findPeopleBySearchTerm($first_name . ' ' . $last_name);
			
		if ( empty( $people ) )
			return false;

		$first_name = trim(strtolower($first_name));
		$last_name = trim(strtolower($last_name));
		foreach ( $people as $person ) {
			$check_first = strtolower($person->getFirstName());
			$check_last = strtolower($person->getLastName());
			if ( $check_first == $first_name && $check_last == $last_name )
				return $person;
		}
		return false;
	}
	
	/****************************************************************************************
	 * Check street and zip against existing addresses
	 * Returns true if new address, false if existing.
	/****************************************************************************************/
	public function isNewEmailAddress($email, HighrisePerson $person) {
		$street		= strtolower($email);
		$addresses	= $person->getEmailAddresses();
		if ( empty( $addresses ) )
			return true;
		foreach( $addresses as $address ) {
			$address = strtolower($address->getAddress());
			if ( $address == $email )
				return false;
		}
		return true;
	}
	
	/****************************************************************************************
	 * Check street and zip against existing addresses
	 * Returns true if new address, false if existing.
	/****************************************************************************************/
	public function isNewAddress($street, $zip, HighrisePerson $person) {
		$street		= preg_replace('/[^a-zA-Z0-9 ]/', '', strtolower($street));
		$zip		= preg_replace('/[^0-9]/', '', strtolower($zip));
		$addresses	= $person->getAddresses();
		if ( !count($addresses) )
			return $true;
		foreach( $addresses as $address ) {
			$cur_street = preg_replace('/[^a-zA-Z0-9 ]/', '', strtolower($address->getStreet()));
			$cur_zip	= preg_replace('/[^0-9]/', '', strtolower($address->getZip()));
			if ( $cur_street == $street && $cur_zip == $zip )
				return false;
		}
		return true;
	}
	
	/****************************************************************************************
	 * Check phone number against existing phone numbers.
	 * Returns true if new number, false if existing.
	/****************************************************************************************/
	public function isNewPhoneNumber($phone_number, HighrisePerson $person) {
		$phones = $person->getPhoneNumbers();
		if ( !count( $phones ) )
			return true;
		foreach ( $phones as $phone ) {
			$cur_num = preg_replace('/[^0-9]/', '', $phone->getNumber());
			if ( $cur_num == $phone_number )
				return false;
		}
		return true;
	}
	
	/****************************************************************************************
	 * Add a new note
	/****************************************************************************************/
	public function addNote($note_body, HighrisePerson $person) {
		$note = new HighriseNote($this->_hr);
		$note->setBody($note_body);
		$person->addNote($note);
		$person->save();
		return $note;
	}
	
	/****************************************************************************************
	 * addHighriseDeal($transaction, HighrisePerson $person)
	 * Add Highrise deal
	 * params
	 * - deal_name	String containing the name for the Deal
	 * - product	Associative array describing an online transaction (* = required):
	 *		- amount*
	 *		- name (product name)*
	 *		- type ('onetime', 'monthly', or 'annual')
	 *		- category ('Rescue', 'Restore' or unset - for Highrise Deals category)
	 *		- duration (in months for monthly, in years for annual)
	 *		- quantity
	 *		- start_date
	 * - person			HighrisePerson object.
	 * returns
	 * An associative array
	 * - deals
	 * - notes
	 * - tasks
	/***************************************************************************************/
	public function addDeal($deal_name, $product, HighrisePerson $person) {
		$deal = new HighriseDeal($this->_hr);
		$deal->setName($deal_name);
		$category = isset($product['category']) ? $product['category'] : false;
		$deal->setCategoryId($this->_getDealCategoryId($category, $product['type']));
		$deal->setStatus('won');
		$deal->setPartyId($person->id);
		$deal->setPrice($product['amount']);
		$deal->setPriceType($this->_getPriceType($product['type']));
		$deal->setCurrency();
		//$deal->setGroupId(HR_GROUP_ID_ADMIN);
		$deal->setVisibleTo("NamedGroup");
		$deal->setGroupId($this->_config['admin_group_id']);
		$deal->setOwnerId($this->_config['deals_admin_user_id']);
		$deal->setAuthorId($this->_config['deals_admin_user_id']);
		$deal->setResponsiblePartyId($this->_config['deals_admin_user_id']);
		if ( $product['type'] == 'monthly' || $product['type'] == 'annual' )
			$deal->setDuration($product['duration']);
		
		$deal->save();
		return $deal;
	} // end addDeal
	
	
	/****************************************************************************************
	 * Add a Highrise tag
	/****************************************************************************************/
	public function addTag($tag, $subject_id, $subject_type) {
		$tag = new HighriseTag(null, $tag, $subject_type);
		$tag->setHighrise($this->_hr);
		$tag->setSubjectId($subject_id);
		$tag->save();
	}
	
	/****************************************************************************************
	 * Add a Highrise task associatied with a deal
	/****************************************************************************************/
	public function addTask($task_body, $due_date, HighriseDeal $deal) {
		$task = new HighriseTask($this->_hr);
		// Set due dates and reminders
		$task->setFrame("specific");
		$task->setDueAt($due_date);
		$task->setBody($task_body);
		$task->setSubjectType('Deal');
		$task->setSubjectId($deal->id);
		$task->setPublic('true');
		$result = array('before' => $task);
		$task->save();
		$result['after'] = $task;
		return $result;
	} // end addTask
	
	/****************************************************************************************
	 * Use product array to return appropriate Highrise Deal category ID.
	 * WARNING!!! DEAL CATEGORY ID'S MUST BE PROPERLY SET IN config.php
	/****************************************************************************************/
	protected function _getDealCategoryId($category, $type) {
		// GENERAL
		if ( !$category || preg_match( '/general/i', $category ) ) {
			switch ($type) {
				case 'monthly':
					return $this->_config['general_monthly_deal_category_id'];
					break;
				case 'annual':
					return $this->_config['general_annual_deal_category_id'];
					break;
				default:
					return $this->_config['general_onetime_deal_category_id'];
			}
		}
		
		// BUSINESS PARTNER
		if ( preg_match( '/business/i', $category ) )
			return $this->_config['business_monthly_deal_category_id'];
	 
		// RESCUE
		if ( preg_match( '/rescue/i', $category ) )
			return $this->_config['rescue_onetime_deal_category_id'];
			
		// RESTORE
		switch ($type) {
		 case 'monthly':
		 	return $this->_config['restore_monthly_deal_category_id'];
		 	break;
		 case 'annual':
		 	return $this->_config['restore_annual_deal_category_id'];
		 	break;
		 default:
		 	return $this->_config['restore_onetime_deal_category_id'];
		}
	}
	
	/****************************************************************************************
	 * Return CWW-specific recurring type as highrise Deal Price Type
	/****************************************************************************************/
	protected function _getPriceType($cww_type) {
		switch($cww_type) {
			case 'monthly':
				return 'month';
				break;
			case 'annual':
				return 'year';
				break;
			default:
				return 'fixed';
		}
	}
}