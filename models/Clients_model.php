<?php

require_once(CLASSES_DIR  . "User.php");
require_once(CLASSES_DIR  . "Account.php");
require_once(CLASSES_DIR  . "Client.php");

class Clients_model extends MY_Model {

    public function __construct()
    {
        parent::__construct();

        $this->load->model('account_model');
        $this->load->model('evaluations_model');
        $this->table = 'clients';


        $this->join = array(
            array(
                'table' => 'accounts',
                'on' => 'clients.account_id = accounts.id'
            )
        );

        $this->joinFields = array(
            'id' => 'clients',
            'account_id' => 'clients',
            'user_id' => 'clients',
            'first_name' => 'clients',
            'last_name' => 'clients',
            'title' => 'clients',
            'street_address' => 'clients',
            'city' => 'clients',
            'state' => 'clients',
            'zipcode' => 'clients',
            'place_id' => 'clients',
            'company' => 'clients',
            'phone_number' => 'clients',
            'email_address' => 'clients',
            'last_updated' => 'clients',
            'shared' => 'clients',
            'share_clients' => 'accounts'
        );

        $this->dataTableSearchableFields = array(
            'last_name',
            'first_name',
            'company',
            'email_address'
        );

        $this->dataTableFields = array(
            'first_name',
            'company'
        );

    }
    
    public function findAllObjects($args = array(), $dependencies = false){

        $args = $this->getPermissions($args);

        $clients = array();

        $user_clients = $this->findAllBy($args, null, null, null, true);

        $shared_clients = $this->findAllBy([
            'share_clients' => true,
            'shared' => true,
            'account_id' => [
                'not' => $this->session->userdata('account_id')
            ]
        ], null, null, null, true);

        foreach(array_merge($user_clients, $shared_clients) as $index => $client) {
            if($dependencies) {
                $client['evaluations'] = $this->evaluations_model->getObjects(['client_id' => $client['id']]);

                if (is_any_granted([UserRole::ROLE_SUPER_ADMINISTRATOR, UserRole::ROLE_DEV])) {
                    $client['account'] = $this->account_model->getObject($client['account_id']);
                }
            }

            $clients[] = new Client($client);
        }

        return $clients;
    }
    
    public function findOneObjectBy($args = array(), $is_show = false){

        $args = $this->getPermissions($args);

        $client = $this->findBy($args);

        if(isset($client['id'])){
            return new Client($client);
        } else {
            if($is_show) {
                if(isset($args['account_id'])){
                    unset($args['account_id']);
                }

                $args['share_clients'] = true;
                $args['account_id'] = array('not' => $this->session->userdata('account_id'));

                $client = $this->findBy($args);

                if (isset($client['id'])) {
                    return new Client($client);
                }
            }
        }

        return new Client();
    }
    
    public function findSharedClientObjectBy($args = array()){
        $args['share_clients'] = true;
        $args['account_id'] = array('not' => $this->session->userdata('account_id'));

        $client = $this->findBy($args);

        if(isset($client['id'])){
            return new Client($client);
        }

        return new Client();
    }
    
    public function create_list_table()
    {
        $account = $this->account_model->get_single_account($this->session->account_id);

        $client_list = array();
        $this->db->select('*');
        
        if ($_SESSION['account_id'] != 1) {
            $this->db->where('account_id', $_SESSION['account_id']);
        }
        $allow_sharing = isset($account->settings->share_clients);
        
        if ($_SESSION['role'] != 1 && !$allow_sharing) {
            $this->db->where('user_id', $_SESSION['user_id']);
        }

        $this->db->from($this->table);

        $query = $this->db->get();

        foreach ($query->result() as $row)
        {
            array_push($client_list, $row);
        }

        return $client_list;
    }
    
    public function get_single_client($id) {
        $this->db->select('*')
            ->where('id', $id)
            ->from('clients');

        $client_row = $this->db->get()->row();

        return $client_row;
    }
    
    public function delete_single_client($id) {
        $your_id = $_SESSION['user_id'];
        $your_account = $_SESSION['account_id'];
        $your_role = $_SESSION['role'];

        $this->db->select('account_id, user_id')
            ->where('id', $id)
            ->from('clients');

        $belong_ids = $this->db->get()->row();

        if ($belong_ids->user_id == $your_id || ($your_role == 1 && $your_account == $belong_ids->account_id) || $your_account == 1)
            $this->db->delete('clients', array('id' => $id));
        else {
            return 'cannot delete entry';
        }
    }
    
    public function search($needle, $limit) {
        $client = NULL;

        $this->db->select('*');
        $this->db->like('business_name', $needle, 'both');
        $this->db->or_like('contact_name', $needle, 'both');
        $this->db->or_like('email_address', $needle, 'both');

        $query = $this->db->get($this->table, $limit);

        $clients = $query->result();

        return $clients;
    }
    
    public function table_get($args = array(), $max = NULL, $offset = NULL, $order = array()){
        $data = array();

        $order = isset($order[0])?$order[0]:NULL;

        $comps = $this->table_findAllBy($args, $max, $offset, $order, $this->clients_model->getPermissions());

        foreach($comps as $index => $comp){
            array_push($data, $this->normalize_data($comp));
        }

        return $data;
    }
    
    public function normalize_data($data)
    {
        $client = new Client($data);

        return array(
            "DT_RowId" => $client->getId(),
            'last_updated' => $client->getLastUpdated(),
            'name' => $this->getName($client),
            'company' => $client->getCompany(),
            'evals' => count($client->getEvaluations()),
            'buttons' => $this->getButtons($client)
        );
    }
    
    public function getName($client) {
        $name = [];

        if($client->getFirstName()) {
            $name[] = $client->getFirstName();
        }


        if($client->getLastName()) {
            $name[] = $client->getLastName();
        }


        return '<a href="' . base_url('clients/'. ($client->canBeEdited()? 'edit' : 'show') . '/' . $client->getId()) . '">' .  implode(' ', $name) . '</a>';
    }
    
    public function getButtons($client) {
        $button = '';

        $button .= '<a href="' . base_url('clients/'. ($client->canBeEdited()? 'edit' : 'show') . '/' . $client->getId()) . '"  class="tooltipped" data-position="bottom" data-delay="50" data-tooltip="Edit">' .
            '<img class="edit" src="' . base_url('assets/img/visibility-button.png') . '"/>' .
        '</a>';

       if($client->canBeEdited()) {
           $msg = 'Are you sure you want to continue?';
           $dialog = "return confirm('" . $msg . "')";
           $button .= '<a onclick="' . $dialog . '" href="' . base_url('clients/delete/' . $client->getId()) . '"  class="tooltipped" data-position="bottom" data-delay="50" data-tooltip="Delete"> ' .
                        '<img class="delete" src="' . base_url('assets/img/delete.png') . '"/>' .
                            '</a>';
       }

       return $button;
    }
    
    public function getPermissions($args = array()){

        if(is_user(UserRole::ROLE_ADMINISTRATOR)){
            $args['account_id'] = get_logged_user_account();
        } else if(is_user(UserRole::ROLE_ADMINISTRATOR) || is_user(UserRole::ROLE_USER) || is_user(UserRole::ROLE_DATA_ENTRY)){
            $args['account_id'] = get_logged_user_account();
        }

        return $args;
    }
}