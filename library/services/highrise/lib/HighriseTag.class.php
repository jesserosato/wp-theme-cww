<?php
	
	class HighriseTag extends HighriseAPI
	{

		protected $_tag_type;
		protected $_hr;

		public $id;
		public $subject_id;
		public $name;
		public $deleted;
		
		public function __construct($id = null, $name = null, $type = null)
		{
			$this->setId($id);
			$this->setName($name);
			if (!empty($type)) {
				$this->setType($type);
			}
			
		}
		
		public function toXML()
		{

			$xml = new SimpleXMLElement("<tag></tag>");
			$xml->addChild("name",$this->getName());
			return $xml->asXML();
		}
		
		public function __toString()
		{
			return $this->name;
		}
		public function setHighrise(HighriseAPI $hr) {
			$this->_hr = $hr;
		}
		public function setName($name)
		{
			$this->name = (string)$name;
		}
		public function getName()
		{
			return $this->name;
		}

		public function setId($id)
		{
			$this->id = (string)$id;
		}

		public function getId()
		{
			return $this->id;
		}
		
		public function setSubjectId($id)
		{
			$this->subject_id = (string)$id;
		}

		public function getSubjectId()
		{
			return $this->subject_id;
		}

		public function setType($type) {

			switch ($type) {
				case 'Person':
					$this->_tag_type = 'people';
					break;
				case 'Company':
					$this->_tag_type = 'companies';
					break;
				case 'Kase':
					$this->_tag_type = 'kases';
					break;
				case 'Deal':
					$this->_tag_type = 'deals';
					break;
				default:
					throw new Exception("'$type' is not a valid status type. Available status names: Person, Company, Kase, Deal");
			};
		}
		
		public function save()
		{
			return $this->_hr->postDataWithVerb("/" . $this->_tag_type . "/" . $this->getSubjectId() . "/tags.xml", "<name>" . $this->getName() . "</name>", "POST");
		}

		public function delete($subject_id) {
			$this->postDataWithVerb("/" . $this->_tag_type . "/" . $subject_id . "/tags/" . $this->getId() . ".xml", "", "DELETE");
			$this->checkForErrors(ucwords($this->_tag_type), 200);
			$this->deleted = true;
		}
	}
	
