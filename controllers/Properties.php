<?php

require_once(APPPATH. 'models/enum/UserRole.php');
require_once(APPPATH . 'models/enum/FlashMessageType.php');
require_once(CLASSES_DIR  . "Listing.php");
require_once(CLASSES_DIR  . "Comparative.php");
require_once(ENUM_DIR  . "SettingsType.php");

class Properties extends MY_Controller {
    private $head = array();
    private $title;

    public function __construct($is_secure = TRUE) {
        parent::__construct($is_secure);

        if(!get_logged_user_account()) {
            redirect('/users');
        }

        $this->head['classname'] = $this->router->fetch_class();
        $this->title = 'Properties';
        $this->load->model('comps_model');
        $this->load->model('listings_model');
        $this->load->model('settings_model');
        $this->load->library('listing_generator');
    }
    
    public function index() {
        $data['import_status'] = $this->settings_model->findObjectBy(['type' => SettingsType::PROPERTIES_IMPORT]);

        $this->load->view('_partials/_head.php', $this->head);
        $this->load->view('_common/header.php');
        $this->load->view('properties/index.php', $data);
        $this->load->view('_common/footer.php');
    }

    public function overview($id = null) {
        $data = array();
        $data['step'] = 'overview';
        $data['next'] = 'attachments';

        $data['listing'] = new Listing();
        $data['brokers'] = [];

        if($id){
            $data['listing'] = $this->listings_model->findOneObjectBy(['id' => $id]);

            $this->permissions($data['listing']);

            if($data['listing']->getId()) {
                if ($data['listing']->getUploadedImages()) {
                    $uploaded_images = $data['listing']->getUploadedImages();
                    usort($uploaded_images, function ($item1, $item2) {
                        if ($item1['index'] == $item2['index']) return 0;
                        return $item1['index'] < $item2['index'] ? -1 : 1;
                    });
                    $data['listing']->setUploadedImages($uploaded_images);
                }

                $data['brokers'] = $data['listing']->getSecondaryBrokers(true);
            } else {
                flash_message(FlashMessageType::NO_PERMISSION);
                redirect('properties');
            }
        }

        if(!$data['listing']->getAccountId() && !$data['listing']->getUserId()) {
            $data['listing']->setAccountId(get_logged_user_account());
            $data['listing']->setUserId(get_logged_user_id());
        }

        if(!$data['listing']->getAccountId() && !$data['listing']->getUserId()) {
            $data['listing']->setAccountId(get_logged_user_account());
            $data['listing']->setUserId(get_logged_user_id());
        }

        $this->load->view('_partials/_head.php', $this->head);
        $this->load->view('_common/header.php');
        $this->load->view('properties/overview/overview.php', $data);
        $this->load->view('_common/footer.php');
    }

    public function attachments($id = null){
        $data = array();
        $data['previous'] = 'overview';
        $data['step'] = 'attachments';
        $data['next'] = 'property-boundaries';

        $data['listing'] = new Listing();

        if($id){
            $data['listing'] = $this->listings_model->findOneObjectBy(['id' => $id]);

            $this->permissions($data['listing']);

            if($data['listing']->getId()) {
            } else {
                flash_message(FlashMessageType::NO_PERMISSION);
                redirect('properties');
            }
        }

        if(!$data['listing']->getAccountId() && !$data['listing']->getUserId()) {
            $data['listing']->setAccountId(get_logged_user_account());
            $data['listing']->setUserId(get_logged_user_id());
        }

        if(!$data['listing']->getAccountId() && !$data['listing']->getComparative()->getUserId()) {
            $data['listing']->setAccountId(get_logged_user_account());
            $data['listing']->setUserId(get_logged_user_id());
        }

        $this->load->view('_partials/_head.php', $this->head);
        $this->load->view('_common/header.php');
        $this->load->view('properties/attachments/index.php', $data);
        $this->load->view('_common/footer.php');
    }

    public function property_boundaries($id = null) {
        $data = array();
        $data['step'] = 'property-boundaries';
        $data['previous'] = 'attachments';

        $data['listing'] = new Listing();

        if($id){
            $data['listing'] = $this->listings_model->findOneObjectBy(['id' => $id]);

            $this->permissions($data['listing']);

            if(!$data['listing']->getId()){
                flash_message(FlashMessageType::NO_PERMISSION);
                redirect('properties');
            }
        } else {
            redirect('properties/overview');
        }

        $this->load->view('_partials/_head.php', $this->head);
        $this->load->view('_common/header.php');
        $this->load->view('properties/propertyBoundaries.php', $data);
        $this->load->view('_common/footer.php');

    }

    public function duplicate() {
        echo json_encode($this->listings_model->duplicate($this->input->post('propertyId')));
    }

    public function table_get() {
        $max = $this->input->get('length');
        $offset = $this->input->get('start');
        $order = $this->input->get('order');
        $args = $this->input->get('search');
        $broker = $this->input->get('broker');

        $permission = $this->listings_model->getPermissions();
        
        if(isset($args['value'])) {
            if(strtolower($args['value']) === 'under contract') {
                $args['value'] = 'UNDER_CONTRACT';
            } elseif(strtolower($args['value']) === 'off market') {
                $args['value'] = 'OFF_MARKET';
            } elseif(strtolower($args['value']) === 'closed') {
                $args['value'] = 'CLOSED';
            } elseif(strtolower($args['value']) === 'available') {
                $args['value'] = 'AVAILABLE';
            }
        }

        $broker_filter = $this->_checkUserFilterByAccount();

        if($broker) {
            $broker_filter = array('user_id' => $broker, 'secondary_brokers.user_id' => $broker);
        }

        $propertiesTotal = $this->listings_model->table_countAllBy(NULL, $permission);
        $propertiesList = $this->listings_model->table_get($args, $max, $offset, $order, $permission, [], $broker_filter);
        $propertiesTotalFiltered = isset($args) && !empty($args)? $this->listings_model->table_countAllBy($args,$permission, [], true, null, $broker_filter): $propertiesTotal;

        $data['recordsTotal'] = $propertiesTotal;
        $data['recordsFiltered'] = $propertiesTotalFiltered;
        $data['data'] = $propertiesList;

        echo json_encode($data);
    }
    
    private function _checkUserFilterByAccount() {
        if (get_logged_user_role() === UserRole::ROLE_USER) {

            $ci =& get_instance();
            $ci->load->model('account_model');
            if ((boolean) $ci->account_model->get_single_account(get_logged_user_account())->share_clients) {
                return null;
            }
            return array('listings.user_id' => get_logged_user_id(), 'secondary_brokers.user_id' => get_logged_user_id());
        }
        return null;
    }

    public function get() {
        if (!empty($this->input->get('id'))) {
            $comp = $this->listings_model->get($this->input->get('id'));
            if ($comp) {
                echo json_encode($comp);
            }
        } else {
            echo json_encode('failure');
        }
    }
    
    public function save() {
        $data = $this->input->post();
        if((isset($data['listing']['map_pin_lat']) && isset($data['listing']['map_pin_lng'])) && (empty($data['listing']['map_pin_lat']) || empty($data['listing']['map_pin_lng'])))
        {
            $message = [
                'status' => 'fail',
                'message' => 'Your location does not map correctly. Please enter a valid address or place the map pin manually.'
            ];
            echo json_encode($message);die;
        }
        if(isset($data['secondary_broker']) && $data['secondary_broker']) {
            $data['secondary_brokers'] = explode(',', $data['secondary_broker']);
        }
        if (isset($data['listing']['topography'])) {
            if ($data['listing']['topography'] == 'Type My Own') {
                $data['listing']['topography'] =
                    isset($data['listing']['topography_custom']) ? trim($data['listing']['topography_custom']) : '';
            }
            unset($data['listing']['topography_custom']);
        }
        if (isset($data['listing']['lot_shape'])) {
            if ($data['listing']['lot_shape'] == 'Type My Own') {
                $data['listing']['lot_shape'] =
                    isset($data['listing']['lot_shape_custom']) ? trim($data['listing']['lot_shape_custom']) : '';
            }
            unset($data['listing']['lot_shape_custom']);
        }
        if (isset($data['listing'])) {
            if($data['listing']['id']) {
                $res = $this->listings_model->put($data);
                echo json_encode($res);
            } else {
                $res = $this->listings_model->post($data);
                echo json_encode($res);
            }
        }
    }

    public function delete(){
        $this->load->model('listings_model');


        $data = $this->input->post();

        if(isset($data['id'])) {
            $this->permissions($this->listings_model->findOneObjectBy(['id' => $data['id']]));
        }

        if($this->listings_model->delete($data)) {
            if(isset($data['id'])) {
                $this->files_model->removeAllByPropertyId($data['id'], [FileOrigin::BUILDOUT_DOCUMENTS, FileOrigin::BUILDOUT_PHOTOS, FileOrigin::PROPERTIES_ATTACHMENTS, FileOrigin::PROPERTIES_IMAGES]);
            }
        }

        redirect('properties');
    }

    public function getDetails() {
        $this->load->model('mls_model');
        $this->load->model('users_model');

        $data['property'] = $this->mls_model->findListingBy(['id' => $this->input->get('id')], true);

        echo json_encode($data);
    }
    
    public function permissions($property) {
        if(is_user(UserRole::ROLE_SUPER_ADMINISTRATOR) || is_user(UserRole::ROLE_DEV)) {
            return true;
        } else if(is_user(UserRole::ROLE_DATA_ENTRY) || (is_user(UserRole::ROLE_ADMINISTRATOR) && is_account_administrator(get_logged_user_account()))){
            return true;
        } else if(is_user(UserRole::ROLE_USER)){
            if(intval(get_logged_user_id()) === intval($property->getUserId())){
                return true;
            }
        }
        redirect('properties');
    }

    public function clean() {
        $properties = $this->listings_model->findAllBy();
        $propertiesToRemove = [];

        foreach ($properties as $property) {
            if(!$this->comps_model->findById($property['comp_id'])) {
                $propertiesToRemove[] = $property['id'];
            }
        }

        foreach($propertiesToRemove as $id) {
            $this->listings_model->delete(['id' => $id]);
        }

        echo json_encode($propertiesToRemove);
    }

    public function export() {
        $this->listing_generator->export();
    }

    public function import() {
        $this->load->view('_partials/_head.php', $this->head);
        $this->load->view('_common/header.php');
        $this->load->view('properties/import/index.php');
        $this->load->view('_common/footer.php');
    }

    public function runImport() {
        $file = $_FILES['file'];
        $iuid = $this->input->post('guid');

        $_SESSION[$iuid] = 0;

        $res = $this->listing_generator->import($file, $iuid);

        echo json_encode($res);
    }

    public function getImportProgress() {
        $iuid = $this->input->get('guid');

        if(isset($_SESSION[$iuid])) {
            echo $_SESSION[$iuid];
        } else {
            echo json_encode(100);
        }
    }
}