<?php
require_once(ENUM_DIR . "FileOrigin.php");
require_once(ENUM_DIR . "PropertyStatus.php");
require_once(CLASSES_DIR . "Listing.php");
require_once(CLASSES_DIR . "SuiteParcel.php");

class Listings_model extends MY_Model
{
    private $suite_parcel_table;
    private $zoning_table;

    public function __construct()
    {
        parent::__construct();

        $this->table = $this->db->dbprefix('listings');
        $this->suite_parcel_table = $this->db->dbprefix('suite_parcel');
        $this->zoning_table = $this->db->dbprefix('zoning');

        $this->searchableByKeyword = array();

        $this->join = array(
            array(
                'table' => 'zoning',
                'on' => 'listings.id = zoning.listing_id',
                'type' => 'left'
            ),
            array(
                'table' => 'secondary_brokers',
                'on' => 'listings.id = secondary_brokers.listing_id',
                'type' => 'left'
            )
        );

        $this->joinFields = $this->getFields();

        $this->dataTableFields = array(
            'last_updated',
            'business_name',
            'street_address',
            'zone',
            'asking_price',
            'created',
            'expiration_date'
        );

        $this->dataTableSearchableFields = array(
            'business_name',
            'street_address',
            'zone',
            'status'
        );

        $this->groupConcat = array(
            'zone' => 'zoning',
            'sub_zone' => 'zoning'
        );

        $this->groupField = array('listings.id');

        $this->load->model('comps_model');
        $this->load->model('zoning_model');
        $this->load->model('files_model');
    }

    public function get($id, $select = '*') {
        if($id) {
            $res = $this->db->select($select)->where('id', $id)->get($this->table)->custom_result_object('Listing');

            return reset($res);
        } else {
            $res = $this->db->select($select)->get($this->table)->custom_result_object('Listing');
            return $res;
        }
    }

    public function post($data = array()) {
        $masterPropertyId=null;
        if(isset($data['map_pin_lat']) && isset($data['map_pin_lng']) && !empty($data['map_pin_lat']) && !empty($data['map_pin_lng'])) {
            $masterPropertyId = $this->getMasterProperty($data);
        }

        $message = [
            'status' => 'fail',
            'message' => 'Try again later, or contact Project CRE support.'
        ];

        $spaces = isset($data['suites_parcels']) ? $data['suites_parcels'] : null;
        $zonings = isset($data['zonings']) ? $data['zonings'] : null;
        $secondary_brokers = isset($data['secondary_brokers']) ? $data['secondary_brokers'] : null;

        if(isset($data['step']) && strval($data['step']) === 'overview') {
            if (!isset($data['listing']['has_suite_parcel']) ||
                (isset($data['listing']['has_suite_parcel']) && empty($data['listing']['has_suite_parcel']))) {
                $data['listing']['has_suite_parcel'] = false;
            }
        }

        $insert = $this->assemble($data['listing'], $masterPropertyId);

        if($insert) {
            $this->db->insert($this->table, $insert);
            $id = $this->db->insert_id();

            if($id) {
                $listing = $this->db->where('id', $id)->get($this->table)->custom_result_object('Listing');

                $this->listings_model->addSuitesParcels($spaces, $id);
                $this->listings_model->addZonings($zonings, $id);
                $this->listings_model->addPropertyBoundaries($listing[0]);
                $this->listings_model->addSecondaryBrokers($id, $secondary_brokers);
                $this->listings_model->addImages($data, $id);

                $message = ['status' => 'success', 'id' => $id];
            }
        }

        return $message;
    }
    
    public function put($data = array()) {
        $masterPropertyId=null;
        if(isset($data['map_pin_lat']) && isset($data['map_pin_lng']) && !empty($data['map_pin_lat']) && !empty($data['map_pin_lng'])) {
            $masterPropertyId = $this->getMasterProperty($data);
        }
        $message = [
            'status' => 'fail',
            'message' => 'Try again later, or contact Project CRE support.'
        ];
        
        $spaces = isset($data['suites_parcels']) ? $data['suites_parcels'] : null;
        $zonings = isset($data['zonings']) ? $data['zonings'] : null;
        $secondary_brokers = isset($data['secondary_brokers']) ? $data['secondary_brokers'] : null;

        if(isset($data['step']) && strval($data['step']) === 'overview') {
            if (!isset($data['listing']['has_suite_parcel']) ||
                (isset($data['listing']['has_suite_parcel']) && empty($data['listing']['has_suite_parcel']))) {
                $data['listing']['has_suite_parcel'] = false;
            }
        }
        
        $insert = $this->assemble($data['listing'], $masterPropertyId);

        if($insert) {
            $this->db->where('id', $data['listing']['id'])->update($this->table, $insert);

            if($data['listing']['id']) {
                $id = $data['listing']['id'];

                $listing = $this->db->where('id', $id)->get($this->table)->custom_result_object('Listing');

                if(strval($data['step']) === 'overview') {
                    if (!isset($spaces) || (isset($spaces) && empty($spaces))) {
                        $this->listings_model->removeSuitesParcels($id);
                    } else {
                        $this->listings_model->addSuitesParcels($spaces, $id);
                    }
                    if (!isset($zonings) || (isset($zonings) && empty($zonings))) {
                        $this->listings_model->removeZonings($id);
                    } else {
                        $this->listings_model->addZonings($zonings, $id);
                    }

                    $this->listings_model->addSecondaryBrokers($id, $secondary_brokers);
                    $this->listings_model->addImages($data, $id);
                }

                $this->listings_model->addPropertyBoundaries($listing[0]);

                $message = ['status' => 'success', 'id' => $id];

                $this->createCompIfClosed($data, $listing[0]);
            }
        }

        return $message;
    }

    public function createCompIfClosed($data, $listing) {
        if(($listing->getStatus() == PropertyStatus::CLOSED || $listing->getStatus() == PropertyStatus::OFF_MARKET) && isset($data['comp']) && $listing->getId()) {
            $comp = $data['comp'];
            $listingId = $listing->getId();
            $listing->setId(null);

            $comparative = new Comparative(json_decode(json_encode($listing), true));
            $comparative->setType($listing->getListingType());
            $comparative->setSalePrice(isset($comp['sale_price']) ? $comp['sale_price'] : null);
            $comparative->setLeaseRate(isset($comp['lease_rate']) ? $comp['lease_rate'] : null);
            $comparative->setPriceSquareFoot(isset($comp['price_square_foot']) ? $comp['price_square_foot'] : null);
            $comparative->setTerm(isset($comp['term']) ? $comp['term'] : null);
            $comparative->setConcessions(isset($comp['concessions']) ? $comp['concessions'] : null);

            unset($comparative->no_stories);
            unset($comparative->building_size_for_view);
            unset($comparative->land_size_for_view);
            unset($comparative->sale_price_for_view);
            unset($comparative->price_square_foot_for_view);
            unset($comparative->frontage_for_view);
            unset($comparative->condition_for_view);
            unset($comparative->date_sold_for_view);
            unset($comparative->state_for_view);
            unset($comparative->date_sold_for_input);
            unset($comparative->utilities_for_view);

            $this->db->insert('comps', $comparative);
            $comp_id = $this->db->insert_id();
            
            if($comp_id) {
                $spaces = $this->db->where('listing_id', $listingId)->get('suite_parcel')->result_object();
                foreach ($spaces as $space) {
                    if (isset($space->id)) {
                        $space->id = null;
                        $space->comp_id = $comp_id;
                        $space->created = 'now()';
                        $space->listing_id = null;
                        $this->db->insert('suite_parcel', $space);
                    }
                }

                $zones = $this->db->where('listing_id', $listingId)->get('zoning')->result_object();
                foreach ($zones as $zone) {
                    if (isset($zone->id)) {
                        $zone->id = null;
                        $zone->comp_id = $comp_id;
                        $zone->listing_id = null;
                        $zone->created = 'now()';
                        $this->db->insert('zoning', $zone);
                    }
                }
            }
        }
    }

    public function assemble($data, $masterPropertyId = null) {
        $insert = [];

        foreach ($data as $column => $value) {
            if (array_key_exists($column, $this->getFields())) {
                $insert[$column] = $this->sanitize($column, $value);
            }
        }

        if(isset($data['listing_type']) && $data['listing_type'] === 'lease') {
            $insert['asking_price'] = null;
        } elseif (isset($data['listing_type']) && $data['listing_type'] === 'sale') {
            $insert['asking_price_psf'] = null;
        }

        if($masterPropertyId) {
            $insert['property_id'] = $masterPropertyId;
        }

        return $insert;
    }

    public function sanitize($column, $value) {
        if(is_array($value)) {
            return json_encode($value);
        } else if($column === 'expiration_date') {
            if($value) {
                return formatDateForDatabase($value);
            } else {
                return null;
            }
        } else if($column === 'asking_price') {
            if(intval($value) === 0) {
                return null;
            } else {
                return $value;
            }
        } else if($column === 'asking_price_psf') {
            if(intval($value) === 0) {
                return null;
            } else {
                return $value;
            }
        } else {
            return $value;
        }
    }

    public function getMasterProperty($data = array()) {
        $listing = isset($data['listing'])? $data['listing'] : null ;
        $zonings = isset($data['zonings'])? $data['zonings'] : null;
        $spaces = isset($data['suites_parcels']) ? $data['suites_parcels'] : null;

        $coordinates = [
            'lat' => $listing['latitude'],
            'lng' => $listing['longitude']
        ];

        $address = [
            'business_name' => $listing['business_name'],
            'street_address' => $listing['street_address'],
            'city' => $listing['city'],
            'state' => $listing['state'],
            'zipcode' => $listing['zipcode'],
            'county' => isset($listing['county'])? $listing['county']:  null,
        ];

        list($isLand, $sf) = $this->getLandInformation($listing, $zonings, $spaces);

        if($isLand) {
            $coordinates = [
                'lat' => isset($listing['map_pin_lat'])? $listing['map_pin_lat']: $listing['latitude'],
                'lng' => isset($listing['map_pin_lng'])? $listing['map_pin_lng']:$listing['longitude']
            ];
        }

        return $this->master->retrieve($address, $coordinates, $sf, $isLand);
    }

    public function getLandInformation($listing = array(), $zonings = null, $parcels = null) {
        $isLand =  false;

        $landParcelSize = null;
        if($parcels) {
            foreach ($parcels as $parcel) {
                if(isset($parcel['type'])) {
                    if('parcel' === strval($parcel['type'])) {
                        if(!$landParcelSize) $landParcelSize = 0;
                        $landParcelSize += isset($parcel['dimension']) && $parcel['dimension'] === 'SF'? $parcel['space_size'] : $parcel['space_size'] * 43560;
                    }
                }
            }
        }

        $zoningSize = null;
        if($zonings) {
            foreach ($zonings as $zoning) {
                if(isset($zoning['zone'])) {
                    if ('land' === strval($zoning['zone'])) {
                        if (!$zoningSize) {
                            $zoningSize = 0;
                        }
                        $zoningSize += isset($zoning['sq_ft']) ?: 0;
                        $isLand = true;
                        break;
                    }
                }
            }
        }

        $landSize = null;
        if(!$isLand) {
            if(isset($listing['land_size']) && !isset($listing['building_size'])) {
                $isLand = true;

                if($landSize === 0) {
                    if(is_numeric($listing['land_dimension'])) {
                        if(!$landSize) $landSize = 0;
                        $landSize += isset($listing['land_dimension']) && strtolower($listing['land_dimension']) === 'sf'? $listing['land_size'] : $listing['land_size'] * 43560;
                    }
                }
            }
        }

        if($landParcelSize) {
            return [$isLand, $landParcelSize];
        } elseif($zoningSize) {
            return [$isLand, $zoningSize];
        } elseif($landSize) {
            return [$isLand, $landSize];
        }

        return [$isLand, false];
    }

    public function getParcelTotal($parcels) {
        $total = 0;
        if($parcels) {
            foreach ($parcels as $parcel) {
                if('PARCEL' === strval($parcel['type'])) {
                    $size = isset($parcel['space_size'])? : 0;

                    if(isset($parcel['dimension'])) {
                        if($parcel['dimension'] === 'ACRE') {
                            $size = 43560*$parcel;
                        }
                    }

                    $total += $size;
                }
            }
        }

        return $total;
    }

    public function findOneObjectBy($args = array(), $use_join = false)
    {
        $args = $this->getPermissions($args);

        $property = $this->findBy($args, $use_join);

        if (isset($property['id'])) {
            return new Listing($property, true);
        }

        return new Listing();
    }

    public function findAllObjectsBy($args = array(), $use_join = false, $limit = null, $offset = null, $order = null)
    {
        $args = $this->getPermissions($args);
        $propertiesObj = [];
        $properties = $this->findAllBy($args, $limit, $offset, $order, $use_join);

        foreach ($properties as $property) {
            if (isset($property['id'])) {

                $propertiesObj[] = new Listing($property, true);
            }
        }
        return $propertiesObj;
    }

    public function findAllSuitesParcelsByListingId($listing_id){
        if($listing_id) {
            $query = $this->db->where('listing_id', $listing_id)->get('suite_parcel');

            $list = $query->custom_result_object('SuiteParcel');

            return empty($list) ? array() : $list;
        } else {
            return array();
        }
    }

    public function findAllZoningsByListingId($listing_id){
        if($listing_id) {
            $query = $this->db->where('listing_id', $listing_id)->get('zoning');

            $list = $query->custom_result_object('Zoning');

            return empty($list) ? array() : $list;
        } else {
            return array();
        }
    }

    public function table_get($args = array(), $max = null, $offset = null, $order = array(), $permission = array(), $filter = null, $user_filter = array()) {
        $data = array();

        $order = isset($order[0]) ? $order[0] : null;
        $orderByFields = ['status' => ['AVAILABLE', 'UNDER_CONTRACT', 'OFF_MARKET', 'CLOSED']];

        $properties = $this->table_findAllBy($args, $max, $offset, $order, $permission, $filter, true, $orderByFields, null,
            $user_filter);

        foreach ($properties as $index => $property) {
            array_push($data, $this->normalize_data($property));
        }

        return $data;
    }

    public function normalize_data($property)
    {
        
        return array(
            "DT_RowId" => $property['id'],
            "masterId" => $property['property_id'],
            "canEdit" => $this->canEditDataTable($property),
            "canAdmin" => $this->canViewMasterProperties(),
            "available" => $property['status'] !== PropertyStatus::CLOSED && $property['status'] !== PropertyStatus::OFF_MARKET,
            'last_updated' => isset($property['last_updated']) ? $property['last_updated'] : '--',
            'business_name' => isset($property['business_name']) ? $property['business_name'] : (isset($property['street_address']) ? $property['street_address'] : 'Address Not Disclosed'),
            'street_address' => isset($property['street_address']) ? $property['street_address'] : 'Address Not Disclosed',
            'zone' => isset($property['zone']) ? $this->getZones($property['zone']) : 'N/A',
            'asking_price' => $this->get_asking_price($property),
            'created' => isset($property['created']) ? formatDate('n/j/Y', $property['created']) : '--',
            'expiration_date' => isset($property['expiration_date']) ? formatDate('n/j/Y',
                $property['expiration_date']) : '--'
        );
    }

    public function removeAllBy($criteria = array())
    {
        $properties = $this->findAllBy($criteria);

        foreach ($criteria as $property => $value) {
            $this->db->where($property, $value);
        }

        $query = null;

        if (!empty($criteria)) {
            $query = $this->db->delete($this->table);
        }

        if ($query) {
            foreach ($properties as $property) {
                $files = $this->files_model->findAllBy(['entity_type' => 'properties', 'entity_id' => $property['id']]);

                foreach ($files as $file) {
                    $this->files_model->delete(['id' => $file['id']]);
                    $this->files_model->deleteFileFromServerOrLocal($file['dir']);
                }
            }

            return true;
        }

        return false;
    }

    public function canEditDataTable($property)
    {
        if (is_user(UserRole::ROLE_SUPER_ADMINISTRATOR) || is_user(UserRole::ROLE_DEV)) {
            return true;
        } else {
            if (is_user(UserRole::ROLE_DATA_ENTRY) || (is_user(UserRole::ROLE_ADMINISTRATOR) && is_account_administrator(get_logged_user_account()))) {
                return true;
            } else {
                if (is_user(UserRole::ROLE_USER)) {
                    if (intval(get_logged_user_id()) === intval($property['user_id'])) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function canViewMasterProperties()
    {
        return is_user(UserRole::ROLE_SUPER_ADMINISTRATOR) || is_user(UserRole::ROLE_DEV);
    }

    public function getZones($zonings)
    {
        $zonings = explode(',', $zonings);

        if (isset($zonings) && is_array($zonings) && !empty($zonings)) {
            $result = array();

            foreach ($zonings as $zoning) {
                if ($zoning) {
                    $result[] = get_zonings($zoning);
                }
            }

            $result = implode(', ', $result);
            $result = !empty($result) ? $result : 'N/A';
        } else {
            $result = 'N/A';
        }

        return $result;
    }

    public function get_asking_price($property)
    {
        if (isset($property['comp_id'])) {
            $suites_parcels = $this->comps_model->findAllSuitesParcelsByCompId($property['comp_id']);
        }

        $lease_price = null;
        if (isset($suites_parcels) && !empty($suites_parcels) && is_array($suites_parcels)) {
            usort($suites_parcels, function ($a, $b) {
                return $a->lease_rate - $b->lease_rate;
            });

            if (isset($suites_parcels[0]) && isset($suites_parcels[count($suites_parcels) - 1])) {
                if ($suites_parcels[0]->getLeaseRate() !== $suites_parcels[count($suites_parcels) - 1]->getLeaseRate()) {
                    $lease_price = maskMoney($suites_parcels[0]->getLeaseRate()) . get_lease_rate_units($suites_parcels[0]->getLeaseRateUnits()) . ' - ' . maskMoney($suites_parcels[count($suites_parcels) - 1]->getLeaseRate()) . get_lease_rate_units($suites_parcels[0]->getLeaseRateUnits());
                } else {
                    $lease_price = maskMoney($suites_parcels[0]->getLeaseRate()) . get_lease_rate_units($suites_parcels[0]->getLeaseRateUnits());
                }
            }
        }

        if (isset($property['listing_type']) && strtolower($property['listing_type']) == 'sale' && isset($property['asking_price'])) {
            return maskMoney($property['asking_price']);
        } elseif (isset($property['listing_type']) && strtolower($property['listing_type']) == 'lease' && isset($property['asking_price_psf'])) {
            if (isset($property['payment_frequency']) && map_frequency($property['payment_frequency'])) {
                return maskMoney($property['asking_price_psf']) . ' ' . map_frequency($property['payment_frequency']);
            } else {
                return maskMoney($property['asking_price_psf']) . ' SF/Mo';
            }
        } elseif (isset($property['listing_type']) && strtolower($property['listing_type']) == 'sale_and_lease' && isset($property['asking_price_psf'])) {
            if (isset($property['payment_frequency']) && map_frequency($property['payment_frequency'])) {
                return maskMoney($property['asking_price_psf']) . ' ' . map_frequency($property['payment_frequency']);
            } else {
                return maskMoney($property['asking_price_psf']) . ' SF/Mo';
            }
        } else {
            if (isset($lease_price)) {
                return $lease_price;
            }
        }

        return 'Price Not Disclosed';
    }

    public function duplicate($id = null)
    {
        if (isset($id)) {
            $property = $this->findBy(array('id' => $id));

            foreach ($property as $key => $val) {
                if ($key != 'id' && $key != 'created' && $key != 'last_updated') {
                    $this->db->set($key, $val);
                }
            }

            $this->db->insert($this->table);

            return array(
                'status' => 'success',
                'message' => 'Property has been duplicated.',
                'id' => $this->db->insert_id()
            );
        } else {
            return array('status' => 'fail', 'message' => 'It was not possible to perform that action at the moment.');
        }
    }

    public function saveFiles($id, $fieldToSave, $files, $file_order = array())
    {

        $order = array();

        if (isset($file_order) && !empty($file_order)) {
            foreach ($file_order as $index => $value) {
                $decoded = json_decode($value, true);
                if (isset($decoded['name'])) {
                    $order[$decoded['name']] = $decoded['index'];
                } else {
                    $order[$decoded['id']] = $decoded['index'];
                }
            }
        }

        $uploaded = array();

        foreach ($files as $inputName => $file) {
            if (isset($files[$inputName]['name'])) {
                $res = $this->files_model->addToServer($file, 'properties', $id);

                if (isset($res['fileId'])) {
                    array_push($uploaded,
                        array('id' => $res['fileId'], 'index' => $order[$file['name']], 'path' => $res['dir']));
                    log_message('debug', "The file " . basename($file["name"]) . " has been uploaded.");
                } else {
                    log_message('debug', "Sorry, there was an error uploading your file.");
                }
            }
        }

        if (count($order) > 0) {
            $entity = $this->findBy(['id' => $id]);

            $data = array();

            if (isset($entity[$fieldToSave])) {
                $files_uploaded = $entity[$fieldToSave];

                foreach ($files_uploaded as $index => $file_info) {
                    if (isset($order[$file_info['id']])) {
                        $file_info['index'] = $order[$file_info['id']];
                        array_push($data, $file_info);
                    }
                }
            }

            if (isset($uploaded) && !empty($uploaded)) {
                foreach ($uploaded as $index => $file_info) {
                    array_push($data, $file_info);
                }
            }

            return $this->hardSave(array(
                'id' => $id,
                $fieldToSave => $data
            ));
        }


    }

    public function getFileDir($filename, $extension)
    {
        $folder = makeDir("upload/" . $this->session->userdata('account_id'));

        $upload_path = $folder ? "upload/" . $this->session->userdata('account_id') . "/" : "upload/";

        $newFilename = $filename . '_' . date("Ymd") . '_' . uniqid() . '.' . $extension;

        $dir = $upload_path . $newFilename;

        return $dir;
    }

    public function getPermissions($args = array())
    {
        if(is_user(UserRole::ROLE_ADMINISTRATOR) || is_user(UserRole::ROLE_DATA_ENTRY)) {
            $args['account_id'] = get_logged_user_account();
        } elseif(is_user(UserRole::ROLE_USER)) {
            $args['user_id'] = get_logged_user_id();
        }

        return $args;
    }

    public function addZonings($zonings, $comp_id)
    {
        $this->_addAssociation($zonings, $comp_id, $this->zoning_table);
    }

    public function removeZonings($comp_id)
    {
        $this->_removeUnusedAssociation(array(), $comp_id, $this->zoning_table);
    }

    public function addSuitesParcels($suites, $comp_id)
    {
        $this->_addAssociation($suites, $comp_id, $this->suite_parcel_table);
    }

    public function removeSuitesParcels($listing_id)
    {
        $this->_removeUnusedAssociation(array(), $listing_id, $this->suite_parcel_table);
    }

    private function _addAssociation($instanceList, $comp_id, $table)
    {
        $idsToKeep = array();
        if (is_array($instanceList) && !empty($instanceList) && !is_null($instanceList)) {
            foreach ($instanceList as $index => $data) {
                $data['listing_id'] = $comp_id;

                $instance = new stdClass();
                if ($table === $this->zoning_table) {
                    $instance = new Zoning($data);
                } else {
                    if ($table === $this->suite_parcel_table) {
                        $instance = new SuiteParcel($data);
                    }
                }

                if (($table === $this->zoning_table && $instance->getListingId() && $instance->getZone()) || ($table === $this->suite_parcel_table && $instance->getListingId() && $instance->getName() && $instance->getLeaseRate() && $instance->getSpaceSize())) {
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

        $this->_removeUnusedAssociation($idsToKeep, $comp_id, $table);
    }

    public function addSecondaryBrokers($property_id, $brokers = array())
    {
        $this->load->model('secondary_brokers_model');

        $this->secondary_brokers_model->delete(['listing_id' => $property_id]);

        if (is_array($brokers)) {
            foreach ($brokers as $i => $id) {
                $this->secondary_brokers_model->save(['listing_id' => $property_id, 'user_id' => $id]);
            }
        }
    }

    private function _removeUnusedAssociation($idsToKeep, $comp_id, $table)
    {
        if (isset($comp_id)) {
            $query = $this->db->select('id')->from($table)->where('listing_id', $comp_id);

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

    public function canEdit($id)
    {
        if (get_logged_user_role() === UserRole::ROLE_SUPER_ADMINISTRATOR) {
            return true;
        } else {
            $property = $this->findBy(['id' => $id, 'account_id' => $this->session->userdata('account_id')], false);

            if (isset($property['id'])) {
                return true;
            }
        }

        return false;
    }
    
    private function addPropertyBoundaries($listing, $type = 'new')
    {
        $map_image_url = null;

        if ($listing->getMapImageUrl() && filter_var($listing->getMapImageUrl(), FILTER_VALIDATE_URL)) {
            $res = $this->files_model->addToServerFromStaticMap($listing->getMapImageUrl(), $listing->getId(), 'properties', 'map_image_url','png');

            if (isset($res['dir'])) {
                if ($listing->getMapImageUrl() && $type !== 'new') {
                    $this->files_model->removeFromServer($listing->getMapImageUrl());
                }
                $map_image_url = $res['dir'];
            }
        }

        if($map_image_url) {
            $this->listings_model->save([
                'id' => $listing->getId(),
                'map_image_url' => $map_image_url
            ]);
        }
    }

    private function addImages($data, $id) {
        if(isset($data['file_order'])) {
            unset($_FILES['files']);
            $this->listings_model->saveFiles($id, 'uploaded_images', $_FILES, $data['file_order']);
        }

    }

    public function getFields() {
        return [
            'id' => 'listings',
            'property_id' => 'listings',
            'buildout_property_id' => 'listings',
            'user_id' => 'listings',
            'account_id' => 'listings',
            'listing_type' => 'listings',
            'asking_price' => 'listings',
            'asking_price_psf' => 'listings',
            'lease_type' => 'listings',
            'payment_frequency' => 'listings',
            'date_listed' => 'listings',
            'business_name' => 'listings',
            'street_address' => 'listings',
            'street_suite' => 'listings',
            'city' => 'listings',
            'county' => 'listings',
            'state' => 'listings',
            'zipcode' => 'listings',
            'condition' => 'listings',
            'property_class' => 'listings',
            'year_built' => 'listings',
            'year_remodeled' => 'listings',
            'building_size' => 'listings',
            'land_size' => 'listings',
            'land_dimension' => 'listings',
            'no_stories' => 'listings',
            'parcel_id_apn' => 'listings',
            'has_suite_parcel' => 'listings',
            'owner_of_record' => 'listings',
            'property_geocode' => 'listings',
            'property_legal' => 'listings',
            'topography' => 'listings',
            'frontage' => 'listings',
            'front_feet' => 'listings',
            'lot_depth' => 'listings',
            'utilities_select' => 'listings',
            'utilities_text' => 'listings',
            'zoning_type' => 'listings',
            'zoning_description' => 'listings',
            'height' => 'listings',
            'main_structure_base' => 'listings',
            'foundation' => 'listings',
            'parking' => 'listings',
            'basement' => 'listings',
            'ada_compliance' => 'listings',
            'exterior' => 'listings',
            'roof' => 'listings',
            'electrical' => 'listings',
            'plumbing' => 'listings',
            'heating_cooling' => 'listings',
            'windows' => 'listings',
            'additional_feature' => 'listings',
            'map_image_url' => 'listings',
            'map_zoom' => 'listings',
            'map_selected_area' => 'listings',
            'map_pin_lat' => 'listings',
            'map_pin_lng' => 'listings',
            'map_pin_zoom' => 'listings',
            'latitude' => 'listings',
            'longitude' => 'listings',
            'google_place_id' => 'listings',
            'expiration_date' => 'listings',
            'uploaded_images' => 'listings',
            'description' => 'listings',
            'notes' => 'listings',
            'status' => 'listings',
            'zone' => 'zoning',
            'sub_zone' => 'zoning',
            'created' => 'listings',
            'last_updated' => 'listings',
            'lot_shape' => 'listings'
        ];
    }
}