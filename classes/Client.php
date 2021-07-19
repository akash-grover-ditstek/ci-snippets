<?php

require_once(CLASSES_DIR . "User.php");
require_once(CLASSES_DIR . "Account.php");

class Client
{
    private $id;
    private $account_id;
    private $user_id;
    private $first_name;
    private $last_name;
    private $title;
    private $street_address;
    private $city;
    private $state;
    private $zipcode;
    private $place_id;
    private $company;
    private $phone_number;
    private $email_address;
    private $shared;
    private $last_updated;

    private $user;
    private $account;
    private $evaluations;

    public function __construct($data = array())
    {
        $this->user = new User();
        $this->account = new Account();
        $this->evaluations = array();

        foreach ($data as $key => $value) {
            $this->{$key} = $value;
        }
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setId($id)
    {
        $this->id = $id;
    }

    public function getName()
    {
        $name = [];

        if ($this->first_name) {
            $name[] = $this->first_name;
        }

        if ($this->last_name) {
            $name[] = $this->last_name;
        }

        return implode(' ', $name);
    }
    
    public function getFirstName()
    {
        return $this->first_name;
    }
    
    public function setFirstName($first_name)
    {
        $this->first_name = $first_name;
    }
    
    public function getLastName()
    {
        return $this->last_name;
    }
    
    public function setLastName($last_name)
    {
        $this->last_name = $last_name;
    }
    
    public function getTitle()
    {
        return $this->title;
    }
    
    public function setTitle($title)
    {
        $this->title = $title;
    }
    
    public function getStreetAddress()
    {
        return $this->street_address;
    }
    
    public function setStreetAddress($street_address)
    {
        $this->street_address = $street_address;
    }
    
    public function getCity()
    {
        return $this->city;
    }
    
    public function setCity($city)
    {
        $this->city = $city;
    }
    
    public function getState()
    {
        return $this->state;
    }
    
    public function setState($state)
    {
        $this->state = $state;
    }
    
    public function getZipcode()
    {
        return $this->zipcode;
    }
    
    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;
    }
    
    public function getCompany()
    {
        return $this->company;
    }
    
    public function setCompany($company)
    {
        $this->company = $company;
    }
    
    public function getShared()
    {
        return $this->shared;
    }
    
    public function setShared($shared)
    {
        $this->shared = $shared;
    }
    
    public function getAccountId()
    {
        return intval($this->account_id);
    }
    
    public function setAccountId($account_id)
    {
        $this->account_id = $account_id;
    }
    
    public function getUserId()
    {
        return intval($this->user_id);
    }
    
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }
    
    public function getPlaceId()
    {
        return $this->place_id;
    }
    
    public function setPlaceId($place_id)
    {
        $this->place_id = $place_id;
    }
    
    public function getPhoneNumber()
    {
        return $this->phone_number;
    }
    
    public function setPhoneNumber($phone_number)
    {
        $this->phone_number = $phone_number;
    }
    
    public function getEmailAddress()
    {
        return $this->email_address;
    }
    
    public function setEmailAddress($email_address)
    {
        $this->email_address = $email_address;
    }
    
    public function getLastUpdated()
    {
        return $this->last_updated;
    }
    
    public function setLastUpdated($last_updated)
    {
        $this->last_updated = $last_updated;
    }
    
    public function getUser()
    {
        return $this->user;
    }
    
    public function setUser(User $user)
    {
        $this->user = $user;
    }
    
    public function getAccount()
    {
        if ($this->account->getId()) {
            return $this->account;
        } else {
            $ci =& get_instance();
            $ci->load->model('account_model');
            $this->account = $ci->account_model->getObject($this->account_id);
            return $this->account;
        }
    }
    
    public function setAccount(Account $account)
    {
        $this->account = $account;
    }
    
    public function getEvaluations()
    {
        return $this->evaluations;
    }
    
    public function setEvaluations($evaluations)
    {
        $this->evaluations = $evaluations;
    }

    public function canBeEdited()
    {
        if ((is_any_granted([
                UserRole::ROLE_SUPER_ADMINISTRATOR,
                UserRole::ROLE_DEV
            ])) || (is_any_granted([UserRole::ROLE_ADMINISTRATOR]) && $this->getAccountId() === get_logged_user_account()) || (is_any_granted([UserRole::ROLE_USER])) && $this->getAccountId() === get_logged_user_account() && $this->getUserId() === get_logged_user_id()) {
            return true;
        }

        return false;
    }

    public function getFormattedAddress($type = null)
    {
        $result = array('street_address' => '', 'details' => '');

        if ($this->street_address) {
            $result['street_address'] = $this->street_address;
        }

        if ($this->city && $this->state) {
            $result['details'] = $this->city . ', ' . get_state($this->state) . ' ' . $this->zipcode;
        }

        if ($type && isset($result[$type])) {
            return $result[$type];
        } else {
            return $result;
        }
    }
}