<?php

defined('BASEPATH') or exit('No direct script access allowed');

require_once(CLASSES_DIR  . "Evaluation.php");
require_once(CLASSES_DIR  . "Comparative.php");
require_once(ENUM_DIR  . "EvaluationImageKey.php");

class Evaluations_model extends MY_Model
{
    private $table_comps;
    private $table_income_approach;
    private $table_sales_approach;
    private $table_cost_approach;

    public function __construct()
    {
        parent::__construct();

        $this->suite_parcel_table = 'suite_parcel';
        $this->zoning_table = 'zoning';
        $this->table = $this->db->dbprefix('evaluations');
        $this->table_comps = $this->db->dbprefix('comps');
        $this->table_income_approach = $this->db->dbprefix('evaluation_income_approaches');
        $this->table_sales_approach = $this->db->dbprefix('evaluation_sales_approaches');
        $this->table_cost_approach = $this->db->dbprefix('evaluation_cost_approaches');

        $this->join = array(
            array(
                'table' => 'clients',
                'on' => 'clients.id = evaluations.client_id'
            ),
            array(
                'table' => 'zoning',
                'on'    => 'evaluations.id = zoning.evaluation_id',
                'type'  => 'left'
            )
        );

        $this->groupConcat = array(
            'zone' => 'zoning',
            'sub_zone' => 'zoning'
        );


        $this->groupField = array('evaluations.id');

        $this->joinFields = $this->getFields();

        $this->load->model('comps_model');
        $this->load->model('evaluations_model');
        $this->load->model('evaluations_metadata_model');
        $this->load->model('wggenerator_model');
        $this->load->model('suites_model');
        $this->load->model('zoning_model');
        $this->load->model('income_model');
        $this->load->model('sales_model');
        $this->load->model('cost_model');
    }

    public function get($id)
    {
        $comp = NULL;

        $this->db->select('*');

        $this->db->from($this->table);

        $this->db->where('id', $id);

        $query = $this->db->get();

        return $this->normalize($query->row_array());
    }

    public function normalize($data)
    {
        if (isset($data['id'])) {
            $propertyImage = $this->evaluations_metadata_model->get(EvaluationImageKey::COVER, $data['id']);
            if (isset($propertyImage) && !empty($propertyImage)) {
                $data['photo'] = $propertyImage->value;
            }
        }

        return $data;
    }
    
    public function post($data = array())
    {
        $masterPropertyId = $this->getMasterProperty($data);

        $message = [
            'status' => 'fail',
            'message' => 'Try again later, or contact Project CRE support.'
        ];

        $spaces = isset($data['suites_parcels']) ? $data['suites_parcels'] : null;

        if (isset($data['step']) && strval($data['step']) === 'overview') {
            if (
                !isset($data['listing']['has_suite_parcel']) ||
                (isset($data['listing']['has_suite_parcel']) && empty($data['listing']['has_suite_parcel']))
            ) {
                $data['listing']['has_suite_parcel'] = false;
            }
        }

        list($data, $evaluationData) = $this->isChangingData($data);

        $insert = $this->assemble($data['evaluation'], $masterPropertyId);

        if ($insert) {
            $this->db->insert($this->table, $insert);
            $evalId = $this->db->insert_id();

            if ($evalId) {
                $eval = $this->db->where('id', $evalId)->get($this->table)->custom_result_object('Evaluation');

                $this->saveSpaces($spaces, $evalId);
                $zonings = isset($data['zonings']) ? $data['zonings'] : [];
                $this->saveZonings($zonings, $evalId);
                $this->saveEvaluationData($evaluationData, $evalId);
                if ($this->isUpdatingMap($insert)) {
                    $this->addPropertyBoundaries($eval[0], null);
                }

                if ($evalId) {
                    list($incomeId, $salesId, $costId) = $this->createApproaches($evalId);

                    $message = [
                        'status' => 'success',
                        'id' => $evalId,
                        'income_id' => $incomeId,
                        'sales_id' => $salesId,
                        'cost_id' => $costId,
                    ];
                }
            }
        }

        return $message;
    }
    
    public function put($data = array())
    {
        $message = [
            'status' => 'fail',
            'message' => 'Try again later, or contact Project CRE support.'
        ];

        $spaces = isset($data['suites_parcels']) ? $data['suites_parcels'] : null;
        $zonings = isset($data['zonings']) ? $data['zonings'] : null;

        if (isset($data['step']) && strval($data['step']) === 'overview') {
            if (
                !isset($data['listing']['has_suite_parcel']) ||
                (isset($data['listing']['has_suite_parcel']) && empty($data['listing']['has_suite_parcel']))
            ) {
                $data['listing']['has_suite_parcel'] = false;
            }
        }
        
        if (isset($data['evaluation']['topography'])) {
            if ($data['evaluation']['topography'] == 'Type My Own') {
                $data['evaluation']['topography'] =
                    isset($data['evaluation']['topography_custom']) ? trim($data['evaluation']['topography_custom']) : '';
            }
            unset($data['evaluation']['topography_custom']);
        }
        if (isset($data['evaluation']['lot_shape'])) {
            if ($data['evaluation']['lot_shape'] == 'Type My Own') {
                $data['evaluation']['lot_shape'] =
                    isset($data['evaluation']['lot_shape_custom']) ? trim($data['evaluation']['lot_shape_custom']) : '';
            }
            unset($data['evaluation']['lot_shape_custom']);
        }
        if (isset($data['evaluation']['most_likely_owner_user'])) {
            if ($data['evaluation']['most_likely_owner_user'] == 'Type My Own') {
                $data['evaluation']['most_likely_owner_user'] =
                    isset($data['evaluation']['most_likely_owner_user_custom']) ? trim($data['evaluation']['most_likely_owner_user_custom']) : '';
            }
        }
        if (isset($data['evaluation']['ada_compliance'])) {
            if ($data['evaluation']['ada_compliance'] == 'Type My Own') {
                $data['evaluation']['ada_compliance'] =
                    isset($data['evaluation']['ada_compliance_custom']) ? trim($data['evaluation']['ada_compliance_custom']) : '';
            }
        }
        if (isset($data['evaluation']['main_structure_base'])) {
            if ($data['evaluation']['main_structure_base'] == 'Type My Own') {
                $data['evaluation']['main_structure_base'] = isset($data['evaluation']['main_structure_base_custom']) ? trim($data['evaluation']['main_structure_base_custom']) : '';
            }
        }
        if (isset($data['evaluation']['foundation'])) {
            if ($data['evaluation']['foundation'] == 'Type My Own') {
                $data['evaluation']['foundation'] = isset($data['evaluation']['foundation_custom']) ? trim($data['evaluation']['foundation_custom']) : '';
            }
        }
        if (isset($data['evaluation']['parking'])) {
            if ($data['evaluation']['parking'] == 'Type My Own') {
                $data['evaluation']['parking'] = isset($data['evaluation']['parking_custom']) ? trim($data['evaluation']['parking_custom']) : '';
            }
        }
        if (isset($data['evaluation']['basement'])) {
            if ($data['evaluation']['basement'] == 'Type My Own') {
                $data['evaluation']['basement'] = isset($data['evaluation']['basement_custom']) ? trim($data['evaluation']['basement_custom']) : '';
            }
        }
        if (isset($data['evaluation']['roof'])) {
            if ($data['evaluation']['roof'] == 'Type My Own') {
                $data['evaluation']['roof'] = isset($data['evaluation']['roof_custom']) ? trim($data['evaluation']['roof_custom']) : '';
            }
        }
        if (isset($data['evaluation']['electrical'])) {
            if ($data['evaluation']['electrical'] == 'Type My Own') {
                $data['evaluation']['electrical'] = isset($data['evaluation']['electrical_custom']) ? trim($data['evaluation']['electrical_custom']) : '';
            }
        }
        if (isset($data['evaluation']['plumbing'])) {
            if ($data['evaluation']['plumbing'] == 'Type My Own') {
                $data['evaluation']['plumbing'] = isset($data['evaluation']['plumbing_custom']) ? trim($data['evaluation']['plumbing_custom']) : '';
            }
        }
        if (isset($data['evaluation']['heating_cooling'])) {
            if ($data['evaluation']['heating_cooling'] == 'Type My Own') {
                $data['evaluation']['heating_cooling'] = isset($data['evaluation']['heating_cooling_custom']) ? trim($data['evaluation']['heating_cooling_custom']) : '';
            }
        }
        if (isset($data['evaluation']['windows'])) {
            if ($data['evaluation']['windows'] == 'Type My Own') {
                $data['evaluation']['windows'] = isset($data['evaluation']['windows_custom']) ? trim($data['evaluation']['windows_custom']) : '';
            }
        }
        if (isset($data['evaluation']['exterior'])) {
            if ($data['evaluation']['exterior'] == 'Type My Own') {
                $data['evaluation']['exterior'] = isset($data['evaluation']['exterior_custom']) ? trim($data['evaluation']['exterior_custom']) : '';
            }
        }
        list($data, $evaluationData) = $this->isChangingData($data);
        
        if(isset($data['zonings'])){   
            $property_sum =0;      
            foreach ($zonings as $zoning) { 
              if(isset($zoning['sq_ft']) && !empty($zoning['sq_ft'])){
                $property_sum=$property_sum+$zoning['sq_ft'];             
              }
            }
            $data['evaluation']['building_size'] = $property_sum;
        }
        
        if(isset($data['evaluation']['street_address']))
        {
            $masterPropertyId = $this->getMasterProperty($data);
            $insert = $this->assemble($data['evaluation'], $masterPropertyId);
        }else{
            $insert = $this->assemble($data['evaluation']);
        }
       
        if ($insert) {
            $this->db->where('id', $data['evaluation']['id'])->update($this->table, $insert);

            if ($data['evaluation']['id']) {
                $id = $data['evaluation']['id'];

                $eval = $this->db->where('id', $id)->get($this->table)->custom_result_object('Evaluation');

                $data = $this->updateRelationalEntities($data, $spaces, $id, $zonings);

                $this->saveEvaluationData($evaluationData, $id);
                $this->saveImages($data, $id);

                if ($this->isUpdatingMap($insert)) {
                    $this->addPropertyBoundaries($eval[0], null);
                }

                if ($data['evaluation']['id']) {
                    $returnData = $this->updateApproaches($data);

                    $message = [
                        'status' => 'success',
                        'id' => $data['evaluation']['id'],
                        "data" => $returnData
                    ];
                } else {
                    $message = ['status' => 'success'];
                }
            }
        }

        return $message;
    }

    private function saveImages($data, $id)
    {
        if (isset($data['photo']) && !empty($data['photo'])) {
            $field = EvaluationImageKey::COVER;
            $this->updateOrCreateImage($id, $field);
        }
    }

    private function isUpdatingMap($params)
    {
        if (isset($params['map_image_url']) && isset($params['map_image_for_report_url'])) {
            return true;
        }
    }

    public function updateOrCreateImage($id, $field)
    {
        $metadata = $this->evaluations_metadata_model->retrieveOrCreate(['evaluation_id' => $id, 'name' => $field]);

        $file = $this->files_model->findBy([
            'title' => $field,
            'origin' => FileOrigin::EVALUATION_IMAGES,
            'entity_type' => 'evaluations',
            'entity_id' => $id
        ]);
        $maxMB = getenv('MAX_IMAGE_MB') * 1024 * 1024;
        if($_FILES['file']['size']>$maxMB){
            return ['status' => 'fail'];
        }else{  
            if (isset($file['id']) && $field != Evaluation::EXTRA_IMAGE_ID) {
                if ($this->files_model->removeFromServer($file['dir'])) {
                    $this->files_model->delete(['id' => $file['id']]);
                }
                $metadata['value'] = null;
            }

            $description = get_eval_image_types($field);

            $description = isset($description['title']) ? $description['title'] : null;
            $response = $this->files_model->addToServer(
                reset($_FILES),
                'evaluations',
                $id,
                $field,
                $description,
                FileOrigin::EVALUATION_IMAGES
            );

            if (isset($response['dir'])) {
                $metadata['value'] = $response['dir'];
                if ($field != Evaluation::EXTRA_IMAGE_ID) {
                    $this->evaluations_metadata_model->save($metadata);
                }
                $this->evaluations_model->save(['id' => $id, 'data' => [$field => $response['dir']]]);
                $response['status'] = 'success';
            }
        }
        return $response;
    }

    private function isChangingData($data)
    {
        $evaluationData = [];
        if (isset($data['evaluation']['data'])) {
            $evaluationData = isset($data['evaluation']['data']) ? $data['evaluation']['data'] : null;
            unset($data['evaluation']['data']);
        }

        return [$data, $evaluationData];
    }

    private function saveEvaluationData($data, $id)
    {
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $meta = $this->evaluations_metadata_model->get($key, $id);
                if (isset($meta->id)) {
                    $this->evaluations_metadata_model->put($key, $value, $id);
                } else {
                    $this->evaluations_metadata_model->post($key, $value, $id);
                }
            }
        }
    }
    
    public function createApproaches($id)
    {
        $this->db->insert($this->table_income_approach, ['evaluation_id' => $id]);
        $incomeId = $this->db->insert_id();
        $this->db->insert($this->table_sales_approach, ['evaluation_id' => $id]);
        $salesId = $this->db->insert_id();
        $this->db->insert($this->table_cost_approach, ['evaluation_id' => $id]);
        $costId = $this->db->insert_id();

        return [$incomeId, $salesId, $costId];
    }

    public function updateApproaches($data = array())
    {
        $id = $data['evaluation']['id'];
        $incomeId  = $salesId = $costId =  "";

        if (isset($data['evaluation']['income_approach'])) {
            if (isset($data['evaluation_income_approaches']['id']) && $data['evaluation_income_approaches']['id'] > 0) {
                $incomeId = $data['evaluation_income_approaches']['id'];
            } else {
                $this->db->insert($this->table_income_approach, ['evaluation_id' => $id]);
                $incomeId = $this->db->insert_id();
            }
            $incomeData = $data['evaluation']['income_approach'];
            $this->income_model->put($incomeId, $incomeData);
        }

        if (isset($data['evaluation']['sales_approach'])) {
            if (isset($data['evaluation_sales_approaches']['id']) && $data['evaluation_sales_approaches']['id'] > 0) {
                $salesId = $data['evaluation_sales_approaches']['id'];
            } else {
                $sales=$this->db->where('evaluation_id', $id)->get($this->table_sales_approach);
                if($sales->num_rows() == 0)
                {
                    $this->db->insert($this->table_sales_approach, ['evaluation_id' => $id]);
                    $salesId = $this->db->insert_id();
                }else{
                    $sale_id=$sales->row();
                    $salesId = $sale_id->id;
                }
            }
            $salesData = $data['evaluation']['sales_approach'];
            $this->sales_model->put($salesId, $salesData);
        }
        if (isset($data['evaluation']['cost_approach']) || isset($data['evaluation']['cost_approach_improvement'])) {
            if (isset($data['evaluation_cost_approaches']['id']) && $data['evaluation_cost_approaches']['id'] > 0) {
                $costId = $data['evaluation_cost_approaches']['id'];
            } else {
                $this->db->insert($this->table_cost_approach, ['evaluation_id' => $id]);
                $costId = $this->db->insert_id();
            }
        }

        $costData =  isset($data['evaluation']['cost_approach']) ? $data['evaluation']['cost_approach'] : array();
        $costImprovementData =  isset($data['evaluation']['cost_approach_improvement']) ? $data['evaluation']['cost_approach_improvement'] : array();
        $this->cost_model->put($costId, array_merge($costData, $costImprovementData));

        return [
            "evaluation_income_approaches_id" => $incomeId,
            "evaluation_sales_approaches_id" => $salesId,
            "evaluation_cost_approaches_id" => $costId
        ];
    }
    
    public function findOneObjectBy($args = array())
    {
        $entity = $this->findBy($args);

        if (isset($entity['id'])) {
            $entity['income'] = $this->income_model->get(null, $entity['id']);

            $entity['sales'] = $this->sales_model->get(null, $entity['id']);

            $entity['cost'] = $this->cost_model->get(null, $entity['id']);

            $entity['zonings'] = $this->zoning_model->findAllZonings('evaluation_id', $entity['id']);

            $entity['suitesParcels'] = $this->suites_model->findAllSuites('evaluation_id', $entity['id']);

            $entity['data'] = $this->evaluations_metadata_model->all($entity['id']);

            $res = new Evaluation($entity);

            return $res;
        }

        return new Evaluation();
    }

    public function getObjects($args)
    {
        $evaluations = array();

        foreach ($this->findAllBy($args) as $index => $evaluation) {
            $evaluations[] = new Evaluation($evaluation);
        }

        return $evaluations;
    }

    public function getEntityData($data)
    {
        $entity_data = array_filter($data, function ($val, $key) {
            if (stripos($key, '_url') === false) {
                return true;
            }
            return false;
        }, ARRAY_FILTER_USE_BOTH);

        $upload_data = array_filter($data, function ($val, $key) {
            if (stripos($key, '_url') !== false) {
                return true;
            }
            return false;
        }, ARRAY_FILTER_USE_BOTH);

        return array($entity_data, $upload_data);
    }
    
    public function assemble($data, $masterPropertyId = null)
    {
        $insert = [];

        foreach ($data as $column => $value) {
            if (array_key_exists($column, $this->evaluations_model->getFields())) {
                $insert[$column] = $this->sanitize($column, $value);
            }
        }

        if ($masterPropertyId) {
            $insert['property_id'] = $masterPropertyId;
        }

        return $insert;
    }
    
    public function assembleEvaluation($data)
    {

        $insert = [];

        foreach ($data as $column => $value) {
            if (array_key_exists($column, $this->getFields())) {
                $insert[$column] = $this->sanitizeEvaluation($column, $value);
            }
        }

        return $insert;
    }
    
    public function sanitize($column, $value)
    {
        if (is_array($value)) {
            return json_encode($value);
        } else if ($column === 'has_income_approach' || $column === 'has_sales_approach' || $column === 'has_cost_approach') {
            if ($value) {
                return strval($value) === 'true';
            } else {
                return null;
            }
        } else if ($column === 'expiration_date' || $column === 'report_date' || $column === 'date_of_analysis' || $column === 'last_transferred_date') {
            if ($value) {
                return formatDateForDatabase($value);
            } else {
                return null;
            }
        } else if($column == 'zoning_description')
        {
            $value =  preg_replace('#(<p><br></p>\s*)+#i', '', $value);
            return $value;
        }
        else {
            return $value;
        }
    }
    
    public function sanitizeEvaluation($column, $value)
    {
        if (is_array($value)) {
            return json_encode($value);
        } else {
            return $value;
        }
    }

    public function getMasterProperty($data = array())
    {
        $comp = isset($data['evaluation']) ? $data['evaluation'] : null;
        $zonings = isset($data['zonings']) ? $data['zonings'] : null;
        $spaces = isset($data['suites_parcels']) ? $data['suites_parcels'] : null;

        $coordinates = [
            'lat' => $comp['latitude'],
            'lng' => $comp['longitude']
        ];

        $address = [
            'business_name' => $comp['business_name'],
            'street_address' => $comp['street_address'],
            'city' => $comp['city'],
            'state' => $comp['state'],
            'zipcode' => $comp['zipcode'],
            'county' => isset($comp['county']) ? $comp['county'] :  null,
        ];
        list($isLand, $sf) = $this->getLandInformation($comp, $zonings, $spaces);

        if ($isLand) {
            $coordinates = [
                'lat' => isset($comp['map_pin_lat']) ? $comp['map_pin_lat'] : $comp['latitude'],
                'lng' => isset($comp['map_pin_lng']) ? $comp['map_pin_lng'] : $comp['longitude']
            ];
        }

        return $this->master->retrieve($address, $coordinates, $sf, $isLand);
    }
    
    public function getLandInformation($listing = array(), $zonings = null, $parcels = null)
    {
        $isLand =  false;

        $landParcelSize = null;
        if ($parcels) {
            foreach ($parcels as $parcel) {
                if (isset($parcel['type'])) {
                    if ('parcel' === strval($parcel['type'])) {
                        if (!$landParcelSize) $landParcelSize = 0;
                        $landParcelSize += isset($parcel['dimension']) && $parcel['dimension'] === 'SF' ? $parcel['space_size'] : $parcel['space_size'] * 43560;
                    }
                }
            }
        }

        $zoningSize = null;
        if ($zonings) {
            foreach ($zonings as $zoning) {
                if (isset($zoning['zone'])) {
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
        if (!$isLand) {
            if (isset($listing['land_size']) && !isset($listing['building_size'])) {
                $isLand = true;

                if ($landSize === 0) {
                    if (is_numeric($listing['land_dimension'])) {
                        if (!$landSize) $landSize = 0;
                        $landSize += isset($listing['land_dimension']) && strtolower($listing['land_dimension']) === 'sf' ? $listing['land_size'] : $listing['land_size'] * 43560;
                    }
                }
            }
        }

        if ($landParcelSize) {
            return [$isLand, $landParcelSize];
        } elseif ($zoningSize) {
            return [$isLand, $zoningSize];
        } elseif ($landSize) {
            return [$isLand, $landSize];
        }

        return [$isLand, false];
    }

    public function treat_data($post, $data = array())
    {
        foreach ($post as $entity_property => $entity_value) {
            if (!empty($entity_value)) {
                if (is_string($entity_value)) {
                    if (strpos($entity_property, '_url')) {
                    } else {
                        $data[$entity_property] = $entity_value;
                    }
                } else {
                    if (is_array($entity_value)) {
                        $data[$entity_property] = $this->treat_data($entity_value);
                    } else {
                        if (is_numeric($entity_value)) {
                            $data[$entity_property] = $entity_value;
                        } else {
                            if (is_a($entity_value, 'DateTime')) {
                                $data[$entity_property] = $entity_value;
                            }
                        }
                    }
                }
            }
        }
        return $data;
    }

    public function getFields()
    {
        return [
            'id' => 'evaluations',
            'client_id' => 'evaluations',
            'account_id' => 'evaluations',
            'user_id' => 'evaluations',
            'position' => 'evaluations',
            'summary' => 'evaluations',
            'file_number' => 'evaluations',
            'submitted' => 'evaluations',
            'created' => 'evaluations',
            'file_pdf_url' => 'evaluations',
            'business_name' => 'evaluations',
            'street_address' => 'evaluations',
            'street_suite' => 'evaluations',
            'city' => 'evaluations',
            'county' => 'evaluations',
            'state' => 'evaluations',
            'zipcode' => 'evaluations',
            'type' => 'evaluations',
            'under_contract_price' => 'evaluations',
            'last_transferred_date' => 'evaluations',
            'price' => 'evaluations',
            'property_image_url' => 'evaluations',
            'condition' => 'evaluations',
            'property_class' => 'evaluations',
            'year_built' => 'evaluations',
            'year_remodeled' => 'evaluations',
            'sale_price' => 'evaluations',
            'price_square_foot' => 'evaluations',
            'lease_rate' => 'evaluations',
            'term' => 'evaluations',
            'concessions' => 'evaluations',
            'building_size' => 'evaluations',
            'land_size' => 'evaluations',
            'land_dimension' => 'evaluations',
            'date_sold' => 'evaluations',
            'no_stories' => 'evaluations',
            'parcel_id_apn' => 'evaluations',
            'has_suite_parcel' => 'evaluations',
            'has_income_approach' => 'evaluations',
            'has_sales_approach' => 'evaluations',
            'has_cost_approach' => 'evaluations',
            'owner_of_record' => 'evaluations',
            'property_geocode' => 'evaluations',
            'property_legal' => 'evaluations',
            'property_rights' => 'evaluations',
            'high_and_best_user' => 'evaluations',
            'most_likely_owner_user' => 'evaluations',
            'intended_use' => 'evaluations',
            'intended_user' => 'evaluations',
            'topography' => 'evaluations',
            'frontage' => 'evaluations',
            'front_feet' => 'evaluations',
            'lot_depth' => 'evaluations',
            'utilities_select' => 'evaluations',
            'utilities_text' => 'evaluations',
            'zoning_type' => 'evaluations',
            'zoning_description' => 'evaluations',
            'services' => 'evaluations',
            'height' => 'evaluations',
            'main_structure_base' => 'evaluations',
            'foundation' => 'evaluations',
            'parking' => 'evaluations',
            'basement' => 'evaluations',
            'ada_compliance' => 'evaluations',
            'date_of_analysis' => 'evaluations',
            'inspector_name' => 'evaluations',
            'report_date' => 'evaluations',
            'exterior' => 'evaluations',
            'roof' => 'evaluations',
            'electrical' => 'evaluations',
            'plumbing' => 'evaluations',
            'heating_cooling' => 'evaluations',
            'windows' => 'evaluations',
            'additional_feature' => 'evaluations',
            'conforming_use_determination' => 'evaluations',
            'key_highlights' => 'evaluations',
            'site_details_north' => 'evaluations',
            'site_details_south' => 'evaluations',
            'site_details_east' => 'evaluations',
            'site_details_west' => 'evaluations',
            'traffic_counts' => 'evaluations',
            'map_image_url' => 'evaluations',
            'map_zoom' => 'evaluations',
            'map_selected_area' => 'evaluations',
            'map_pin_lat' => 'evaluations',
            'map_pin_lng' => 'evaluations',
            'map_pin_zoom' => 'evaluations',
            'map_image_for_report_url' => 'evaluations',
            'google_place_id' => 'evaluations',
            'map_pin_image_url' => 'evaluations',
            'latitude' => 'evaluations',
            'longitude' => 'evaluations',
            'exhibits' => 'evaluations',
            'county_details_url' => 'evaluations',
            'county_tax_url' => 'evaluations',
            'subdivision_survey_url' => 'evaluations',
            'weighted_market_value' => 'evaluations',
            'land_assessment' => 'evaluations',
            'structure_assessment' => 'evaluations',
            'sids' => 'evaluations',
            'taxes_in_arrears' => 'evaluations',
            'tax_liability' => 'evaluations',
            'review_summary' => 'evaluations',
            'client_first_name' => ['clients', 'first_name'],
            'client_last_name' => ['clients', 'last_name'],
            'client_company' => ['clients', 'company'],
            'zone' => 'zoning',
            'sub_zone' => 'zoning',
            'last_transfer_date_known' => 'evaluations',
            'rounding' => 'evaluations',
            'comp_adjustment_mode' => 'evaluations',
            'comp_type' => 'evaluations',
            'land_type' => 'evaluations',
            'lot_shape' => 'evaluations'
        ];
    }
    
    private function updateRelationalEntities($data, $spaces, $id, $zonings)
    {
        if (isset($data['step']) && strval($data['step']) === 'overview') {
            if (!isset($spaces) || (isset($spaces) && empty($spaces))) {
                $this->deleteSpaces($id);
            } else {
                $this->saveSpaces($spaces, $id);
            }

            if (!isset($zonings) || (isset($zonings) && empty($zonings))) {
                $this->removeZonings($id);
            } else {
                $this->saveZonings($zonings, $id);
            }
        }
        return $data;
    }

    public function deleteSpaces($listing_id)
    {
        $this->removeUnusedAssociation(array(), $listing_id, $this->suite_parcel_table);
    }

    public function saveZonings($zonings, $evaluation_id)
    {
        $this->zoning_model->addZonings($zonings, $evaluation_id, 'evaluation_id');
    }

    public function removeZonings($evaluation_id)
    {
        $this->removeUnusedAssociation(array(), $evaluation_id, $this->zoning_table);
    }


    private function removeUnusedAssociation($idsToKeep, $evaluation_id, $table)
    {
        if (isset($evaluation_id)) {
            $query = $this->db->select('id')->from($table)->where('evaluation_id', $evaluation_id);

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
    
    public function addPropertyBoundaries($evaluation, $type = 'new')
    {
        $map_image_url = null;
        $map_pin_image_url = null;
        $map_image_for_report_url = null;

        if ($evaluation->getMapImageUrl() && filter_var($evaluation->getMapImageUrl(), FILTER_VALIDATE_URL)) {
            $res = $this->files_model->addToServerFromStaticMap(
                $evaluation->getMapImageUrl(),
                $evaluation->getId(),
                'evaluations',
                'map_image_url',
                'png'
            );

            if (isset($res['dir'])) {
                if ($evaluation->getMapImageUrl() && $type !== 'new') {
                    $this->files_model->removeFromServer($evaluation->getMapImageUrl());
                }
                $map_image_url = $res['dir'];
            }
        }

        if ($evaluation->getMapImageForReportUrl() && filter_var($evaluation->getMapImageForReportUrl(), FILTER_VALIDATE_URL)) {
            $res = $this->files_model->addToServerFromStaticMap(
                $evaluation->getMapImageForReportUrl(),
                $evaluation->getId(),
                'evaluations',
                'map_image_for_report_url',
                'png'
            );

            if (isset($res['dir'])) {
                if ($evaluation->getMapImageForReportUrl() && $type !== 'new') {
                    $this->files_model->removeFromServer($evaluation->getMapImageForReportUrl());
                }
                $map_image_for_report_url = $res['dir'];
            }
        }

        if ($map_image_url || $map_pin_image_url || $map_image_for_report_url) {
            $data = ['id' => $evaluation->getId()];

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

    public function createevaluationdata($client_id)
    {
        if(isset($_SESSION['settings']['comp_adjustment_mode']))
        {
            $comp_adjustment_mode = $_SESSION['settings']['comp_adjustment_mode'];
        }else{
            $comp_adjustment_mode="Percent";
        }
        $account_id = $_SESSION['account_id'];
        $user_id = $_SESSION['user_id'];
        $this->db->insert('evaluations', array('id' => '', 'client_id' => $client_id, 'user_id' => $user_id, 'account_id' => $account_id,'comp_adjustment_mode' => $comp_adjustment_mode));
        $evalid = $this->db->insert_id();
        return $evalid;
    }

    public function get_cities($state)
    {
        $this->db->distinct();

        $this->db->select('city');

        $this->db->where('city !=', null);
        $this->db->where('city !=', '');
        $this->db->where('state',$state);

        $this->db->where('comps.date_sold is NOT NULL');
        $this->db->where('comps.sale_price > 0');
        
        if(is_user(UserRole::ROLE_ADMINISTRATOR) || is_user(UserRole::ROLE_DATA_ENTRY)){
            $this->db->where('comps.account_id', $_SESSION['account_id']);
        } elseif(is_user(UserRole::ROLE_USER)){
            $this->db->where('comps.user_id', $_SESSION['user_id']);
        }

        $query = $this->db->get('comps')->result_array();

        return $this->toArray($query);
    }

    public function get_Comps($data)
    {
        $this->db->select('*,comps.id as c_id')->from('comps');
        if($data['comp_type']=='sale' || $data['comp_type']=='lease')
        {
            $this->db->where('type',$data['comp_type']);
        }
        if($data['select_comp_type_modal']!='')
        {
            $this->db->where('comp_type ',$data['select_comp_type_modal']);
        }
        if($data['land_type']!='')
        {
            $this->db->where('land_type',$data['land_type']);
        }
        if($data['state']!='')
        {
            $this->db->where('state',$data['state']);
            if(isset($data['city']))
            {
                $this->db->where_in('city',$data['city']);
            }
        }
        if($data['start_date']!='')
        {
            $this->db->where('date(date_sold) >= ',date('Y-m-d', strtotime($data['start_date'])));
        }
        if($data['end_date']!='')
        {
            $this->db->where('date(date_sold) <= ',date('Y-m-d', strtotime($data['end_date'])));
        }
        if($data['square_footage_min']!='')
        {
            $this->db->where('building_size >= ',str_replace(",", "",$data['square_footage_min']));
        }
        if($data['square_footage_max']!='')
        {
            $this->db->where('building_size <= ',str_replace(",", "",$data['square_footage_max']));
        }
        if($data['land_sf_min']!='')
        {
            $this->db->where('land_size >= ',str_replace(",", "",$data['land_sf_min']));
        }
        if($data['land_sf_max']!='')
        {
            $this->db->where('land_size <= ',str_replace(",", "",$data['land_sf_max']));
        }
        if($data['cap_rate_min']!='')
        {
            $this->db->where('cap_rate >= ',str_replace("%",'',str_replace(",", "",$data['cap_rate_min'])));
        }
        if($data['cap_rate_max']!='')
        {
            $this->db->where('cap_rate <= ',str_replace("%","",str_replace(",", "",$data['cap_rate_max'])));
        }
        if($data['property_type']!='')
        {
            $this->db->where('zone',$data['property_type']);
            if($data['property_sub_type']!='')
            {
                $this->db->where('sub_zone',$data['property_sub_type']);
            }
        }
        $this->db->where('comps.date_sold is NOT NULL');
        $this->db->where('comps.sale_price > 0');
        
        if(is_user(UserRole::ROLE_ADMINISTRATOR) || is_user(UserRole::ROLE_DATA_ENTRY)){
            $this->db->where('comps.account_id', $_SESSION['account_id']);
        } elseif(is_user(UserRole::ROLE_USER)){
            $this->db->where('comps.user_id', $_SESSION['user_id']);
        }
        $this->db->join('zoning as z', 'comps.id = z.comp_id', 'LEFT');
        $this->db->group_by('comps.id');
        $this->db->order_by("date_sold", "desc");
        return $this->db->get()->result();
    }
}
