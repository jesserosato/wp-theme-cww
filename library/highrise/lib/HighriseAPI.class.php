<?php

require_once('HighriseAddress.class.php');
require_once('HighriseCompany.class.php');
require_once('HighriseCustomfield.class.php');
require_once('HighriseDeal.class.php');
require_once('HighriseEmailAddress.class.php');
require_once('HighriseEmail.class.php');
require_once('HighriseGroup.class.php');
require_once('HighriseInstantMessenger.class.php');
require_once('HighriseNote.class.php');
require_once('HighriseParty.class.php');
require_once('HighriseEntity.class.php');
require_once('HighrisePerson.class.php');
require_once('HighrisePhoneNumber.class.php');
require_once('HighriseTag.class.php');
require_once('HighriseTask.class.php');
require_once('HighriseTwitterAccount.class.php');
require_once('HighriseUser.class.php');
require_once('HighriseWebAddress.class.php');

	/*
		* http://developer.37signals.com/highrise/people
		*
		* TODO LIST:
		* Add Tasks support
		* Get comments for Notes / Emails
		* findPeopleByTagName
		* Get Company Name, etc proxy
		* Convenience methods for saving Notes $person->saveNotes() to check if notes were modified, etc.
		* Add Tags to Person
	*/
	
	class HighriseAPI
	{
		public $account;
		public $token;
		protected $curl;
		public $debug;
		
		public function __construct()
		{
			$this->curl = curl_init();
			curl_setopt($this->curl,CURLOPT_RETURNTRANSFER,true);

			curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('Accept: application/xml', 'Content-Type: application/xml'));
			// curl_setopt($curl,CURLOPT_POST,true);
			curl_setopt($this->curl,CURLOPT_SSL_VERIFYPEER,0);
			curl_setopt($this->curl,CURLOPT_SSL_VERIFYHOST,0);
		}
	
		public function setAccount($account)
		{
			$this->account = $account;
		}
		
		public function setToken($token)
		{
			$this->token = $token;
			curl_setopt($this->curl,CURLOPT_USERPWD,$this->token.':x');
		}

		protected function postDataWithVerb($path, $request_body, $verb = "POST")
		{
			$this->curl = curl_init();
			
			$url = "https://" . $this->account . ".highrisehq.com" . $path;

			if ($this->debug)
				print "postDataWithVerb $verb $url ============================\n";

			
			curl_setopt($this->curl, CURLOPT_URL,$url);
			curl_setopt($this->curl, CURLOPT_POSTFIELDS, $request_body);
			if ($this->debug == true)
				curl_setopt($this->curl, CURLOPT_VERBOSE, true);
				
			curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('Accept: application/xml', 'Content-Type: application/xml'));
			curl_setopt($this->curl, CURLOPT_USERPWD,$this->token.':x');
			curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER,0);
			curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST,0);
			curl_setopt($this->curl, CURLOPT_RETURNTRANSFER,true);
			
							
			if ($verb != "POST")
				curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, $verb);
 			else
				curl_setopt($this->curl, CURLOPT_POST, true);
				
			$ret = curl_exec($this->curl);
			
			if ($this->debug == true)
				print "Begin Request Body ============================\n" . htmlspecialchars($request_body) . "End Request Body ==============================\n";
			
			curl_setopt($this->curl,CURLOPT_HTTPGET, true);
			
			return $ret;
		}
		
		protected function getURL($path)
		{
			curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('Accept: application/xml', 'Content-Type: application/xml'));
			curl_setopt($this->curl, CURLOPT_USERPWD,$this->token.':x');
			curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER,0);
			curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST,0);
			curl_setopt($this->curl, CURLOPT_RETURNTRANSFER,true);

			$url = "https://" . $this->account . ".highrisehq.com" . $path;
	
			if ($this->debug == true)
				curl_setopt($this->curl, CURLOPT_VERBOSE, true);
	
				
			curl_setopt($this->curl,CURLOPT_URL,$url);
			$response = curl_exec($this->curl);

			if ($this->debug == true)
				print "Response: =============\n" . $response . "============\n";
		
			return $response;
			
		}
		
		protected function getLastReturnStatus()
		{
			return curl_getinfo($this->curl, CURLINFO_HTTP_CODE); 
		}
		
		protected function getXMLObjectForUrl($url)
		{
			$xml = $this->getURL($url);
			$xml_object = simplexml_load_string($xml);
			return $xml_object;
		}
		
		protected function checkForErrors($type, $expected_status_codes = 200)
		{
			if (!is_array($expected_status_codes))
				$expected_status_codes = array($expected_status_codes);
			
			if (!in_array($this->getLastReturnStatus(), $expected_status_codes))
			{
				switch($this->getLastReturnStatus())
				{
					case 404:
						throw new Exception("$type not found");
						break;
					case 403:
						throw new Exception("Access denied to $type resource");
						break;
					case 507:
						throw new Exception("Cannot create $type: Insufficient storage in your Highrise Account");
						break;
					
					default:
						throw new Exception("API for $type returned Status Code: " . $this->getLastReturnStatus() . " Expected Code: " . implode(",", $expected_status_codes));
						break;
				}				
			}
		}
		
		/* Users */
		
		public function findAllUsers()
		{
			$xml = $this->getUrl("/users.xml");
			$this->checkForErrors("User");
			
			$xml_object = simplexml_load_string($xml);
			
			$ret = array();
			foreach($xml_object->user as $xml_user)
			{
				$user = new HighriseUser();
				$user->loadFromXMLObject($xml_user);
				$ret[] = $user;
			}
			
			return $ret;
		}

		public function findUserByEmail($email)
		{

			$users = $this->findAllUsers();

			$foundusers = array();
			foreach ($users as $user) {
				if (strtolower(trim($email)) == strtolower(trim($user->email_address))) {
					return $user;
				}
			} 
			
			return false;
		}
		
		public function findMe()
		{
			$xml = $this->getUrl("/me.xml");
			$this->checkForErrors("User");
			
			$xml_obj = simplexml_load_string($xml);
			$user = new HighriseUser();
			$user->loadFromXMLObject($xml_obj);
			return $user;
		}
		
		/* Tasks */
		
		public function findCompletedTasks()
		{
			$xml = $this->getUrl("/tasks/completed.xml");
			$this->checkForErrors("Tasks");
			return $this->parseTasks($xml);
		}

		public function findAssignedTasks()
		{
			$xml = $this->getUrl("/tasks/assigned.xml");
			$this->checkForErrors("Tasks");
			return $this->parseTasks($xml);
		}

		
		public function findUpcomingTasks()
		{
			$xml = $this->getUrl("/tasks/upcoming.xml");
			$this->checkForErrors("Tasks");
			return $this->parseTasks($xml);
		}

		private function parseTasks($xml)
		{
			$xml_object = simplexml_load_string($xml);			
			$ret = array();
			foreach($xml_object->task as $xml_task)
			{
				$task = new HighriseTask($this);
				$task->loadFromXMLObject($xml_task);
				$ret[] = $task;
			}

			return $ret;
		
		}
		
		public function findTaskById($id)
		{
			$xml = $this->getURL("/tasks/$id.xml");
			$this->checkForErrors("Task");
			$task_xml = simplexml_load_string($xml);
			$task = new HighriseTask($this);
			$task->loadFromXMLObject($task_xml);
			return $task;
			
		}

		/* Deals */

		public function findDealById($id)
		{
			$xml = $this->getURL("/deals/$id.xml");
			$this->checkForErrors("Deal");
			$deal_xml = simplexml_load_string($xml);
			$deal = new HighriseDeal($this);
			$deal->loadFromXMLObject($deal_xml);
			return $deal;
		}

		public function findAllDeals()
		{
			$xml = $this->getUrl("/deals.xml");
			$this->checkForErrors("Deals");
			return $this->parseDeals($xml);
		}

	
		private function parseDeals($xml)
		{
			$xml_object = simplexml_load_string($xml);			
			$ret = array();
			foreach($xml_object->deal as $xml_deal)
			{
				# print_r($xml_deal);
				$deal = new HighriseDeal($this);
				$deal->loadFromXMLObject($xml_deal);
				$ret[] = $deal;
			}

			return $ret;
		
		}


		
		/* Notes & Emails */

		public function findEmailById($id)
		{
			$xml = $this->getURL("/emails/$id.xml");
			$this->checkForErrors("Email");
			$email_xml = simplexml_load_string($xml);
			$email = new HighriseEmail($this);
			$email->loadFromXMLObject($email_xml);
			return $email;
		}
				
		public function findNoteById($id)
		{
			$xml = $this->getURL("/notes/$id.xml");
			$this->checkForErrors("Note");
			$note_xml = simplexml_load_string($xml);
			$note = new HighriseNote($this);
			$note->loadFromXMLObject($note_xml);
			return $note;
		}
		
		public function findCompanyById($id) {
			$xml = $this->getURL("/companies/$id.xml");
			$this->checkForErrors("Company");
			$xml_object = simplexml_load_string($xml);
			$company = new HighriseCompany($this);
			$company->loadFromXMLObject($xml_object);
			return $company;
		}


		public function findPersonById($id)
		{
			$xml = $this->getURL("/people/$id.xml");
			$this->checkForErrors("Person");
			$xml_object = simplexml_load_string($xml);
			$person = new HighrisePerson($this);
			$person->loadFromXMLObject($xml_object);
			return $person;
		}
		
		public function findAllTags()
		{
			$xml = $this->getUrl("/tags.xml");
			$this->checkForErrors("Tags");
			
			$xml_object = simplexml_load_string($xml);			
			$ret = array();
			foreach($xml_object->tag as $tag)
			{
				$ret[(string)$tag->name] = new HighriseTag((string)$tag->id, (string)$tag->name);
			}
			
			return $ret;
		}
		
		public function findAllPeople()
		{
			return $this->parseListing("/people.xml");	
		}
		
		public function findAllCompanies()
		{
			return $this->parseListing("/companies.xml", undef, "company");	
		}
		
		public function findPeopleByTagName($tag_name)
		{
			$tags = $this->findAllTags();
			foreach($tags as $tag)
			{
				if ($tag->name == $tag_name)
					$tag_id = $tag->id;
			}
			
			if (!isset($tag_id))
				throw new Excepcion("Tag $tag_name not found");
			
			return $this->findPeopleByTagId($tag_id);
		}
		
		public function findCompaniesByTagName($tag_name)
		{
			$tags = $this->findAllTags();
			foreach($tags as $tag)
			{
				if ($tag->name == $tag_name)
					$tag_id = $tag->id;
			}
			
			if (!isset($tag_id))
				throw new Excepcion("Tag $tag_name not found");
			
			return $this->findCompaniesByTagId($tag_id);
		}
		


		public function findPeopleByTagId($tag_id)
		{
			$url = "/people.xml?tag_id=" . $tag_id;
			$people = $this->parseListing($url);
			return $people;	
		}

		public function findCompaniesByTagId($tag_id)
		{
			$url = "/companies.xml?tag_id=" . $tag_id;
			$people = $this->parseListing($url);
			return $people;	
		}


		
		public function findPeopleByEmail($email)
		{
			return $this->findPeopleBySearchCriteria(array("email"=>$email));
		}
		
		public function findPeopleByTitle($title)
		{
			$url = "/people.xml?title=" . urlencode($title);
			
			$people = $this->parseListing($url);
			return $people;
		}

		public function findPeopleByCompanyId($company_id)
		{
			$url = "/companies/" . urlencode($company_id) . "/people.xml";
			$people = $this->parseListing($url);
			return $people;
		}

		public function findPeopleBySearchTerm($search_term)
		{
			$url = "/people/search.xml?term=" . urlencode($search_term);
			$people = $this->parseListing($url, 25);
			return $people;
		}
		
		public function findCompaniesBySearchTerm($search_term)
		{
			$url = "/companies/search.xml?term=" . urlencode($search_term);
			return $this->parseListing($url, 25, "company");
		}
		
		public function findPeopleBySearchCriteria($search_criteria)
		{
			$url = "/people/search.xml";
			
			$sep = "?";
			foreach($search_criteria as $criteria=>$value)
			{
				$url .= $sep . "criteria[" . urlencode($criteria) . "]=" . urlencode($value);
				$sep = "&";
			}
			
			return $this->parseListing($url, 25);
		}
		
		public function findCompaniesBySearchCriteria($search_criteria)
		{
			$url = "/companies/search.xml";
			
			$sep = "?";
			foreach($search_criteria as $criteria=>$value)
			{
				$url .= $sep . "criteria[" . urlencode($criteria) . "]=" . urlencode($value);
				$sep = "&";
			}
			
			return $this->parseListing($url, 25, "company");
		}
		
		public function findPeopleSinceTime($time)
		{
			$url = "/people/search.xml?since=" . urlencode($time);
			return $this->parseListing($url);
		}

		public function findCompaniesSinceTime($time)
		{
			$url = "/companies/search.xml?since=" . urlencode($time);
			return $this->parseListing($url, undef, "company");
		}

		public function parseListing($url, $paging_results = 500, $type = "person")
		{
			if (strstr($url, "?"))
				$sep = "&";
			else
				$sep = "?";

			if ($type == "person") {
				$error_type = "People";
			} elseif ($type == "company") {
				$error_type = "Company";
			} else {
				throw new Exception("invalid type in parseListing");
			}

			$offset = 0;
			$return = array();
			while(true) // pagination
			{
				$xml_url = $url . $sep . "n=$offset";
				// print $xml_url;
				$xml = $this->getUrl($xml_url);
				$this->checkForErrors($error_type);
				$xml_object = simplexml_load_string($xml);

				foreach($xml_object->$type as $xml_type_obj)
				{
					// print_r($xml_person);
					if ($type == "person") {
						$newobj = new HighrisePerson($this);
					} else {
						$newobj = new HighriseCompany($this);
					}
					$newobj->loadFromXMLObject($xml_type_obj);
					$return[] = $newobj;
				}
				
				if (count($xml_object) != $paging_results)
					break;
				
				$offset += $paging_results;
			}
			
			return $return;
		}

		public function findAllCustomfields()
		{
			$xml = $this->getUrl("/subject_fields.xml");
			$this->checkForErrors("Custom Fields");
			
			$xml_object = simplexml_load_string($xml);			
			$ret = array();
			foreach($xml_object->{'subject-field'} as $cf)
			{
				$ret[(string)$cf->label] = new HighriseCustomfield(null, null, (string)$cf->id, (string)$cf->label);
			}
			
			return $ret;
		}

        /* Groups */

        public function findAllGroups()
        {
            $xml = $this->getUrl("/groups.xml");
            $this->checkForErrors("Groups");

            $xml_object = simplexml_load_string($xml);

            $ret = array();
            foreach ($xml_object->group as $xml_group)
            {
                $group = new HighriseGroup();
                $group->loadFromXMLObject($xml_group);
                $ret[] = $group;
            }

            return $ret;
        }
		
	}
	
