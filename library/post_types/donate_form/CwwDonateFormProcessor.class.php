<?php
// Authorize.net SDK
require_once dirname(dirname(__DIR__)) . '/services//authorizenet/AuthorizeNet.php'; 
// Load parent class
require_once dirname(dirname(__DIR__)) . '/utilities/FormProcessor.class.php';

/************************************************************************************ 
/* A class for processing Donate Form entries
/* By Jesse Rosato, 2012 - jesse.rosato@gmail.com
/************************************************************************************/
class CwwDonateFormProcessor extends FormProcessor {
	protected $post_id;
	protected $meta_data;
	protected $settings;
	protected $data;
	
	/************************************************************************************ 
	/* Default constructor
	/************************************************************************************/
	public function __construct() {
		if( empty( $_POST['df_post_id'] ) )
			throw new Exception('CwwDonateFormProcessor expects POST key "df_post_id".');
		$this->post_id	= $_POST['df_post_id'];
		unset($_POST['df_post_id']);
		
		parent::__construct();
		
		$this->set_meta_data();
		$this->set_settings();
		$this->set_required_fields();
		$this->_organize_data();
	} // end __construct()
	
	/************************************************************************************ 
	/* Set the Donate Form post's meta data.
	/*
	/* @param array $meta_data
	/************************************************************************************/
	public function set_meta_data( $meta_data = false ) {
		if ( !empty( $meta_data ) && is_array( $meta_data ) ) {
			$this->meta_data = $meta_data;
			return;
		}
			
		if ( function_exists( 'get_bloginfo' ) ) {
			$this->meta_data['org']				= get_bloginfo('name');
			$this->meta_data['org_mail']		= get_bloginfo('admin_email');
		}
		
		// Alias meta fields to make them more manageable for use
		$meta_fields = array(
			'monthly_duration'			=> 'cww_df_monthly_duration',
			'annual_duration'			=> 'cww_df_annual_duration',
			'mc_list_id'				=> 'cww_df_mc_list_id',
			'sf_update'					=> 'cww_df_sf_update',
			'sf_campaign'				=> 'cww_df_sf_campaign',
			'sf_category'				=> 'cww_df_sf_category',
			'sf_task_user'				=> 'cww_df_sf_task_user',
			'sf_donation_exp_task_desc'	=> 'cww_df_sf_donation_exp_task_desc',
			'sf_card_exp_task_desc'		=> 'cww_df_sf_card_exp_task_desc',
			'hr_update'					=> 'cww_df_hr_update',
			'hr_deals_admin_id'			=> 'cww_df_hr_deals_admin_id',
			'conf_post_id'				=> 'cww_df_conf_post_id',
			'conf_mail_post_id'			=> 'cww_df_conf_mail_post_id',
		);
		
		foreach ( $meta_fields as $key => $field)
			$this->meta_data[$key] = get_post_meta($this->post_id, $field, true);

		// Make sure conf_post_id and conf_mail_post_id are numeric
		if ( !is_numeric( $this->meta_data['conf_post_id'] ) )
			$this->meta_data['conf_post_id'] = $this->post_id;
		if ( !is_numeric( $this->meta_data['conf_mail_post_id'] ) ) 
			$this->meta_data['conf_mail_post_id'] = false;
			
		return $this->meta_data;
	} // end set_meta_data()
	
	/************************************************************************************ 
	/* Get the Donate Form post's meta data.
	/*
	/* @return array
	/************************************************************************************/
	public function get_meta_data( $key = false ) {
		if ( $key )
			return ( empty( $this->meta_data[$key] ) ? false : $this->meta_data[$key] );
		return ( empty( $this->meta_data ) ? array() : $this->meta_data );
	} // end get_meta_data()
	
	/************************************************************************************ 
	/* Set the sitewide Donate Form settings.
	/*
	/* @param array $settings
	/************************************************************************************/
	public function set_settings( $settings = false ) {
		if ( !empty( $settings ) && is_array( $settings ) )
			$this->settings = $settings;
		else
			$this->settings = get_option( 'cww_df_options' );
		if ( !empty( $this->meta_data['hr_deals_admin_id'] ) )
			$this->settings['cww_df_highrise_setting_deals_admin_user_id'] = $this->meta_data['hr_deals_admin_id'];
	} // end set_settings()
	
	/************************************************************************************ 
	/* Get the sitewide Donate Form settings.
	/*
	/* @return array
	/************************************************************************************/
	public function get_settings() {
		return ( empty( $this->settings ) ? array() : $this->settings );
	} // end get_settings()
	
	/************************************************************************************ 
	/* Set the form's required field keys.
	/*
	/* @param array $required_fields
	/************************************************************************************/
	public function set_required_fields( $required_fields = false ) {
		if ( !empty( $required_fields  ) && is_array( $required_fields ) ) {
			$this->required_fields = $required_fields;
			return;
		}
		
		$this->required_fields = array(
			'df_firstname',
			'df_lastname', 
			'df_address', 
			'df_city', 
			'df_state', 
			'df_zip', 
			'df_country', 
			'df_phone', 
			'df_email',
			'df_donor_type',
			'df_type',
			'df_card_num',
			'df_exp_date',
			'df_card_code'
		);
		
		if ( !empty( $this->clean['df_donor_type'] ) && $this->clean['df_donor_type'] != 'Individual' )
			$this->required_fields[]		= 'df_company';
		
		// Only set extended required fields if data has been sanitized
		// and donation type has been chosen.
		if ( empty( $this->clean ) || empty( $this->clean['df_type'] ) )
			return;
		
		switch ( $this->clean['df_type'] ) {
			case "monthly":
				$this->required_fields[]	= 'df_amount_monthly';
				$this->required_fields[]	= 'df_startdate';
				break;
			case "annual":
				$this->required_fields[]	= 'df_amount_annual';
				$this->required_fields[] 	= 'df_startdate';
				break;
			case "onetime":
				$this->required_fields[]	= 'df_amount_onetime';
				break;
		}
	} // end set_required_fields()
	
	/************************************************************************************ 
	/* Return organized data.
	/*
	/* @return array
	/************************************************************************************/
	public function get_organized_data() {
		return ( empty( $this->data ) ? array() : $this->data );
	}
	
	/************************************************************************************ 
	/* Set data element
	/*
	/* @return array
	/************************************************************************************/
	public function set_data_element($key, $value) {
		$this->data[$key] = $value;
	}
	
	/************************************************************************************ 
	/* Sanitize given data.
	/*
	/* @return array
	/************************************************************************************/
	public function sanitize_data( $data ) {
		if ( empty( $data ) || !is_array( $data ) )
			throw new Exception('sanitize_data() expects an array of data to sanitize.');
			
		$result = array();
		foreach( $data as $key => $val ) {
			// Get rid of slashes added by wordpress
			$val = stripslashes(stripslashes($val));
			$flag = preg_match('/email/i', $key) ? FILTER_SANITIZE_EMAIL : FILTER_SANITIZE_STRING;
			$result[$key] = filter_var(trim($val), $flag);
		}
		return $result;
	} // end sanitize_data()
	
	/************************************************************************************ 
	/* Validates data
	/*
	/* @return bool
	/************************************************************************************/
	public function validate_data() {
		// Check for empty required fields.
		if ( !$this->required_fields_set() )
			return false;
		
		// Validate amount
		$amount_field = 'df_amount_' . $this->data['donation']['type_code'];
		if ( $this->validate_currency( $this->data['donation']['amount'], $amount_field ) ) {
			// Amount passes muster, strip everything but digits and period.
			$this->data['donation']['amount'] = (float) preg_replace('/[^0-9.]/', '', $this->data['donation']['amount']);
		} else {
			return false;
		}
		
		// Validate start date	
		if ($this->data['donation']['recurring']) {
			if ( $this->validate_start_date( $this->data['donation']['start_date'], new DateTime() ) ) {
				// Start date passes muster. Replace non-dash delimiters
				$this->data['donation']['start_date'] = preg_replace('[-/]', '-', $this->data['donation']['start_date']);
			} else {
				return false;
			}
		}
		
		// Validate email address
		if ( in_array( 'df_email', $this->required_fields ) ) {
			if ( !filter_var( $this->data['donor']['email'], FILTER_VALIDATE_EMAIL ) ) {
				$this->errors['df_email'] = 'format';
				return false;
			}
		}
		
		// Validate phone number
		if ( in_array( 'df_phone', $this->required_fields ) ) {
			if( $this->validate_phone_number( $this->data['donor']['phone'], 'df_phone' ) ) {
				// Phone number passes muster, replace the non-numerical one in the donor array.
				$this->data['donor']['phone'] = preg_replace('[^0-9]', '', $this->data['donor']['phone']);
			} else {
				return false;
			}
		}
		
		// Validate card info
		// - Validate credit card number
		if ( !$this->validate_card_number( $this->data['card']['num'], 'df_card_num' ) )
			return false;
		if ( !$this->validate_card_exp( $this->data['card']['exp'], 'df_exp_date' ) )
			return false;
		if ( !$this->validate_card_code( $this->data['card']['code'], 'df_card_code') )
			return false;
		
		// Everything checks out
		return true;
		
	} // end validate_data()
	
	/************************************************************************************ 
	/* Submit donation to authorize.net and process response.
	/************************************************************************************/
	public function process_donation() {
		// Auth.net submission
		// Double check to make sure Authorize.net credentials are set.
		if ( empty( $this->settings['cww_df_authorizenet_setting_api_login_id'] ) )
			throw new Exception('process_payment() requires Authorize.net login id be set.');	
		if ( empty( $this->settings['cww_df_authorizenet_setting_transaction_key'] ) )
			throw new Exception('process_payment() requires Authorize.net transaction key be set.');
		$this->data['authnet']['login'] = $this->settings['cww_df_authorizenet_setting_api_login_id'];
		$this->data['authnet']['key'] = $this->settings['cww_df_authorizenet_setting_transaction_key'];
		
		$result = false;
		if ( $this->data['donation']['recurring'] ) {
			// - Recurring
			$response = $this->submit_recurring_donation($this->data, $this->post_id);
			// Handle errors thrown during payment processing
			if($response->xml->messages->resultCode == "Error"){
				// Transaction was NOT approved.
				$this->errors['form'] = 'processing';
			}else{
				// Transaction approved.
				$this->data['donation']['subscription_id'] = $response->getSubscriptionId();
				$result = true;
			}
		} else {  // end if($this->data['donation']['recurring'])
			// - One time
			$response = $this->submit_onetime_donation($this->data, $this->post_id); 
			// Handle errors thrown during payment processing
			if($response->approved){
				// Transaction approved.
				$this->data['donation']['transaction_id'] = $response->transaction_id;
				$result = true;
			} else{
				// Transaction was NOT approved.
				if ($response->declined) {
					// Card was declined.
					$this->errors['form'] = 'declined';
				} else {
					// Transaction error, or transaction held.
					$this->errors['form'] = 'processing';
				}
			}
		}
		return $result;
	} // end proces_payment()
	
	/************************************************************************************ 
	/* Submit onetime donation to Authorize.net
	/*
	/* @return array
	/************************************************************************************/
	public function submit_onetime_donation() {
		$transaction = new AuthorizeNetAIM($this->data['authnet']['login'], $this->data['authnet']['key']);
		$transaction->setFields(
	        array(
	        'amount' => $this->data['donation']['amount'], 
	        'card_num' => $this->data['card']['num'], 
	        'exp_date' => $this->data['card']['exp'],
	        'first_name' => $this->data['donor']['first_name'],
	        'last_name' => $this->data['donor']['last_name'],
	        'address' => $this->data['donor']['address'],
	        'city' => $this->data['donor']['city'],
	        'state' => $this->data['donor']['state'],
	        'country' => $this->data['donor']['country'],
	        'zip' => $this->data['donor']['zip'],
	        'email' => $this->data['donor']['email'],
	        'phone' => $this->data['donor']['phone'],
	        'card_code' => $this->data['card']['code'],
	        'invoice_num' => $this->data['donation']['type'],
	        )
	    );
		return $transaction->authorizeAndCapture();
	} // end submit_onetime_donation()
	
	/************************************************************************************ 
	/* Submit recurring donation to Authorize.net
	/*
	/* @return array
	/************************************************************************************/
	public function submit_recurring_donation() {
		$request = new AuthorizeNetARB($this->data['authnet']['login'], $this->data['authnet']['key']);
		
		// Auth.net expects all recurring donations to be expressed in terms of months,
		// Therefore annual contributions have an 'intervalLength' of 12 months.
		$this->data['donation']['interval_unit'] = "months";
		
		// Set the subscription fields.
		$name = $this->data['donor']['first_name'] . ' ' . $this->data['donor']['last_name'];
		$subscription = new AuthorizeNet_Subscription;
		// Donation data
		$subscription->name						= $name;
		$subscription->intervalLength			= $this->data['donation']['interval'];
		$subscription->intervalUnit				= $this->data['donation']['interval_unit'];
		$subscription->startDate				= $this->data['donation']['start_date'];
		$subscription->totalOccurrences			= $this->data['donation']['installments'];
		$subscription->amount					= $this->data['donation']['amount'];
		// Card data
		$subscription->creditCardCardNumber		= $this->data['card']['num'];
		$subscription->creditCardExpirationDate	= $this->data['card']['exp'];
		$subscription->creditCardCardCode		= $this->data['card']['code'];
		// Donor data
		$subscription->customerEmail			= $this->data['donor']['email'];
		$subscription->customerPhoneNumber		= $this->data['donor']['phone'];
		$subscription->billToFirstName			= $this->data['donor']['first_name'];
		$subscription->billToLastName			= $this->data['donor']['last_name'];
		$subscription->billToCompany			= $this->data['donor']['company'];
		$subscription->billToAddress			= $this->data['donor']['address'];
		$subscription->billToCity 				= $this->data['donor']['city'];
		$subscription->billToState 				= $this->data['donor']['state'];
		$subscription->billToZip 				= $this->data['donor']['zip'];
		$subscription->billToCountry 			= $this->data['donor']['country'];
		// Order data
		$subscription->orderInvoiceNumber		= $this->data['donation']['type'];
		
		// Submit request to Authorize.net
		return $request->createSubscription($subscription);
	} // end submit_recurring_donation()
	
	/************************************************************************************ 
	/* Send confirmation email.
	/*
	/* @param array $data
	/*
	/* @return bool
	/************************************************************************************/
	public function send_confirmation_mail( $data = false ) {
		if ( !function_exists( 'get_post' ) || empty( $this->meta_data['conf_mail_post_id'] ) )
		
			return false;
		if ( empty( $data ) || !is_array( $data ) )
			$data = $this->data;
		
		$mail_post		= get_post($this->meta_data['conf_mail_post_id']);
		$mail_body		= $mail_post->post_content;
		$mail_body		= apply_filters('the_content', $this->token_replace($mail_body));
		$mail_subject	= $mail_post->post_title;
		$mail_headers	= 'From: ' . $this->meta_data['org'] . ' <' . $this->meta_data['org_mail'] . '>';
		add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));
		if ( empty( $data['donor']['email'] ) )
			return false;
		if ( wp_mail($data['donor']['email'], $mail_subject, $mail_body, $mail_headers) ) {
			return true;
		} else {
			$this->errors['conf_mail'] = 'failure';
			return false;
		}
	} // end send_confirmation_mail
	
	/************************************************************************************ 
	/* Redirect user back to form.
	/*
	/* @param array $data
	/*
	/* @return
	/************************************************************************************/
	public function redirect( $data = false ) {
		if ( empty( $data ) || !is_array( $data ) )
			$data = $this->data;
			
		if ( !function_exists('get_post' ) )
			return;
			
		$trans_key = md5($data['donor']['first_name'] . $data['donor']['last_name'] . time());
		$conf_post = get_post($this->meta_data['conf_post_id']);
		$pass_data = array();
		$pass_data['conf_content'] = '';
		// Display any error messages generated after payment processing.
		if ( !empty( $this->errors ) && count( $this->errors ) ) {
			$pass_data['conf_content'] .= '<div class="attention"><ul>';
			foreach ( $this->errors as $field => $error ) {
				if ( !empty( $this->error_msgs[$field][$error] ) )	
					$pass_data['conf_content'] .= '<li>' . $this->error_msgs[$field][$error] . '</li>';
			}
			$pass_data['conf_content'] .= '</ul></div>';
		}
		$pass_data['conf_content'] .= apply_filters('the_content', $this->token_replace($conf_post->post_content));
		$pass_data['conf_post_id'] = $this->meta_data['conf_post_id'];
		set_transient($trans_key, serialize($pass_data), 300);
		// Redirect to confirmation page.
		$url = get_permalink($this->post_id);
		header('Location: ' . $url . '?df=' . $trans_key);
	} // end redirect()
	
	/************************************************************************************ 
	/* Replace tokens
	/* Supported tokens:
	/* - %transaction_id 
	/* - %donation_type 
	/* - %name
	/* - %address
	/* - %company
	/* - %card_number
	/* - %amount
	/*
	/* @param string $text
	/* @param array $data
	/*
	/* @return string
	/************************************************************************************/
	public function token_replace( $text, $data = false ) {
		if ( empty( $data ) || !is_array( $data ) )
			$data = $this->data;

		if ( !empty( $data['donation']['subscription_id'] ) )
			$transaction_id = $data['donation']['subscription_id'];
		else if ( !empty( $data['donation']['transaction_id'] ) )
			$transaction_id = $data['donation']['transaction_id'];
				
		if ( !empty( $transaction_id ) )
			$text = str_replace('%transaction_id', $transaction_id, $text);
			
		$text = str_replace('%donation_type', $data['donation']['type'], $text);
		
		$name = $data['donor']['first_name'] . ' ' . $data['donor']['last_name'];
		$text = str_replace('%name', $name, $text);
		
		if ( !empty( $data['donor']['address'] ) ) {
			$address = $data['donor']['address'] . ', ' . $data['donor']['city'] . ', ' . $data['donor']['state'] . ' ' . $data['donor']['zip'] . ' ' . $data['donor']['country'];
			$text = str_replace('%address', $address, $text);
		}
		
		$text = str_replace('%donor_type', $data['donor']['type'], $text);
				
		$text = str_replace('%company', $data['donor']['company'], $text);
		
		$card_num = substr($data['card']['num'], -4);
		$text = str_replace('%card_number', $card_num, $text);
		
		$amount = '$' . number_format($data['donation']['amount'], 2);
		$text = str_replace('%amount', $amount, $text);
		
		return $text;
	}
	
	/************************************************************************************ 
	/* Organize the data in $this->clean into $this->data.
	/* WARNING: This function is CWW specific.
	/************************************************************************************/
	protected function _organize_data() {
		// Make sure all the required fields are set before we organize.
		if ( !$this->required_fields_set() )
			return;
	
		$this->data = array();
		// Customer data
		$this->data['donor'] = array();
		$this->data['donor']['salutation']		= $this->clean['df_salutation'];
		$this->data['donor']['first_name'] 		= $this->clean['df_firstname'];
		$this->data['donor']['last_name'] 		= $this->clean['df_lastname'];
		$this->data['donor']['type']			= $this->clean['df_donor_type'];
		$this->data['donor']['company'] 		= $this->clean['df_company'];
		$this->data['donor']['address'] 		= $this->clean['df_address'];
		$this->data['donor']['city'] 			= $this->clean['df_city'];
		$this->data['donor']['state'] 			= $this->clean['df_state'];
		$this->data['donor']['zip'] 			= $this->clean['df_zip'];
		$this->data['donor']['country'] 		= $this->clean['df_country'];
		$this->data['donor']['phone'] 			= $this->clean['df_phone'];
		$this->data['donor']['email'] 			= $this->clean['df_email'];
		$this->data['donor']['notes']			= $this->clean['df_notes'];
		$this->data['donor']['subscribe'] 		= empty($this->clean['df_subscribe']) ? false : $this->clean['df_subscribe'];
		
		// - Card data: SECURITY RISK!! DO NOT STORE!!! ONLY FOR AUTHORIZE.NET
		$this->data['card'] = array();
		$this->data['card']['num']  			= $this->clean['df_card_num']; 
		$this->data['card']['exp']  			= $this->clean['df_exp_date'];
		$this->data['card']['code'] 			= $this->clean['df_card_code'];

		// - Transaction data
		$this->data['donation']['start_date']	= $this->clean['df_startdate'];
		// Make sure donation type is set before organizing donation type data
		if ( !empty( $this->clean['df_type'] ) ) {
			$this->data['donation']['type_code']	= $this->clean['df_type'];
			// Set up recurring payment data
			switch ( $this->data['donation']['type_code'] ) {
				case "monthly":
					$this->data['donation']['amount']		= $this->clean['df_amount_monthly'];
					$this->data['donation']['interval']		= '1';
					$this->data['donation']['type']			= 'Monthly Partner';
					$this->data['donation']['recurring']	= true;
					$this->data['donation']['period']		= 'monthly';
					$this->data['donation']['installments'] = $this->meta_data['monthly_duration'];
					break;
				case 'annual':
					$this->data['donation']['amount']		= $this->clean['df_amount_annual'];
					$this->data['donation']['interval']		= '12';
					$this->data['donation']['type']			= 'Annual Donation';
					$this->data['donation']['recurring']	= true;
					$this->data['donation']['period']		= 'yearly';
					$this->data['donation']['installments'] = $this->meta_data['annual_duration'];
					break;
				case 'onetime':
					$this->data['donation']['type']			= 'One Time';
					$this->data['donation']['amount']		= $this->clean['df_amount_onetime'];
					$this->data['donation']['recurring']	= false;
					break;
			}
		}
	} // end organize_data()
	
} // end class