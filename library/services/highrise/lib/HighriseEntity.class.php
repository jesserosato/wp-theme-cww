<?php
	
	class HighriseEntity extends HighriseAPI
	{

		protected $url_base;
		protected $errorcheck;

		public $id;
		public $background;
		public $created_at;
		public $updated_at;
		public $type;
		
		// TODO: public $owner_id;
		// TODO: public $group_id;
		public $author_id;
		public $contact_details;
		public $visible_to;
		
		// contact-data
		
		public $email_addresses;
		public $phone_numbers;
		public $addresses;
		public $web_addresses;
		public $instant_messengers;
		public $twitter_accounts;

		public $tags;
		protected $original_tags;
		
		public $customfields = array();
		protected $original_customfields = array();
		
		public $notes;

		public function __construct(HighriseAPI $highrise)
		{
			$this->highrise = $highrise;
			$this->account = $highrise->account;
			$this->token = $highrise->token;
			$this->setVisibleTo("Everyone");
			$this->debug = $highrise->debug;
			$this->curl = curl_init();		

			$this->phone_numbers = array();
			$this->email_addresses = array();
			$this->web_addresses = array();
			$this->addresses = array();
			$this->instant_messengers = array();
			$this->twitter_accounts = array();
		}

		public function delete()
		{
			$this->postDataWithVerb("/" . $this->url_base . "/" . $this->getId() . ".xml", "", "DELETE");
			$this->checkForErrors($this->errorcheck, 200);	
		}

		public function save()
		{
			$person_xml = $this->toXML();
			if ($this->getId() != null)
			{
				$new_xml = $this->postDataWithVerb("/" . $this->url_base . "/" . $this->getId() . ".xml?reload=true", $person_xml, "PUT");
				$this->checkForErrors($this->errorcheck);
			}
			else
			{
				$new_xml = $this->postDataWithVerb("/" . $this->url_base . ".xml", $person_xml, "POST");
				$this->checkForErrors($this->errorcheck, 201);
			}
			
			// Reload object and add tags.
			$tags = $this->tags;
			$original_tags = $this->original_tags;
				
			$this->loadFromXMLObject(simplexml_load_string($new_xml));
			$this->tags = $tags;
			$this->original_tags = $original_tags;
			$this->saveTags();
		
			return true;
		}

		public function setType($type) {
			$this->type = (string)$type;
		}
		public function getType() {
			return (string)$this->type;
		}

		public function getEmailAddresses()
		{
			return $this->email_addresses;
		}

		public function getPhoneNumbers()
		{
			return $this->phone_numbers;
		}

		public function getAddresses()
		{
			return $this->addresses;
		}

		public function getWebAddresses()
		{
			return $this->web_addresses;
		}

		public function getInstantMessengers()
		{
			return $this->instant_messengers;
		}
		
		public function getTwitterAccounts()
		{
			return $this->twitter_accounts;
		}

		
		public function addNote(HighriseNote $note)
		{
			$note->setSubjectId($this->id);
			$note->setSubjectType("Party");
			$note->save();
			$this->notes[$note->id] = $note;
		}
		
		public function getNotes()
		{
			$this->notes = array();
			$xml = $this->getURL("/" . $this->url_base . "/" . $this->id . "/notes.xml");
			$xml_obj = simplexml_load_string($xml);

			if ($this->debug == true) {
				print_r($xml_obj);
			}
			
			if (isset($xml_obj->note) && count($xml_obj->note) > 0)
			{
				foreach($xml_obj->note as $xml_note)
				{
					$note = new HighriseNote($this->highrise);
					$note->loadFromXMLObject($xml_note);
					$this->addNote($note);		
				}
			}
			
			return $this->notes;
		}
		
		public function saveTags()
		{
			if (is_array($this->tags))
			{
				foreach($this->tags as $tag_name => $tag)
				{
					if ($tag->getId() == null) // New Tag
					{
					 	
						if ($this->debug)
							print "Adding Tag: " . $tag->getName() . "\n";
						$new_tag_data = $this->postDataWithVerb("/" . $this->url_base . "/" . $this->getId() . "/tags.xml", "<name>" . $tag->getName() . "</name>", "POST");
						$this->checkForErrors($this->errorcheck . " (add tag)", array(200, 201));
						$new_tag_data = simplexml_load_string($new_tag_data);
						$this->tags[$tag_name]->setId($new_tag_data->id);
						unset($this->original_tags[$tag->getId()]);

					}
					else // Remove Tag from deletion list
					{
						unset($this->original_tags[$tag->getId()]);
					}
				}
				
				if (is_array($this->original_tags))
				{
					foreach($this->original_tags as $tag_id=>$v)
					{
						if ($this->debug)
							print "REMOVE TAG: " . $tag_id;
						$new_tag_data = $this->postDataWithVerb("/" . $this->url_base . "/" . $this->getId() . "/tags/" . $tag_id . ".xml", "", "DELETE");
						$this->checkForErrors($this->errorcheck . " (delete tag)", 200);
					}					
				}
				
				foreach($this->tags as $tag_name => $tag)
					$this->original_tags[$tag->getId()] = 1;	
			}
		}

		public function getTags() {
			return $this->tags;
		}

		public function addTag($v)
		{
			if ($v instanceof HighriseTag && !isset($this->tags[$v->getName()]))
			{
				$this->tags[$v->getName()] = $v;
				$this->original_tags[$v->getId()] = 1;
				
			}
			elseif (!isset($this->tags[$v]))
			{
				$tag = new HighriseTag();
				$tag->name = $v;
				$this->tags[$v] = $tag;
			}
		}

		public function addCustomfield($v, $val=FALSE)
		{
			if ($v instanceof HighriseCustomfield && !isset($this->customfields[$v->getSubjectFieldLabel()]))
			{
				$this->customfields[$v->getSubjectFieldLabel()] = $v;
				$this->original_customfields[$v->getSubjectFieldLabel()] = 1;
				if($val) {
				  $this->customfields[$v->getSubjectFieldLabel()]->setValue($val);
				}
			}
			elseif (!isset($this->customfields[$v]))
			{
				$field = new HighriseCustomfield();
				$field->setSubjectFieldLabel = $v;
				$this->customfields[$v] = $field;
			}
		}

		public function toXML() {
			$xml = new SimpleXMLElement("<entity></entity>");
			$xml = $this->createXML($xml);
			return $xml->asXML();
		}
			
		public function createXML($xml)
		{

			$xml->addChild("id",$this->getId());
			$xml->id->addAttribute("type","integer");

			$created = $xml->addChild("created-at",$this->getCreatedAt());
			$created->addAttribute("type","datetime");

			$updated = $xml->addChild("updated-at",$this->getUpdatedAt());
			$updated->addAttribute("type","datetime");

			$xml->addChild("background",$this->getBackground());
			$xml->addChild("visible-to",$this->getVisibleTo());
			$xml->addChild("type",$this->getType());

			$contact_data = $xml->addChild("contact-data");

			// loop through the email addresses and append them to the XML object.
			$emails = $contact_data->addChild("email-addresses");
			foreach ($this->email_addresses as $email_obj) { 
				$email = $emails->addChild("email-address");
				$email_obj->createXML($email);
			}

			$phones = $contact_data->addChild("phone-numbers");

			foreach ($this->phone_numbers as $phone_obj) { 
				$phone = $phones->addChild("phone-number");
				$phone_obj->createXML($phone);
			}

			$addresses = $contact_data->addChild("addresses");
			foreach ($this->addresses as $address_obj) { 
				$address = $addresses->addChild("address");
				$address_obj->createXML($address);
			}

			$ims = $contact_data->addChild("instant-messengers");
			foreach ($this->instant_messengers as $im_obj) {
				$im = $ims->addChild("instant-messengers");
				$im_obj->createXML($im);
			}

			$twitters = $contact_data->addChild("twitter-accounts");
			foreach ($this->twitter_accounts as $twat_obj) {
				$twitter = $twitters->addChild("twitter-accounts");
				$twat_obj->createXML($twitter);
			}

			$webs = $contact_data->addChild("web-addresses");
			foreach ($this->web_addresses as $web_obj) {
				$web = $webs->addChild("web-addresses");
				$web_obj->createXML($web);
			}

			return $xml;
		}
		
		public function loadFromXMLObject($xml_obj)
		{
			if ($this->debug)
				print_r($xml_obj);
			
			$this->setId($xml_obj->id);
			$this->setAuthorId($xml_obj->{'author-id'});
			$this->setBackground($xml_obj->{'background'});
			$this->setVisibleTo($xml_obj->{'visible-to'});	
			$this->setCreatedAt($xml_obj->{'created-at'});
			$this->setUpdatedAt($xml_obj->{'updated-at'});
			$this->loadContactDataFromXMLObject($xml_obj->{'contact-data'});
			$this->loadTagsFromXMLObject($xml_obj->{'tags'});	
			$this->loadCustomfieldsFromXMLObject($xml_obj->{'subject_datas'});
		}
		
		public function loadCustomfieldsFromXMLObject($xml_obj)
		{
			$this->original_customfields = array();
			$this->customfields = array();
			
			if (count($xml_obj->{'subject_data'}) > 0)
			{
				foreach($xml_obj->{'subject_data'} as $field)
				{
					$new_field = new HighriseCustomfield($field->{'id'}, $field->{'value'}, $field->{'subject_field_id'}, $field->{'subject_field_label'});
					$this->original_customfields[$new_field->getSubjectFieldLabel()] = 1;
					$this->addCustomfield($new_field);
				}
			}
		}
		
		public function loadTagsFromXMLObject($xml_obj)
		{
			$this->original_tags = array();
			$this->tags = array();
			
			if (count($xml_obj->{'tag'}) > 0)
			{
				foreach($xml_obj->{'tag'} as $value)
				{
					$tag = new HighriseTag($value->{'id'}, $value->{'name'}, $this->type);
					$original_tags[$tag->getName()] = 1;	
					$this->addTag($tag);
				}
			}
		}
		
		public function loadContactDataFromXMLObject($xml_obj)
		{

			$this->phone_numbers = array();
			$this->email_addresses = array();
			$this->web_addresses = array();
			$this->addresses = array();
			$this->instant_messengers = array();
			
			if (isset($xml_obj->{'phone-numbers'}))
			{
				foreach($xml_obj->{'phone-numbers'}->{'phone-number'} as $value)
				{
					$number = new HighrisePhoneNumber($value->{'id'}, $value->{'number'}, $value->{'location'});
					$this->phone_numbers[] = $number;
				}				
			}

			if (isset($xml_obj->{'email-addresses'}))
			{			
				foreach($xml_obj->{'email-addresses'}->{'email-address'} as $value)
				{
					$email_address = new HighriseEmailAddress($value->{'id'}, $value->{'address'}, $value->{'location'});
					$this->email_addresses[] = $email_address;
				}
			}
			
			if (isset($xml_obj->{'instant-messengers'}))
			{
				foreach($xml_obj->{'instant-messengers'}->{'instant-messenger'} as $value)
				{
					$instant_messenger = new HighriseInstantMessenger($value->{'id'}, $value->{'protocol'}, $value->{'address'}, $value->{'location'});
					$this->instant_messengers[] = $instant_messenger;
				}
			}
			
			if (isset($xml_obj->{'web-addresses'}))
			{
				foreach($xml_obj->{'web-addresses'}->{'web-address'} as $value)
				{
					$web_address = new HighriseWebAddress($value->{'id'}, $value->{'url'}, $value->{'location'});
					$this->web_addresses[] = $web_address;
				}
			}
			
			if (isset($xml_obj->{'twitter-accounts'}))
			{
				foreach($xml_obj->{'twitter-accounts'}->{'twitter-account'} as $value)
				{
					$twitter_account = new HighriseTwitterAccount($value->{'id'}, $value->{'username'}, $value->{'location'});
					$this->twitter_accounts[] = $twitter_account;
				}
			}
			
			if (isset($xml_obj->{'addresses'}))
			{
				foreach($xml_obj->{'addresses'}->{'address'} as $value)
				{
					$address = new HighriseAddress();

					$address->setId($value->id);
					$address->setCity($value->city);
					$address->setCountry($value->country);
					$address->setLocation($value->location);
					$address->setState($value->state);
					$address->setStreet($value->street);
					$address->setZip($value->zip);

					$this->addresses[] = $address;
				}			
			}
		}

		public function addAddress($street, $city, $state, $zip, $country, $location = "Work") {

			$addr = new HighriseAddress();
			$addr->setStreet($street);
			$addr->setCity($city);
			$addr->setState($state);
			$addr->setZip($zip);
			$addr->setCountry($country);
			$addr->setLocation($location);
			$this->addresses[] = $addr;
		}

		/*
		public function addAddress(HighriseAddress $address)
		{
			$this->addresses[] = $address;
		}
		*/
		
		public function addEmailAddress($address, $location = "Work")
		{
			$item = new HighriseEmailAddress();
			$item->setAddress($address);
			$item->setLocation($location);
			
			$this->email_addresses[] = $item;
		}
		
		public function addPhoneNumber($number, $location = "Work")
		{
			$item = new HighrisePhoneNumber();
			$item->setNumber($number);
			$item->setLocation($location);
			
			$this->phone_numbers[] = $item;
		}

		public function addWebAddress($url, $location = "Work")
		{
			$item = new HighriseWebAddress();
			$item->setUrl($url);
			$item->setLocation($location);
			
			$this->web_addresses[] = $item;
		}
		
		public function addInstantMessenger($protocol, $address, $location = "Work")
		{
			$item = new HighriseInstantMessenger();
			$item->setProtocol($protocol);
			$item->setAddress($address);
			$item->setLocation($location);
				
			$this->instant_messengers[] = $item;
		}

		public function addTwitterAccount($username, $location = "Business")
		{
			$item = new HighriseTwitterAccount();
			$item->setUsername($username);
			$item->setLocation($location);
			
			$this->twitter_accounts[] = $item;
		}

		public function setCompanyId($company_id)
		{
			$this->company_id = (string)$company_id;
		}

		public function getCompanyId()
		{
			return $this->company_id;
		}
		
		public function setVisibleTo($visible_to)
		{
			$valid_permissions = array("Everyone", "Owner", "NamedGroup");
			if ($visible_to != 'NamedGroup')
				$visible_to = ucwords(strtolower($visible_to));
			if ($visible_to != null && !in_array($visible_to, $valid_permissions)) {
				throw new Exception("$visible_to is not a valid visibility permission. Available visibility permissions: " . implode(", ", $valid_permissions));
			}
			$this->visible_to = (string)$visible_to;
		}

		public function getVisibleTo()
		{
			return $this->visible_to;
		}
		
		public function setAuthorId($author_id)
		{
			$this->author_id = (string)$author_id;
		}

		public function getAuthorId()
		{
			return $this->author_id;
		}
	
		public function setUpdatedAt($updated_at)
		{
			$this->updated_at = (string)$updated_at;
		}

		public function getUpdatedAt()
		{
			return $this->updated_at;
		}
		
		public function setCreatedAt($created_at)
		{
			$this->created_at = (string)$created_at;
		}

		public function getCreatedAt()
		{
			return $this->created_at;
		}

		public function setBackground($background)
		{
			$this->background = (string)$background;
		}

		public function getBackground()
		{
			return $this->background;
		}

		public function setId($id)
		{
			$this->id = (string)$id;
		}

		public function getId()
		{
			return $this->id;
		}

        public function getCustomFields()
        {
            return $this->customfields;
        }

	}
	
