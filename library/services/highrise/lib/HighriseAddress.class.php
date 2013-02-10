<?php
	class HighriseAddress
	{
		public $id;
		public $city;
		public $country;
		public $location;
		public $state;
		public $street;
		public $zip;

		public function createXML(&$xml) {
			$xml->addChild("id",$this->getId());
			$xml->id->addAttribute("type","integer");
			$xml->addChild("city",$this->getCity());
			$xml->addChild("country",$this->getCountry());
			$xml->addChild("location",$this->getLocation());
			$xml->addChild("state",$this->getState());
			$xml->addChild("street",$this->getStreet());
			$xml->addChild("zip",$this->getZip());
			return $xml;
		}
		
		public function toXML()
		{
			$xml = new SimpleXMLElement("<address></address>");
			$xml = $this->createXML($xml);
			return $xml->asXML();
		}
		
		public function __toString()
		{
			return $this->getFullAddress();
		}

		public function getFullAddress()
		{
			$return = "";
			if ($this->getStreet() != "" && $this->getStreet() != null)
			{
				$return .= $this->getStreet(). ", ";
			}
			
			if ($this->getCity() != "" && $this->getCity() != null)
			{
				$return .= $this->getCity(). ", ";
			}

			if ($this->getState() != "" && $this->getState() != null)
			{
				$return .= $this->getState(). ", ";
			}

			if ($this->getZip() != "" && $this->getZip() != null)
			{
				$return .= $this->getZip(). ", ";
			}

			if ($this->getCountry() != "" && $this->getCountry() != null)
			{
				$return .= $this->getCountry(). ".";
			}
			
			if (substr($return, -2) == ", ")
				$return = substr($return, 0, -2);
				
			return $return;
		}
		
		public function setZip($zip)
		{
			$this->zip = (string)$zip;
		}

		public function getZip()
		{
			return $this->zip;
		}
		
		public function setStreet($street)
		{
			$this->street = (string)$street;
		}

		public function getStreet()
		{
			return $this->street;
		}

		public function setState($state)
		{
			$this->state = (string)$state;
		}

		public function getState()
		{
			return $this->state;
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

		public function setCountry($country)
		{
			$this->country = (string)$country;
		}

		public function getCountry()
		{
			return $this->country;
		}

		public function setCity($city)
		{
			$this->city = (string)$city;
		}

		public function getCity()
		{
			return $this->city;
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
