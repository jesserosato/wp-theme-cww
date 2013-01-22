<?php
define("SF_SOAP_CLIENT_DIR", __DIR__ . '/API/soapclient/');
define("ERROR_NO_RESPONSE", "There was no response from Salesforce.  Try again later.");

require_once(SF_SOAP_CLIENT_DIR . 'SforcePartnerClient.php');

class SalesforceSOAPAPIInterface
{
	protected $errors = array();
	protected $responses;		// for debugging mode
	protected $_sf_connection;	// connection
	protected $_debug = false;  // debugging mode
	
	public function __construct($sf_info )
	{
		// TO-DO: $this->_validate_sf_info($sf_info);
		
		// Use debug mode when sandbox parameter is passed.
		if ( !( empty( $sf_info['sandbox'] ) ) )
		$this->enable_debug(); 
		
		// Login.
		$this->_connect_to_sf( $sf_info );
	}
	
	/**
	 * Returns errors
	 *
	 * @return array
	 *
	 */
	public function get_errors()
	{
		return $this->errors;
	}
	
	/**
	 * Returns responses
	 *
	 * @return array or false
	 *
	 */
	public function get_responses()
	{
		return ($this->_debug ? $this->responses : false);
	}
	
	
	/**
	 * Prepare any salesforce object
	 *
	 * @param $obj_arr array
	 * @param $fields_is_obj bool
	 * @param $context SalesforceSOAPAPIInterface
	 * 
	 * @return stdClass
	 *
	 */
	public function prep_sf_obj( array $obj_arr, $fields_is_obj = false )
	{	
		$obj = new stdClass();
		
		// META
		if ( empty( $obj_arr['meta']['type'] ) )
			return false;
		$prefix = empty( $obj_arr['meta']['prefix'] ) ? '' : ($obj_arr['meta']['prefix'] . '__');
		$custom = empty($obj_arr['meta']['custom']) ? '' : '__c';
		$obj->type = $prefix . $obj_arr['meta']['type'] . $custom;
		unset($obj_arr['meta']);
		
		// DATA
		foreach ( $obj_arr as $field => $arr ) {
			if ( empty ( $arr ) || ( is_array( $arr ) && !empty( $arr['ignore'] ) ) )
				continue;
				
			// Complex fields use an array, simple fields are simple key => value pairs.
			if ( is_array( $arr ) ) {
				if ( !empty( $arr['custom'] ) )
					$field .= '__c';
				if ( !empty( $arr['prefix'] ) )
					$field = $prefix . $field;
				// Use callback to set value if supplied (args required to use callback)
				if ( !empty( $arr['callback'] ) && !empty( $arr['args'] ) ) {
					// If the callback is a one element array, set it to use $this.
					// If the callback is an array with > 2 elements, skip this field.
					if ( is_array( $arr['callback'] ) ) {
						if ( count( $arr['callback'] ) > 2 )
							continue;
						if ( count( $arr['callback'] ) == 1 )
							$arr['callback'] = array($this, array_shift($arr['callback']));
					}
					// Put args into array if they aren't already
					$arr['args'] = is_array($arr['args']) ? $arr['args'] : array($arr['args']);
					$arr['value'] = call_user_func_array($arr['callback'], $arr['args']);
				}
				$value = $arr['value'];
			} else {
				$value = $arr;
			}
			
			// Check that if the field is required, that it has a value
			if ( !empty( $arr['required'] ) && empty ( $value ) ) {
				$this->_error(new Exception("Value for $field field is required."));
				return false;
			}
				
			
			// Assign the property to the objects field property in the appropriate way
			if ( !empty( $value ) ) {
				if ( $fields_is_obj )
					$obj->fields->$field = $value;
				else
					$obj->fields[$field] = $value;
			}
		}
		
		return $obj;
	}

	
	/**
	 * Provides access to the _sf_connection query method
	 *
	 * @param $query string
	 *
	 * @return QueryResult or false
	 *
	 */
	 public function query( $query )
	 {
		 try {
			 $response = $this->_sf_connection->query( $query );
			 if ( $this->_debug ) $this->_response($response);
			 if ( $response )
			 	return $response;
			 else
				throw new Exception(ERROR_NO_RESPONSE);
		 } catch( Exception $e ) {
			 $this->_error( $e );
			 return false;
		 }
	 }
	 
	 /**
	  * Provides access to the _sf_connection create method
	  *
	  * @param $objects array
	  *
	  * @return array or false
	  *
	  */
	 public function create( $objects )
	 {
		 if ( !is_array( $objects ) )
		 	return false;
		 try {
			 $response = $this->_sf_connection->create($objects);
			 if ( $this->_debug ) $this->_response($response);
			 if ( $response ) {
			 	if ( $response[0]->success )
			 		return $response;
			 	else
					throw new Exception($response[0]->errors[0]->message);
			} else {
				throw new Exception(ERROR_NO_RESPONSE);
			}
			 	
		 } catch( Exception $e ) {
			 $this->_error( $e );
			 return false;
		 }
	 }
	 
	 /**
	  * Provides access to the _sf_connection update method
	  *
	  * @param $objects array
	  *
	  * @return array or false
	  *
	  */
	 public function update( array $objects )
	 {
		 if ( !is_array( $objects ) )
		 	return false;
		 
		 foreach ( $objects as $object ) {
			 if ( empty( $object->Id ) )
			 	return false;
		 }
		 
		 try {
			 $response = $this->_sf_connection->update($objects);
			 if ( $this->_debug ) $this->_response($response);
			 if ( $response ) {
			 	if ( $response[0]->success )
			 		return $response;
			 	else
					throw new Exception($response[0]->errors[0]->message);
			} else {
				throw new Exception(ERROR_NO_RESPONSE);
			}
		 } catch( Exception $e ) {
			 $this->_error( $e );
			 return false;
		 }
	 }
	 
	 /**
	  * Provides access to the _sf_connection upsert method
	  *
	  * @param $ext_id_field string
	  * @param $objects array
	  *
	  * @return array or false
	  *
	  */
	 public function upsert( $ext_id_field, array $objects )
	 {
		 if ( empty( $ext_id_field ) || !is_array( $objects ) )
		 	return false;
		 
		 try {
			 $response = $this->_sf_connection->upsert($ext_id_field, $objects);
			 if ( $this->_debug ) $this->_response($response);
			 if ( $response ) {
			 	if ( $response[0]->success )
			 		return $response;
			 	else
					throw new Exception($response[0]->errors[0]->message);
			} else {
				throw new Exception(ERROR_NO_RESPONSE);
			}
		 } catch( Exception $e ) {
			 $this->_error( $e );
			 return false;
		 }
	 }
	 
	 /**
	 * Provides access to the _sf_connection delete method
	 *
	 * @param $ids array
	 *
	 * @return array or false
	 *
	 */
	 public function delete( $ids )
	 {
		 if ( !is_array( $ids ) )
		 	return false;
		 
		 try {
			 $response = $this->_sf_connection->delete($ids);
			 if ( $this->_debug ) $this->_response($response);
			 if ( $response ) {
			 	if ( $response[0]->success )
			 		return $response;
			 	else
					throw new Exception($response[0]->errors[0]->message);
			} else {
				throw new Exception(ERROR_NO_RESPONSE);
			}
		 } catch ( Exception $e ) {
		 	$this->_error( $e );
		 	return false;
		 }
	 }
	
	/**
	 * Enable debug mode
	 *
	 */
	public function enable_debug()
	{
		$this->responses = array();
		$this->_debug = true;
	}
	
	/**
	 * Save an error into the errors array
	 *
	 * @param $e
	 *
	 */
	protected function _error( $e )
	{
		$backtrace = debug_backtrace();
		array_shift($backtrace);
		$functions_backtrace = array();
		foreach ( $backtrace as $call )
			$functions_backtrace[] = $call['function'];
		$this->errors[] = array('backtrace' => $functions_backtrace, 'error' => $e);
	}
	
	/**
	 * Save an response into the responses array
	 *
	 * @param $response QueryResult or array
	 *
	 */
	protected function _response( $response )
	{
		$backtrace = debug_backtrace();
		array_shift($backtrace);
		$functions_backtrace = array();
		foreach ( $backtrace as $call )
			$functions_backtrace[] = $call['function'];
		$this->responses[] = array('backtrace' => $functions_backtrace, 'response' => $response);
	}
	 
	
	/*
	 * Establishes a connection and login with the Salesforce DB
	 *
	 */
	private function _connect_to_sf( $sf_info )
	{
		try {
			$this->_sf_connection = new SforcePartnerClient();
			$this->_sf_connection->createConnection(SF_SOAP_CLIENT_DIR . "partner.wsdl.xml");
			if ( !empty( $sf_info['sandbox'] ) )
				$this->_sf_connection->setEndpoint('https://test.salesforce.com/services/Soap/u/20.0');
			$username = $sf_info['username'] . (empty($sf_info['sandbox']) ? '' : '.' . $sf_info['sandbox']);
			$password = $sf_info['password'] . $sf_info['security_token'];
			$this->_sf_connection->login($username, $password);
		} catch( Exception $e ) {
			$this->errors['connection'] = $e;
		}
	} // end _connect()
} // end class