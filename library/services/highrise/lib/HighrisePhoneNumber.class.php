<?php

	class HighrisePhoneNumber
	{
		public $id;
		public $number;
		public $location;
	
		public function __construct($id = null, $number = null, $location = null)
		{
			$this->setId($id);
			$this->setNumber($number);
			$this->setLocation($location);			
		}
		
		public function createXML(&$xml)
		{ 
                        $xml->addChild("id",$this->getId());
                        $xml->id->addAttribute("type","integer");
                        $xml->addChild("number",$this->getNumber());
                        $xml->addChild("location",$this->getLocation());
			return $xml;
		}
			
		public function toXML()
		{

                        $xml = new SimpleXMLElement("<phone-number></phone-number>");
			$xml = $this->createXML($xml);
			return $xml->asXML();
		}
				
		public function setLocation ($location)
		{
			$valid_locations = array("Work", "Mobile", "Fax", "Pager", "Home", "Skype", "Other");
			$location = ucwords(strtolower($location));
			if ($location != null && !in_array($location, $valid_locations))
				throw new Exception("$location is not a valid location. Available locations: " . implode(", ", $valid_locations));
			
			$this->location = (string)$location;
		}

		public function getLocation ()
		{
			return $this->location;
		}
		
		public function setNumber ($number)
		{
			$this->number = (string)$number;
		}

		public function getNumber ()
		{
			return $this->number;
		}

		
		public function setId ($id)
		{
			$this->id = (string)$id;
		}

		public function getId ()
		{
			return $this->id;
		}

		public function __toString()
		{
			return $this->number;
		}	
	}
	
