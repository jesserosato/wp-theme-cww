<?php
	
	class HighriseParty extends HighriseAPI
	{
		private $highrise;
		public $person;
		public $company;
		public $type;

		public function __construct(HighriseAPI $highrise, $obj = '')
		{
			$this->highrise = $highrise;
			$this->account = $highrise->account;
			$this->token = $highrise->token;
			$this->debug = $highrise->debug;
			$this->curl = curl_init();

			if (!empty($obj)) {
				if (!is_object($obj)) {
					throw new Exception("you didn't pass in an object to the HighriseParty constructor");
				}

				$class = get_class($obj);

				if ($class = "HighrisePerson") {
					$this->person = $obj;
					$this->type = "Person";
				} elseif ($class = "HighriseCompany") {
					$this->company = $obj;
					$this->type = "Company";
				} else {
					throw new Exception("the object that gets passed into HighriseParty must be either HighrisePerson or HighriseCompany");
				}
			}
		}

		public function createXML(&$xml) {

			if ($this->type == "Company") {
				$xml = $this->company->createXML($xml);
			} elseif ($this->type == "Person") {
				$xml = $this->person->createXML($xml);
			} else {
				throw new Exception("Party type is not supported: " . $this->type);
			}

			return $xml;

		}
		public function toXML()
		{
			if (empty($this->type)) {
				return "";
			}

			if ($this->type == "Company") {
				$xml = $this->company->toXML("party");
			} elseif ($this->type == "Person") {
				$xml = $this->person->toXML("party");
			} else {
				throw new Exception("Party type is not supported: " . $this->type);
			}
			$xml = str_replace('<?xml version="1.0"?>','',$xml);
			return $xml;
		}		
		
		public function loadFromXMLObject($xml_obj)
		{

			if ($this->debug) {
				print_r($xml_obj);
			}

			if (empty($xml_obj->{'type'})) {
				return false;
			}

			if ($xml_obj->{'type'} == "Company") {
				$this->type = "Company";
                        	$this->company = new HighriseCompany($this->highrise);
                        	$this->company->loadFromXMLObject($xml_obj);
			} elseif ($xml_obj->{'type'} == "Person") {
				$this->type = "Person";
                        	$this->person = new HighrisePerson($this->highrise);
                        	$this->person->loadFromXMLObject($xml_obj);
			} else {
				throw new Exception("Party type is not supported: " . $xml_obj->{'type'});
			}

		}

	}

