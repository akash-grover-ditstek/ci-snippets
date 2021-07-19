<?php

require_once(CLASSES_DIR  . "Comparative.php");
require_once(CLASSES_DIR  . "SuiteParcel.php");
require_once(CLASSES_DIR  . "Zoning.php");

class Comps_model extends MY_Model {
    public $table;
    public $suite_parcel_table;
    public $zoning_table;

    public function __construct() {
        parent::__construct();

        $this->table = $this->db->dbprefix('comps');
        $this->suite_parcel_table = 'suite_parcel';
        $this->zoning_table = 'zoning';

        $this->dataTableFields = array(
            'last_updated',
            'street_address',
            'sale_price',
            'building_size',
            'land_size',
            'price_square_foot',
            'zone',
            'date_sold'
        );

        $this->join = array(
            array(
                'table' => 'zoning',
                'on'    => 'comps.id = zoning.comp_id',
                'type'  => 'left'
            )
        );

        $this->joinFields = $this->getFields();

        $this->groupConcat = array(
            'zone' => 'zoning',
            'sub_zone' => 'zoning'
        );

        $this->groupField = array('comps.id');

        $this->dataTableSearchableFields = array('street_address');

        $this->load->model('listings_model');
        $this->load->model('files_model');
    }

    public function get($id, $select = '*', $normalize = false) {
        if($id) {
            $res = $this->db->select($select)->where('id', $id)->get($this->table)->custom_result_object('Comparative');
            $res = $normalize ? $this->normalize($res) : $res;
            return reset($res);
        } else {
            $res = $this->db->select($select)->get($this->table)->custom_result_object('Comparative');
            $res = $normalize ? $this->normalize($res) : $res;
            return $res;
        }
    }

    public function put($data = array()) {
        $id = $data['id'];
        if (isset($data['land_type'])) {
            if ($data['land_type'] == 'Type My Own') {
                $data['land_type'] = isset($data['land_type_custom']) ? trim($data['land_type_custom']) : '';
            }
            unset($data['land_type_custom']);
        }

        $masterPropertyId = $this->getMasterProperty($data);

        if($masterPropertyId) {
            
            if($data['comp_type']=="building_with_land")
            {
                $data['land_type'] = null;
            }
            $update = $this->assemble($data,$masterPropertyId);
            if($this->db->where('id', $id)->update($this->table, $update)) {
                if($data['comp_type']=="building_with_land")
                {
                    if(isset($data['zonings'])) {
                        $zone=0;
                        $zonings = isset($data['zonings']) ? $data['zonings'] : null;
                        foreach ($zonings as $z) {
                          if (isset($data['zonings'][$zone]['sub_zone'])) {
                              if ($data['zonings'][$zone]['sub_zone'] == 'Type My Own') {
                                $data['zonings'][$zone]['sub_zone'] = isset($data['zonings'][$zone]['subzone_type_custom']) ? trim($data['zonings'][$zone]['subzone_type_custom']) : '';
                              }
                          }
                          unset($data['zonings'][$zone]['subzone_type_custom']);
                          $zone++;
                        }
                        $this->saveZonings($this->extractZonings($data), $id);
                    }
                }else{
                    $this->db->where('comp_id',$id);
                    $this->db->delete('zoning');
                }

                $this->addPropertyImage($data, $id);
                $comp = $this->comps_model->_findBy(['id' => $id]);
                $comp['update'] = true;

                return $comp;
            }
        }
        return 'failure';
    }

    public function post($data = array()) {
        if (isset($data['land_type'])) {
            if ($data['land_type'] == 'Type My Own') {
                $data['land_type'] = isset($data['land_type_custom']) ? trim($data['land_type_custom']) : '';
            }
            unset($data['land_type_custom']);
        }
        $masterPropertyId = $this->getMasterProperty($data);

        if($masterPropertyId) {
            if($data['comp_type']=="building_with_land")
            {
                $data['land_type'] = null;
            }
            $insert = $this->assemble($data, $masterPropertyId);
            if ($this->db->insert($this->table, $insert)) {
                $id = $this->db->insert_id();

                if($data['comp_type']=="building_with_land")
                {
                    if(isset($data['zonings'])) { 
                        $zone=0;
                        $zonings = isset($data['zonings']) ? $data['zonings'] : null;
                        foreach ($zonings as $z) {
                          if (isset($data['zonings'][$zone]['sub_zone'])) {
                              if ($data['zonings'][$zone]['sub_zone'] == 'Type My Own') {
                                $data['zonings'][$zone]['sub_zone'] = isset($data['zonings'][$zone]['subzone_type_custom']) ? trim($data['zonings'][$zone]['subzone_type_custom']) : '';
                              }
                          }
                          unset($data['zonings'][$zone]['subzone_type_custom']);
                          $zone++;
                        }
                        $this->saveZonings($this->extractZonings($data), $id);
                    }
                }else{
                    $this->db->where('comp_id',$id);
                    $this->db->delete('zoning');
                }
                $this->addPropertyImage($data, $id);

                $comp = $this->comps_model->_findBy(['id' => $id]);

                $comp['update'] = false;

                return $comp;
            }
        }

        return 'failure';
    }
    
    public function normalize($comps = array()) {

        if(is_array($comps) && isset($comps)) {
            foreach ($comps as $i => $comp) {
                $comps[$i]->suitesParcel = $this->findAllSuitesParcelsByCompId($comp->id);
                $comps[$i]->zonings = $this->findAllZoningsByCompIdForForm($comp->id);

                if ($comp->getBuildingSize()) {
                    $comps[$i]->building_size_for_view = maskArea($comp->getBuildingSize());

                }

                if ($comp->getLandSize()) {
                    if($comp->getLandDimension() == 'SF') {
                        $comps[$i]->land_size_for_view = maskArea($comp->getLandSize());
                    } else {
                        $comps[$i]->land_size_for_view = maskArea(($comp->getLandSize()*43560), 'SF', null, false);
                    }
                }

                $comps[$i]->land_dimension_for_view = $comp->getLandDimension();

                if ($comp->getSalePrice()) {
                    $comps[$i]->sale_price_for_view = maskMoney($comp->getSalePrice());
                }

                if ($comp->getFrontage()) {
                    $comps[$i]->frontage_for_view = $comp->getFrontage(true);
                }

                if ($comp->getPriceSquareFoot()) {
                    $comps[$i]->price_square_foot = $comp->getPriceSquareFoot();
                }

                if($comp->getLandDimension() == 'SF') {
                    $comps[$i]->price_square_foot_for_view = maskMoney($comp->getSalePrice()/$comp->getLandSize(false , true));
                }else{
                    $comps[$i]->price_square_foot_for_view = maskMoney($comp->getSalePrice()/($comp->getLandSize(false, true)*43560));
                }

                if ($comp->getCondition()) {
                    $comps[$i]->condition_for_view = get_conditions($comp->getCondition());
                    if(!isset( $comps[$i]->condition_for_view)){
                        $comps[$i]->condition_for_view = '';
                    }
                }

                if ($comp->getDateSold()) {
                    $comps[$i]->date_sold_for_view = formatDate('M j, Y', $comp->getDateSold());
                    $comps[$i]->date_sold_for_input = formatDate('m/d/Y', $comp->getDateSold());
                }

                if ($comp->getState()) {
                    $comps[$i]->state_for_view = strtoupper($comp->getState());
                }

                if ($comp->getUtilitiesSelect()) {
                    if($comp->getUtilitiesSelect() === 'other') {
                        $comps[$i]->utilities_for_view = $comp->getUtilitiesText();
                    } else {
                        $comps[$i]->utilities_for_view = $comp->getUtilitiesSelect();
                    }
                }
                if ($comp->getLeaseType()) {
                    $comps[$i]->lease_type = $comp->getLeaseType();
                }
                
                if ($comp->getLandType()) {
                    $land_type_custom = $comps[$i]->land_type;
                    $comps[$i]->land_type = $comp->getLandType();
                    if($comps[$i]->land_type=="Type My Own")
                    {
                        $comps[$i]->land_type_custom = $land_type_custom;
                    }else{
                        $comps[$i]->land_type_custom = "";
                    }
                }

            }
        }
        return $comps;
    }

    public function addPropertyImage($data, $id) {
        $col = 'property_image_url';
        $update = ['id' => $id];

        if(isset($data[$col]) && !empty($data[$col])) {
           $current = $this->db->select($col)->where('id', $id)->get($this->table)->first_row();


            $filename = $this->files_model->uploadToS3('property_image_url', 'comps', $id);

            if (!empty($filename)) {
                $update[$col] = $filename;
                if(is_object($current) && strval($current->{$col}) !== strval($filename)) {
                        $this->files_model->removeFromServer($current->{$col});
                }
            }

            $this->db->where('id', $id)->update($this->table, $update);
        }
    }

    public function saveSpaces($suites, $comp_id)
    {
        $this->addAssociation($suites, $comp_id, $this->suite_parcel_table);
    }

    public function deleteSpaces($listing_id)
    {
        $this->removeUnusedAssociation(array(), $listing_id, $this->suite_parcel_table);
    }


    public function saveZonings($zonings, $comp_id)
    {
        $this->addAssociation($zonings, $comp_id, $this->zoning_table);
    }

    public function removeZonings($comp_id)
    {
        $this->removeUnusedAssociation(array(), $comp_id, $this->zoning_table);
    }

    private function addAssociation($instanceList, $comp_id, $table)
    {
        $idsToKeep = array();
        if (is_array($instanceList) && !empty($instanceList) && !is_null($instanceList)) {
            foreach ($instanceList as $index => $data) {
                $data['comp_id'] = $comp_id;

                $instance = new stdClass();
                if ($table === $this->zoning_table) {
                    $instance = new Zoning($data);
                } else {
                    if ($table === $this->suite_parcel_table) {
                        $instance = new SuiteParcel($data);
                    }
                }

                if (($table === $this->zoning_table && $instance->getCompId() && $instance->getZone()) || ($table === $this->suite_parcel_table && $instance->getCompId() && $instance->getName() && $instance->getLeaseRate() && $instance->getSpaceSize())) {
                    if (!$instance->getId()) { 
                        $this->db->insert($table, $instance);
                        $idsToKeep[] = $this->db->insert_id();
                    } else { 
                        $this->db->where('id', $instance->getId());
                        $this->db->update($table, $instance);
                        $idsToKeep[] = $instance->getId();
                    }
                }
            }
        }

        $this->removeUnusedAssociation($idsToKeep, $comp_id, $table);
    }


    private function removeUnusedAssociation($idsToKeep, $comp_id, $table)
    {
        if (isset($comp_id)) {
            $query = $this->db->select('id')->from($table)->where('comp_id', $comp_id);

            if (isset($idsToKeep) && is_array($idsToKeep) && !empty($idsToKeep)) {
                $query = $query->where_not_in('id', $idsToKeep);
            }

            $instanceList = $query->get()->result_array();

            if (isset($instanceList) && is_array($instanceList) && !empty($instanceList) && !is_null($table)) {
                foreach ($instanceList as $instance) {
                    if (isset($instance['id'])) {
                        $this->db->delete($table, array('id' => $instance['id']));
                    }
                }
            }
        }
        return true;
    }

    public function assemble($data, $masterPropertyId = null) {
        $insert = [];
        foreach ($data as $column => $value) {
            if (array_key_exists($column, $this->getFields())) {
                $insert[$column] = $this->sanitize($column, $value);
            }
        }

        if($masterPropertyId) {
            $insert['property_id'] = $masterPropertyId;
        }

        unset($insert['property_image_url']);

        if (!isset($data['user_id'])) {
            $insert['user_id'] = $this->session->userdata('user_id');
        }

        if (!isset($data['account_id'])) {
            $insert['account_id'] = $this->session->userdata('account_id');
        }

        if(isset($data['zonings'])){
            unset($data['zonings']);
        }

        return $insert;
    }

    public function sanitize($column, $value) {
        if(is_array($value)) {
            return json_encode($value);
        } else if($column === 'date_sold') {
            if($value) {
                return date("Y-m-d", strtotime($value));
            } else {
                return null;
            }
        } else if($column === 'land_size'){
            return str_replace(",", "",$value);    
        }  else {
            return $value;
        }
    }

    public function getMasterProperty($data) {
        $zonings = isset($data['zonings'])? $data['zonings'] : null;

        $coordinates = [
            'lat' => $data['latitude'],
            'lng' => $data['longitude']
        ];

        $address = [
            'business_name' => $data['business_name'],
            'street_address' => $data['street_address'],
            'city' => $data['city'],
            'state' => $data['state'],
            'zipcode' => $data['zipcode'],
            'county' => isset($data['county'])? $data['county']:  null,
        ];

        list($isLand, $sf) = $this->getLandInformation($data, $zonings);

        if($isLand) {
            $coordinates = [
                'lat' => isset($data['map_pin_lat'])? $data['map_pin_lat'] : $data['latitude'],
                'lng' => isset($data['map_pin_lng']) ? $data['map_pin_lng'] : $data['longitude']
            ];
        }

        return $this->master->retrieve($address, $coordinates, $sf, $isLand);
    }

    public function getLandInformation($data, $zonings = null) {
        $zonings = isset($data['zonings'])? $data['zonings'] : null;

        return $this->listings_model->getLandInformation($data, $zonings);
    }

    public function findOneObjectBy($args = array()){
        $comparative = $this->findBy($args);

        if(isset($comparative['id'])){
            $comparative['suitesParcels'] = $this->findAllSuitesParcelsByCompId($comparative['id']);
            $comparative['zonings'] = $this->findAllZoningsByCompId($comparative['id']);
    
            return new Comparative($comparative);
        }

        return new Comparative();
    }

    public function removeAllBy($criteria = array()) {
        if(!empty($criteria)) {
            $comps = $this->findAllBy($criteria);

            foreach ($criteria as $property => $value) {
                $this->db->where($property, $value);
            }

            $query = null;

            if (!empty($criteria)) {
                $query = $this->db->delete($this->table);
            }

            if ($query) {
                foreach ($comps as $comp) {
                    if (isset($comp['property_image_url'])) {
                        S3::remove_file($comp['property_image_url']);
                    }
                }

                return true;
            }
        }
        return false;
    }

    public function removeFieldFromJson($args) {
        $id = $args['id'];
        $fieldValue = $args['field_value'];

        $comps = $this->get($id);

        if(isset($comps['exhibits'])){
            foreach($comps['exhibits'] as $key => $value){
                if(strpos($value, $fieldValue) !== false){
                    unset($comps['exhibits'][$key]);
                }
            }
        }

        return $comps['exhibits'];
    }

    public function _findBy($criteria = array()) {
        $comp = NULL;

        $this->db->select('*');
        $this->db->from($this->table);

        foreach ($criteria as $property => $value) {
            $this->db->where($property, $value);
        }

        $query = $this->db->get();

        $comp = $query->row_array();

        if (isset($comp['building_size'])) {
            $comp['building_size_for_view'] = maskArea($comp['building_size']);
        }

        if (isset($comp['land_size']) && isset($comp['land_dimension'])) {
            if($comp['land_dimension'] == 'SF') {
                $comp['land_size_for_view'] = maskArea($comp['land_size']);
            } else {
                $comp['land_size_for_view'] = maskArea($comp['land_size'], 'a.', null, false);
            }
        }

        if (isset($comp['sale_price'])) {
            $comp['sale_price_for_view'] = maskMoney($comp['sale_price']);
    }

        if (isset($comp['price_square_foot'])) {
            $comp['price_square_foot_for_view'] = maskMoney($comp['price_square_foot'], 2);
        }

        if (isset($comp['condition'])) {
            $comp['condition_for_view'] = get_conditions($comp['condition']);
        }

        if (isset($comp['date_sold'])) {
            $comp['date_sold_for_view'] = formatDate('m/d/Y', $comp['date_sold']);
            $comp['date_sold_for_text_view'] = formatDate('M j, Y', $comp['date_sold']);
        }

        if (isset($comp['state'])) {
            $comp['state_for_view'] = strtoupper($comp['state']);
        }

        if (isset($comp['zoning_type'])) {
            $comp['zone_type'] = $comp['zoning_type'];
        }

        $comp['zonings'] = $this->findAllZoningsByCompId($comp['id']);

        if(isset($comp['zonings']) && is_array($comp['zonings']) && !empty($comp['zonings'])) {
            $result = array();

            foreach ($comp['zonings'] as $zoning) {
                if ($zoning) {
                    $result[] = get_zonings($zoning->getZone());
                }
            }

            $result = implode(', ', $result);
            $result = !empty($result) ? $result : 'N/A';

            $comp['zoning'] = $result;
        }

        return $comp;
    }

    public function search($needle, $limit, $id) {
        $comp = NULL;

        $currentComp = $this->db->select('type')->where('id', $id)->get('comps')->first_row();

        $this->db->distinct();
        $this->db->select('comps.id, comps.street_address, comps.city, comps.state, comps.business_name, comps.property_image_url, comps.type, comps.date_sold');
        $this->db->where('comps.id !=', $id);

        if(isset($currentComp['type'])) {
            $type = $currentComp['type'];
            $this->db->group_start();
            $this->db->group_start();

            if($type === 'sale') {
                $this->db->where('comps.type', 'sale');
            } elseif($type === 'lease') {
                $this->db->where('comps.type', 'lease');
            } elseif($type === 'sale_and_lease') {
                $this->db->where('comps.type', 'sale');
                $this->db->where('comps.type', 'lease');
            }

            $this->db->group_end();

            $this->db->or_where('comps.type', null);

            $this->db->group_end();
        }

        $this->db->where('comps.date_sold is NOT NULL');
        $this->db->where('comps.sale_price > 0');
        $this->db->where('comps.type', 'sale');
        
        if(is_user(UserRole::ROLE_ADMINISTRATOR) || is_user(UserRole::ROLE_DATA_ENTRY)){
            $this->db->where('comps.account_id', $_SESSION['account_id']);
        } elseif(is_user(UserRole::ROLE_USER)){
            $this->db->where('comps.user_id', $_SESSION['user_id']);
        }

        $this->db->group_start();
        $this->db->like('comps.street_address', $needle, 'both');
        $this->db->or_like('comps.business_name', $needle, 'both');
        $this->db->group_end();

        $query = $this->db->get($this->table, $limit);

        $comps = $query->result();

        return $comps;
    }

    public function table_get($args = array(), $max = NULL, $offset = NULL, $order = array(), $permission = array(), $filter = array()){
        $data = array();

        $order = isset($order[0])?$order[0]:NULL;

        $comps = $this->table_findAllBy($args, $max, $offset, $order, $permission, $filter);

        foreach($comps as $index => $comp){
            array_push($data, $this->normalize_data($comp,$permission));
        }

        return $data;
    }

    public function getZones($zonings)
    {
        $zonings = explode(',', $zonings);

        if (isset($zonings) && is_array($zonings) && !empty($zonings)) {
            $result = get_zonings($zonings[0]);
            $result = !empty($result)? $result: 'N/A';
        } else {
            $result = 'N/A';
        }

        return $result;
    }
    
    public function addPropertyBoundaries($comp, $type = 'new')
    {
        $map_image_url = null;
        $map_pin_image_url = null;
        $map_image_for_report_url = null;

        if ($map_image_url || $map_pin_image_url || $map_image_for_report_url) {
            $data = ['id' => $comp->getId()];

            if ($map_image_url) {
                $data['map_image_url'] = $map_image_url;
            }

            if ($map_image_for_report_url) {
                $data['map_image_for_report_url'] = $map_image_for_report_url;
            }

            if ($map_pin_image_url) {
                $data['map_pin_image_url'] = $map_pin_image_url;
            }

            $this->save($data);
        }
    }
    
    public function normalize_data($comp,$type=null)
    {
        $building_size = null;
        if (isset($comp['building_size']) && $comp['building_size'] > 0) {
            $building_size = $comp['building_size'];
            $comp['building_size'] = maskArea($comp['building_size']);
        } else {
            $comp['building_size'] = '--';
        }

        if(is_null($building_size) && isset($comp['land_size'])) {
            $building_size = $comp['land_size'];
        }

        $sale_price = null;
        if (isset($comp['sale_price']) && $comp['sale_price'] > 0) {
            $sale_price = $comp['sale_price'];
            $comp['sale_price'] = maskMoney($comp['sale_price']);
        } else {
            if(isset($comp['lease_rate']) && $comp['lease_rate'] > 0) {
                $sale_price = $comp['lease_rate'];
                $comp['sale_price'] = maskMoney($comp['lease_rate']);
            } else {
                $comp['sale_price'] = '--';
            }
        }

        if (isset($comp['price_square_foot']) && $comp['price_square_foot'] > 0) {
            if (isset($comp['type']) && $comp['type'] =='sale') {
                $comp['price_square_foot'] = maskMoney($comp['price_square_foot']);
            }else{
                $comp['price_square_foot'] = maskMoney($comp['price_square_foot']);
            }
        } else {
            $comp['price_square_foot'] = '--';
            if(isset($sale_price) && is_number($sale_price)) {
                if(isset($building_size) && is_number($building_size)) {
                    if($building_size==0)
                    {
                        $comp['price_square_foot'] = '--';
                        $building_size=1;
                    } else {
                        $comp['price_square_foot'] = maskMoney($sale_price/$building_size);
                    }
                }
            }
        }

        if (isset($comp['zone']) && !empty($comp['zone'])) {
            $comp['zone'] = $this->getZones($comp['zone']);
        } 
        else {
            if(isset($comp['comp_type']) && !empty($comp['comp_type']))
            {
                if($comp['comp_type']=="land_only")
                {
                    $comp['zone'] = "Land";
                }else{
                    $comp['zone'] = get_comp_type($comp['comp_type']);
                }
            }
        }

        if (isset($comp['condition'])) {
            $comp['condition'] = get_conditions($comp['condition']);
            if(!isset($comp['condition'])){
                $comp['condition'] = '--';
            }
        } else {
            $comp['condition'] = '--';
        }

        if (isset($comp['date_sold'])) {
            $comp['date_sold'] = formatDate('n/j/Y', $comp['date_sold']);
        } else {
            $comp['date_sold'] = '--';
        }

        if (!isset($comp['last_updated'])) {
            $comp['last_updated'] = '--';
        }

        if (isset($comp['lease_type'])) {
            $comp['lease_type'] = get_lease_types($comp['lease_type']);
            if(!isset($comp['lease_type'])){
                $comp['lease_type'] = '--';
            }
        } else {
            $comp['lease_type'] = '--';
        }

        $street_address = isset($comp['street_address'])
            ? $comp['street_address']
            : $comp['business_name'];

        if($type['type']=="lease")
        {
            return array(
                "DT_RowId" => $comp['id'],
                "masterId" => $comp['property_id'],
                "canAdmin" => $this->canViewMasterProperties(),
                'last_updated' => $comp['last_updated'],
                'street_address' => "<a onclick='CompsController.openForm({$comp['id']})' data-activates='slide-out' class='button-collapse'>{$street_address}</a>",
                'sale_price' => $comp['sale_price'],
                'lease_type' => $comp['lease_type'],
                'term' => $comp['term'],
                'building_size' => $comp['building_size'],
                'zone' => $comp['zone'],
            );
        }else{
            return array(
                "DT_RowId" => $comp['id'],
                "masterId" => $comp['property_id'],
                "canAdmin" => $this->canViewMasterProperties(),
                'last_updated' => $comp['last_updated'],
                'street_address' => "<a onclick='CompsController.openForm({$comp['id']})' data-activates='slide-out' class='button-collapse'>{$street_address}</a>",
                'sale_price' => $comp['sale_price'],
                'building_size' => $comp['building_size'],
                'land_size' => maskAreaNumber($comp['land_size']),
                'price_square_foot' => $comp['price_square_foot'],
                'zone' => $comp['zone'],
                'date_sold' => $comp['date_sold']
            );
        }
    }

    public function canViewMasterProperties()
    {
        return is_user(UserRole::ROLE_SUPER_ADMINISTRATOR) || is_user(UserRole::ROLE_DEV);
    }
    
    public function findAllSuitesParcelsByCompId($comp_id){
        $query =  $this->db->where('comp_id', $comp_id)->get('suite_parcel');

        $list = $query->custom_result_object('SuiteParcel');

        return empty($list)? array() : $list;
    }
    
    public function findAllZoningsByCompId($comp_id){
        $query =  $this->db->where('comp_id', $comp_id)->get('zoning');
        $list = $query->custom_result_object('Zoning');
        return empty($list)? array() : $list;
    }

    public function findAllZoningsByCompIdForForm($comp_id){
        $query =  $this->db->where('comp_id', $comp_id)->get('zoning');
        $list = $query->custom_result_object('Zoning');
        if($list)
        {
            foreach($list as $l)
            {
                if (isset($l->sub_zone)) {
                    $l->subzone_type_custom = $l->getSubZoneCustom($l->zone);
                    $l->sub_zone = $l->getSubZone($l->zone);
                }
            }
        }
        return empty($list)? array() : $list;
    }

    public function getFields() {
        return array(
            'id' => 'comps',
            'property_id' => 'comps',
            'user_id' => 'comps',
            'account_id' => 'comps',
            'business_name' => 'comps',
            'street_address' => 'comps',
            'street_suite' => 'comps',
            'city' => 'comps',
            'county' => 'comps',
            'state' => 'comps',
            'zipcode' => 'comps',
            'type' => 'comps',
            'property_image_url' => 'comps',
            'condition' => 'comps',
            'property_class' => 'comps',
            'zoning_type' => 'comps',
            'year_built' => 'comps',
            'year_remodeled' => 'comps',
            'sale_price' => 'comps',
            'price_square_foot' => 'comps',
            'lease_rate' => 'comps',
            'term' => 'comps',
            'concessions' => 'comps',
            'building_size' => 'comps',
            'land_size' => 'comps',
            'land_dimension' => 'comps',
            'date_sold' => 'comps',
            'frontage' => 'comps',
            'utilities_select' => 'comps',
            'utilities_text' => 'comps',
            'map_pin_lat' => 'comps',
            'map_pin_lng' => 'comps',
            'map_pin_zoom' => 'comps',
            'latitude' => 'comps',
            'longitude' => 'comps',
            'summary' => 'comps',
            'created' => 'comps',
            'last_updated' => 'comps',
            'net_operating_income' => 'comps',
            'cap_rate' => 'comps',
            'zone' => 'zoning',
            'sub_zone' => 'zoning',
            'lease_type' => 'comps',
            'comp_type' => 'comps',
            'land_type' => 'comps'
        );
    }
    
    private function extractZonings($data)
    {
        $zonings = isset($data['zonings']) ? $data['zonings'] : array();
        return $zonings;
    }
}