<?php
class HighriseGroup
{
    public $id;
    public $name;
    public $users;


    public function loadFromXMLObject($xml_obj)
    {
        $this->setId($xml_obj->{'id'});
        $this->setName($xml_obj->{'name'});
        $this->setUsers($xml_obj->{'users'});

        return true;
    }

    public function setUsers($users)
    {
        $ret = array();
        foreach ($users->{'user'} as $xml_user)
        {
            $user = new HighriseUser();
            $user->loadFromXMLObject($xml_user);
            $ret[] = $user;
        }

        $this->users = $ret;
    }

    public function getUsers()
    {
        return $this->users;
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


}
	
