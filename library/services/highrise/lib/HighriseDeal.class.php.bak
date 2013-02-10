<?php
	class HighriseDeal extends HighriseAPI
	{
		private $highrise;
		public $id;

		public $account_id;
		public $author_id;
		public $background;
		public $category_id;
		public $created_at;
		public $updated_at;
		public $currency;
		public $duration;
		public $group_id;
		public $visible_to;
		public $name;
		public $owner_id;
		public $party_id;
		public $price;
		public $price_type;
		public $responsible_party_id;
		public $status;
		public $status_changed_on;
		public $parties;
		public $party;
		
		public function __construct(HighriseAPI $highrise)
		{
			$this->highrise = $highrise;
			$this->account = $highrise->account;
			$this->token = $highrise->token;
			$this->debug = $highrise->debug;
			$this->curl = curl_init();		
			$this->parties = array();

		}

		public function status_update($status)
		{
			$valid_status = array(
				'pending',
				'won',
				'lost'
			);
			if (!in_array($status,$valid_status)) {
				throw new Exception("'$status' is not a valid status type. Available status names: " . implode(", ", $valid_status));
			}
			$status_update_xml = "<status><name>$status</name></status>";
			$response = $this->postDataWithVerb("/deals/" . $this->getId() . "/status.xml", $status_update_xml, "PUT");
			$this->checkForErrors("Deals", 200);	
			return true;
		}
		
		public function save()
		{
			if ($this->getName() == null)
				throw new Exception("getName() returned null, you cannot save a deal without the name");

			$deal_xml = $this->toXML();

			if ($this->id == null) {

				$new_deal_xml = $this->postDataWithVerb("/deals.xml", $deal_xml, "POST");
				$this->checkForErrors("Deal", 201);	
				$this->loadFromXMLObject(simplexml_load_string($new_deal_xml));
			} else {
				$new_deal_xml = $this->postDataWithVerb("/deals/" . $this->getId() . ".xml", $deal_xml, "PUT");
				$this->checkForErrors("Deal", 200);	
			}
			return true;	
		}
		
		public function delete()
		{
			$this->postDataWithVerb("/deals/" . $this->getId() . ".xml", "", "DELETE");
			$this->checkForErrors("Task", 200);	
			$this->deleted = true;
		}
		
		public function assignToUser(HighriseUser $user)
		{
			$this->setOwnerId($user->getId());
		}

		public function setId($deal_id)
		{
			$this->id = (string)$deal_id;
		}

		public function getId()
		{
			return $this->id;
		}

		public function setOwnerId($owner_id)
		{
			$this->owner_id = (string)$owner_id;
		}

		public function getOwnerId()
		{
			return $this->owner_id;
		}

		
		public function setAccountId($account_id)
		{
			$this->account_id = (string)$account_id;
		}

		public function getAccountId()
		{
			return $this->account_id;
		}

		public function setAuthorId($author_id)
		{
			$this->author_id = (string)$author_id;
		}

		public function getAuthorId()
		{
			return $this->author_id;
		}

		
		
		public function setBackground($background)
		{
			$this->background = (string)$background;
		}

		public function getBackground()
		{
			return $this->background;
		}

		
		public function setCategoryId($category_id)
		{
			$this->category_id = (string)$category_id;
		}

		public function getCategoryId()
		{
			return $this->category_id;
		}

		
		// this shouldn't really be a function...
		public function setCreatedAt($created_at)
		{
			$this->created_at = (string)$created_at;
		}

		public function getCreatedAt()
		{
			return $this->created_at;
		}

		
		public function setCurrency($currency = 'USD')
		{
			$this->currency = (string)$currency;
		}

		public function getCurrency()
		{
			return $this->currency;
		}

		public function setDuration($duration)
		{
			$this->duration = (string)$duration;
		}

		public function getDuration()
		{
			return $this->duration;
		}

		
		public function setGroupId($group_id)
		{
			$this->group_id = (string)$group_id;
		}

		public function getGroupId()
		{
			return $this->group_id;
		}

		
		public function setName($name)
		{
			$this->name = (string)$name;
		}

		public function getName()
		{
			return $this->name;
		}

		public function setPartyId($party_id)
		{
			$this->party_id = (string)$party_id;
		}

		public function getPartyId()
		{
			return $this->party_id;
		}

		
		public function setPrice($price)
		{
			$this->price = (string)$price;
		}

		public function getPrice()
		{
			return $this->price;
		}

		
		public function setPriceType($price_type)
		{
			$valid_price_types = array("fixed", "hour", "month", "year");
			$price_type = strtolower($price_type);
			if ($price_type != null && !in_array($price_type, $valid_price_types)) {
				throw new Exception("$price_type is not a valid price type. Available price types: " . implode(", ", $valid_price_types));
			}
			$this->price_type = (string)$price_type;

		}

		public function getPriceType()
		{
			return $this->price_type;
		}

		
		public function setResponsiblePartyId($responsible_party_id)
		{
			$this->responsible_party_id = (string)$responsible_party_id;
		}

		public function getResponsiblePartyId()
		{
			return $this->responsible_party_id;
		}

		public function setStatus($status)
		{
			$valid_statuses = array("pending", "won", "lost");
			$status = strtolower($status);
			if ($status != null && !in_array($status, $valid_statuses)) {
				throw new Exception("$status is not a valid status. Available statuses: " . implode(", ", $valid_statuses));
			}
			$this->status = (string)$status;

		}

		public function getStatus()
		{
			return $this->status;
		}

		// TODO:  shouldn't be a function.
		public function setStatusChangedOn($status_changed_on)
		{
			$this->status_changed_on = (string)$status_changed_on;
		}

		public function getStatusChangedOn()
		{
			return $this->status_changed_on;
		}

		// TODO:  shouldn't be a function.
		public function setUpdatedAt($updated_at)
		{
			$this->updated_at = (string)$updated_at;
		}

		public function getUpdatedAt()
		{
			return $this->updated_at;
		}

		public function setVisibleTo($visible_to)
		{
			$valid_permissions = array("Everyone", "Owner");
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

		// no set parties or party since they're "special"
		public function getParties()
		{
			return $this->parties;
		}

		public function getParty()
		{
			return $this->party;
		}


		/*
		public function addParty($obj) {
			if (!is_object($obj)) {
				throw new Exception("You didn't pass in an object to addParty()");
			}

			$party = new HighriseParty($this->highrise,$obj);
			$this->parties[] = $party;
		}
		*/

		public function createXML($xml) {

			if ($this->getName() == null) { 
				throw new Exception("HighriseDeals::getName returned null which is invalid inside of createXML.  Name is required for a deal");
			}

			$xml->addChild('id',$this->getId());
			$xml->{'id'}->addAttribute('type','integer');

			$xml->addChild('name',$this->getName());

			$xml->addChild('account-id',$this->getAccountId());
			$xml->{'account-id'}->addAttribute('type','integer');

			$xml->addChild('author-id',$this->getAuthorId());
			$xml->{'author-id'}->addAttribute('type','integer');

			$xml->addChild('background',$this->getBackground());

			$xml->addChild('category-id',$this->getCategoryId());
			$xml->{'category-id'}->addAttribute('type','integer');

			$xml->addChild('created-at',$this->getCreatedAt());
			$xml->{'created-at'}->addAttribute('type','datetime');

			$xml->addChild('updated-at',$this->getUpdatedAt());
			$xml->{'updated-at'}->addAttribute('type','datetime');

			$xml->addChild('currency',$this->getCurrency());

			$xml->addChild('duration',$this->getDuration());
			$xml->{'duration'}->addAttribute('type','integer');

			$xml->addChild('group-id',$this->getGroupId());
			$xml->{'group-id'}->addAttribute('type','integer');

			$xml->addChild('owner-id',$this->getOwnerId());
			$xml->{'owner-id'}->addAttribute('type','integer');

			$xml->addChild('party-id',$this->getPartyId());
			$xml->{'party-id'}->addAttribute('type','integer');

			$xml->addChild('price',$this->getPrice());
			$xml->{'price'}->addAttribute('type','integer');

			$xml->addChild('price-type',$this->getPriceType());

			$xml->addChild('responsible-party-id',$this->getResponsiblePartyId());
			$xml->{'responsible-party-id'}->addAttribute('type','integer');

			$xml->addChild('status',$this->getStatus());

			$xml->addChild('status-changed-on',$this->getStatusChangedOn());
			$xml->{'status-changed-on'}->addAttribute('type','date');

			$xml->addChild('visible-to',$this->getVisibleTo());

			# if (is_object($this->party)) {
				# $party = $xml->addChild("party");
				# $this->party->createXML($party);
			# }

			if (count($this->parties) > 0) {
				$parties = $xml->addChild('parties');
				$parties->addAttribute("type", "array");
				foreach ($this->parties as $party_obj) {
					$party = $parties->addChild("party");
					$party_obj->createXML($party);
				}
			}

			return $xml;

		}

		public function toXML()
		{

			$xml  = new SimpleXMLElement("<deal></deal>");
			$xml = $this->createXML($xml);
			return $xml->asXML();
		}		
		
		public function loadFromXMLObject($xml_obj)
		{
	
			if ($this->debug)
				print_r($xml_obj);

			$this->setId($xml_obj->{'id'});
			$this->setAccountId($xml_obj->{'account-id'});
			$this->setAuthorId($xml_obj->{'author-id'});
			$this->setBackground($xml_obj->{'background'});
			$this->setCategoryId($xml_obj->{'category-id'});
			$this->setCreatedAt($xml_obj->{'created-at'});
			if (empty($xml_obj->{'currency'})) {
				$this->setCurrency("USD");
			} else {
				$this->setCurrency($xml_obj->{'currency'});
			}
			$this->setDuration($xml_obj->{'duration'});
			$this->setGroupId($xml_obj->{'group-id'});
			$this->setName($xml_obj->{'name'});
			$this->setOwnerId($xml_obj->{'owner-id'});
			$this->setPartyId($xml_obj->{'party-id'});
			$this->setPrice($xml_obj->{'price'});
			$this->setPriceType($xml_obj->{'price-type'});
			$this->setResponsiblePartyId($xml_obj->{'responsible-party-id'});
			$this->setStatus($xml_obj->{'status'});
			$this->setStatusChangedOn($xml_obj->{'status-changed-on'});
			$this->setUpdatedAt($xml_obj->{'updated-at'});
			$this->setVisibleTo($xml_obj->{'visible-to'});
			$this->loadPartyFromXMLObject($xml_obj->{'party'});
			$this->loadPartiesFromXMLObject($xml_obj->{'parties'});

			return true;
		}


		function loadPartyFromXMLObject($xml_obj) {

			if ($xml_obj != null) {
				$this->party = new HighriseParty($this->highrise);
				$this->party->loadfromXMLObject($xml_obj);
			}

		}

		function loadPartiesFromXMLObject($xml_obj) {
			foreach ($xml_obj->{'party'} as $party_obj) {
				$new_party = new HighriseParty($this->highrise);
				$new_party->loadFromXMLObject($party_obj);
				$this->parties[] = $new_party;
			} 
		}

	}
	
