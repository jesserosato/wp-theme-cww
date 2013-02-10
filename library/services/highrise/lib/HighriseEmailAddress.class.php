<?php
	class HighriseEmailAddress 
	{
		public $id;
		public $address;
		public $location;
		
		public function __construct($id = null, $address = null, $location = null)
		{
			$this->setId($id);
			$this->setAddress($address);
			$this->setLocation($location);			
		}

		public function createXML(&$xml)
		{
			$xml->addChild("id",$this->getId());
			$xml->id->addAttribute("type","integer");
			$xml->addChild("address",$this->getAddress());
			$xml->addChild("location",$this->getLocation());
			return $xml;
		}
		
		
		public function toXML()
		{
			$xml = new SimpleXMLElement("<email-address></email-address>");
			$xml = $this->createXML($xml);
			return $xml->asXML();
		}
		
		public function __toString()
		{
			return $this->getAddress();
		}
		
		public function setAddress($address)
		{
			$this->address = (string)$address;
		}

		public function getAddress()
		{
			return $this->address;
		}
	
		public function setLocation($location)
		{
			$valid_locations = array("Work", "Home", "Other");
			$location = ucwords(strtolower($location));
			if ($location != null && !in_array($location, $valid_locations))
				throw new Exception("$location is not a valid location. Available locations: " . implode(", ", $valid_locations));
				
			$this->location = (string)$location;
		}

		public function getLocation()
		{
			return $this->location;
		}

		public function setId($id)
		{
			$this->id = (string)$id;
		}

		public function getId()
		{
			return $this->id;
		}	
	}
		
