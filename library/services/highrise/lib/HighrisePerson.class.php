<?php

require_once('HighriseEntity.class.php');

	
	class HighrisePerson extends HighriseEntity
	{
		public $company_id;
		public $company_name;
		public $first_name;
		public $last_name;
		public $title;

		public function __construct(HighriseAPI $highrise)
		{
			parent::__construct($highrise);
			$this->url_base = "people";
			$this->errorcheck = "Person";
			$this->setType("Person");
		}


		// take in a more or less empty XML object, populate it with all the fields from the parent
		// add on our own custom fields then return the object
		public function createXML($xml) {

			$xml = parent::createXML($xml);
			$xml->addChild("company-id",$this->getCompanyId());
			$xml->addChild("company-name",$this->getCompanyName());
			$xml->addChild("first-name",$this->getFirstName());
			$xml->addChild("last-name",$this->getLastName());
			$subject_datas = $xml->addChild("subject_datas");
			$subject_datas->addAttribute("type", "array");
			foreach($this->customfields as $custom_field) {
  			$d = $subject_datas->addChild("subject_data");
  			foreach($custom_field->getXMLObject()->children() as $child) {
  			  $c = $d->addChild($child->getName(), (string)$child);
  			  foreach($child->attributes() as $attr)
  			    $c->addAttribute($attr->getName(), (string)$attr);
  			}
			}
			return $xml;
		}
		public function toXML($header = "person")
		{

			$xml = new SimpleXMLElement("<" . $header . "></" . $header . ">");
			$xml = $this->createXML($xml);
			return $xml->asXML();
		}
		
		public function loadFromXMLObject($xml_obj)
		{
			parent::loadFromXMLObject($xml_obj);

			$this->setFirstName($xml_obj->{'first-name'});
			$this->setLastName($xml_obj->{'last-name'});
			$this->setTitle($xml_obj->{'title'});
			$this->setCompanyId($xml_obj->{'company-id'});
			$this->setCompanyName($xml_obj->{'company-name'});
		}
		
		public function setCompanyId($company_id)
		{
			$this->company_id = (string)$company_id;
		}

		public function getCompanyId()
		{
			return $this->company_id;
		}
		
		public function setCompanyName($company_name)
		{
			$this->company_name = (string)$company_name;
		}

		public function getCompanyName()
		{
			return $this->company_name;
		}

		public function getFullName()
		{
			return $this->getFirstName() . " " . $this->getLastName();
		}
		public function setLastName($last_name)
		{
			$this->last_name = (string)$last_name;
		}

		public function getLastName()
		{
			return $this->last_name;
		}

		public function setFirstName($first_name)
		{
			$this->first_name = (string)$first_name;
		}

		public function getFirstName()
		{
			return $this->first_name;
		}

		public function setTitle($title)
		{
			$this->title = (string)$title;
		}

		public function getTitle()
		{
			return $this->title;
		}
	}
	
