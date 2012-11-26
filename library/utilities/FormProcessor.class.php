<?php
/************************************************************************************ 
/* A class for processing forms.
/* By Jesse Rosato, 2012 - jesse.rosato@gmail.com
/************************************************************************************/
class FormProcessor {
	
	protected $required_fields;
	protected $clean;
	protected $errors;
	public $error_msgs;
	
	/************************************************************************************ 
	/* Default constructor
	/************************************************************************************/
	public function __construct( $method = null ) {
		$method = !empty( $method ) && strtoupper( $method ) == 'GET' ? 'GET' : 'POST';
		if( $method == 'POST' && empty( $_POST ) )
			throw new Exception('FormProcessor expects that form has been submitted via POST.');
		if( $method == 'GET' && empty( $_GET ) )
			throw new Exception('FormProcessor expects that form has been submitted via GET.');

		$raw 						= $method == 'POST' ? $_POST : $_GET;		
		$this->clean				= $this->sanitize_data($raw);
		$this->errors				= array();
		$this->error_msgs			= array();
	} // end __construct()
	
	/************************************************************************************ 
	/* Set the form's required field keys.
	/*
	/* @param array $required_fields
	/************************************************************************************/
	public function set_required_fields( $required_fields ) {
		if ( !empty( $required_fields ) && is_array( $required_fields ) )
			$this->required_fields = $required_fields;
	}
	
	/************************************************************************************ 
	/* Get the form's required field keys.
	/*
	/* @returns
	/* array of required fields
	/************************************************************************************/
	public function get_required_fields() {
		if ( !empty( $this->required_fields ) )
			return $this->required_fields;
		return false;
	}
	
	/************************************************************************************ 
	/* Return sanitized data.
	/*
	/* @returns
	/* array of sanitized data
	/************************************************************************************/
	public function get_sanitized_data() {
		return ( empty( $this->clean ) ? array() : $this->clean );
	}
	
	/************************************************************************************ 
	/* Return errors.
	/*
	/* @returns
	/* array of errors
	/************************************************************************************/
	public function get_errors() {
		return ( empty( $this->errors ) ? array() : $this->errors );
	}
	
	/************************************************************************************ 
	/* Sanitize given data.
	/*
	/* @param array $data
	/*
	/* @return array
	/************************************************************************************/
	public function sanitize_data( $data ) {
		if ( empty( $data ) || !is_array( $data ) )
			throw new Exception('sanitize_data() expects an array of data to sanitize.');
			
		$result = array();
		foreach( $data as $key=>$val ) {
			$val = trim($val);
			$flag = preg_match('/email/i', $key) ? FILTER_SANITIZE_EMAIL : FILTER_SANITIZE_STRING;
			$result[$key] = filter_var($val, $flag);
		}
		return $result;
	} // end sanitize_data()
	
	/************************************************************************************ 
	/* Validates data
	/*
	/* @param array $data
	/*
	/* @return bool
	/************************************************************************************/
	public function validate_data() {
		// Check for empty required fields.
		return $this->required_fields_set( $this->clean );
	}
	
	/************************************************************************************ 
	/* Checks for empty required fields
	/*
	/* @param array $data
	/*
	/* @return bool
	/************************************************************************************/
	public function required_fields_set( ) {
		if ( empty( $this->required_fields ) )
			return true;
		$result = true;
		foreach ( $this->required_fields as $field ) {
			if ( empty( $this->clean[$field] ) ) {
				$this->errors[$field] = 'empty';
				$result = false;
			}
		}
		return $result;
	} // end required_fields_set()
	
	/************************************************************************************ 
	/* Validates a field's input as currency.
	/*
	/* @param string $cur
	/* @param string $key
	/*
	/* @return bool
	/************************************************************************************/
	public function validate_currency( $cur, $key = 'price' ) {
		$filter_options = array('options' => array('regexp' => '/^\$?([0-9]{1,3},([0-9]{3},)*[0-9]{3}|[0-9]+)(.[0-9][0-9])?$/'));
		if ( !filter_var( $cur, FILTER_VALIDATE_REGEXP, $filter_options ) ) {
			$this->errors[$key] = 'format';
			return false;
		}
		return true;
	} // end validate_currency()
	
	/************************************************************************************ 
	/* Validates a field's input as a date (optionally later than $after).
	/*
	/* @param string $date
	/* @param string $key
	/* @param DateTime $after
	/*
	/* @return bool
	/************************************************************************************/
	public function validate_start_date( $date, $key = 'startdate', DateTime $after = null ) {
		$filter_options = array('options' => array('regexp' => '/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/'));
		if ( !filter_var( $date, FILTER_VALIDATE_REGEXP, $filter_options ) ) {
			$this->errors[$key] = 'format';
			return false;
		} else if ($after) {
			$now = $after->getTimestamp();
			$start = strtotime($date);
			if ($start < $now) {
				$this->errors[$key] = 'invalid';
				return false;
			}
		}
		return true;
	} // end validate_start_date()
	
	/************************************************************************************ 
	/* Validates a field's input as a credit card number.
	/*
	/* @param string $num
	/* @param string $key
	/*
	/* @return bool
	/************************************************************************************/
	function validate_card_number($num, $key = 'card_num') {
		$filter_options = array('options' => array('regexp' => '/^(?:4[0-9]{12}(?:[0-9]{3})?|5[1-5][0-9]{14}|6011[0-9]{12}|3(?:0[0-5]|[68][0-9])[0-9]{11}|3[47][0-9]{13})$/'));
		if ( !filter_var( $num, FILTER_VALIDATE_REGEXP, $filter_options ) ) {
			$this->errors[$key] = 'format';
			return false;
		}
		return true;
	} // end validate_card_num()
	
	/************************************************************************************ 
	/* Validates a field's input as a credit card date.
	/*
	/* @param string $exp
	/* @param string $key
	/*
	/* @return bool
	/************************************************************************************/
	function validate_card_exp($exp, $key = 'card_exp') {
		$filter_options = array('options' => array('regexp' => '/^(0[1-9]|1[012])(1[2-9]|[2-9][0-9])$/'));
		if ( !filter_var( $exp, FILTER_VALIDATE_REGEXP, $filter_options ) ) {
			$this->errors[$key] = 'format';
			return false;
		} else {
			// Check card expiration date.
			$today = date("m-y");
			$today = strtotime($today);
			$exp = strtotime($exp);
			if ($exp < $today) {
				$this->errors[$key] = 'invalid';
				return false;
			}
		}
		return true;
	} // end validate_card_exp()
	
	/************************************************************************************ 
	/* Validates a field's input as a credit card code.
	/* WARNING: VERY LOOSE VALIDATION.
	/*
	/* @param string $code
	/* @param string $key
	/*
	/* @return bool
	/************************************************************************************/
	function validate_card_code($code, $key = 'card_code') {
		$filter_options = array('options' => array('regexp' => '/^[0-9]{3,4}$/'));
		if ( !filter_var( $code, FILTER_VALIDATE_REGEXP, $filter_options ) ) {
			$this->errors[$key] = 'format';
			return false;
		}
		return true;
	} // end validate_card_code()
	
	
	/************************************************************************************ 
	/* Validates a field's input as a phone number
	/*
	/* @param string $num
	/* @param string $key
	/*
	/* @return bool
	/************************************************************************************/
	function validate_phone_number($num, $key = 'phone') {
		$filter_options = array('options' => array('regexp'=>'/\(?\d{3}\)?[-\s.]?\d{3}[-\s.]?\d{4}/x'));
		if ( !filter_var( $num, FILTER_VALIDATE_REGEXP, $filter_options ) ) {
			$this->errors[$key] = 'format';
			return false;
		}
		return true;
	} // end validate_phone_number()
	
	/************************************************************************************ 
	/* Get a card type from a credit card number
	/*
	/* @param string $num
	/*
	/* @return string
	/************************************************************************************/
	function card_type($num) {
		if ( preg_match('/^4[0-9]{12}(?:[0-9]{3})?$/', $num) )
			return 'VISA';
		if ( preg_match('/^5[1-5][0-9]{14}$/', $num) )
			return 'MC';
		if ( preg_match('/^3[47][0-9]{13}$/', $num) )
			return 'AMEX';
		if ( preg_match('/^3(?:0[0-5]|[68][0-9])[0-9]{11}$/', $num) )
			return 'DC';
		if ( preg_match('/^6(?:011|5[0-9]{2})[0-9]{12}$/', $num) )
			return 'DISC';
		if ( preg_match('/^(?:2131|1800|35\d{3})\d{11}$/', $num) )
			return 'JCB';
		// No match.
		return false;
	}
	
} // end class
