<?php

	class HighriseTwitterAccount
	{
		public $id;
		public $location;
		public $username;
		
		public function __construct($id = null, $username = null, $location = null)
		{
			$this->setId($id);
			$this->setUsername($username);
			$this->setLocation($location);			
		}
		
		public function createXML(&$xml) {
                        $xml->addChild("id",$this->getId());
                        $xml->id->addAttribute("type","integer");
                        $xml->addChild("username",$this->getUsername());
                        $xml->addChild("location",$this->getLocation());
			return $xml;
		}

		public function toXML()
		{
                        $xml = new SimpleXMLElement("<twitter-account></twitter-account>");
			$xml = $this->createXML($xml);
			return $xml->asXML;
		}
		
		public function setUrl($url)
		{
		 	throw new Exception("Cannot set URLs, change Username instead");
		}

		public function getUrl()
		{
			return "http://twitter.com/" . $this->getUsername();
		}
		
		public function setUsername($username)
		{
			$this->username = (string)$username;
			$this->url = $this->getUrl();
		}

		public function getUsername()
		{
			return $this->username;
		}

		public function setLocation($location)
		{
			$valid_locations = array("Business", "Personal", "Other");
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
	
