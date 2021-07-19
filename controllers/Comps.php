<?php

require_once(APPPATH.'models/enum/UserRole.php');

class Comps extends MY_Controller {
    private $head;
    private $title;

    public function __construct($is_secure = TRUE) {
        parent::__construct($is_secure);

        if(!get_logged_user_account()) {
            redirect('/users');
        }

        $this->load->model('comps_model');
        $this->load->model('zoning_model');
        $this->head['classname'] = $this->router->fetch_class();
        $this->title = 'Comps';
    }

    public function index() {
        $this->load->view('_partials/_head.php', $this->head);
        $this->load->view('_common/header.php');
        $this->load->view('comps/index.php');
        $this->load->view('_common/footer.php');
    }

    public function save() {
        $data = $this->input->post();
        if(empty($data['map_pin_lat']) || empty($data['map_pin_lng']))
        {
            $message = [
                'status' => 'fail',
                'message' => 'Your location does not map correctly. Please enter a valid address or place the map pin manually.'
            ];
            echo json_encode($message);die;
        }
        if(empty($data['year_built']) || $data['year_built']==0)
            $data['year_built']=null;
        if(empty($data['year_remodeled']) || $data['year_remodeled']==0)
            $data['year_remodeled']=null;
        if(empty($data['term']) || $data['term']==0)
            $data['term']=null;
        if($data['id']) {
            $res = $this->comps_model->put($data);
            echo json_encode($res);
        } else {
            $res = $this->comps_model->post($data);
            echo json_encode($res);
        }
    }

    public function get() {
        $id = $this->input->get('id');
        $normalize = $this->input->get('normalize');
        if (!empty($id)) {
            $comp = $this->comps_model->get($id, '*', $normalize);
            if ($comp) {
                echo json_encode($comp);
            }
        } else {
            echo json_encode('failure');
        }
    }

    public function getComps() {
        $ids = $this->input->get('id');
        $normalize = $this->input->get('normalize');
        if (!empty($ids)) {

            foreach($ids as $id)
            {
                $comp[] = $this->comps_model->get($id, '*', $normalize);
            }
            if ($comp) {
                echo json_encode($comp);
            }
        } else {
            echo json_encode('failure');
        }
    }

    public function delete() {
        $this->load->model('comps_model');

        if (!empty($this->input->post('id'))) {
            if ($this->comps_model->removeAllBy(['id' => $this->input->post('id')])) {
                echo json_encode(array("id" => $this->input->post('id')));
            } else {
                echo json_encode('failure');
            }
        } else {
            echo json_encode('failure');
        }
    }

    public function table_get() {
        $max = $this->input->get('length');
        $offset = $this->input->get('start');
        $order = $this->input->get('order');
        $args = $this->input->get('search');

        $permission = $this->listings_model->getPermissions();
        $permission['type']='sale';
        $permission['date_sold IS NOT NULL']=null;

        $compsTotal = $this->comps_model->table_countAllBy(null, $permission);
        $compsList = $this->comps_model->table_get($args, $max, $offset, $order, $permission);
        $compsTotalFiltered = isset($args) && !empty($args)? $this->comps_model->table_countAllBy($args, $permission): $compsTotal;

        $data['recordsTotal'] = $compsTotal;
        $data['recordsFiltered'] = $compsTotalFiltered;
        $data['data'] = $compsList;

        echo json_encode($data);
    }

    public function lease_table_get() {
        $max = $this->input->get('length');
        $offset = $this->input->get('start');
        $order = $this->input->get('order');
        $args = $this->input->get('search');

        $permission = $this->listings_model->getPermissions();
        $permission['type']='lease';
        $permission['date_sold IS NOT NULL']=null;

        $compsTotal = $this->comps_model->table_countAllBy(null, $permission);
        $compsList = $this->comps_model->table_get($args, $max, $offset, $order, $permission);
        $compsTotalFiltered = isset($args) && !empty($args)? $this->comps_model->table_countAllBy($args, $permission): $compsTotal;

        $data['recordsTotal'] = $compsTotal;
        $data['recordsFiltered'] = $compsTotalFiltered;
        $data['data'] = $compsList;

        echo json_encode($data);
    }

    public function saveWeight() {
        $data = $this->input->post();

        if(!empty($data)) {
            if(isset($data['id']) && isset($data['approach']) && isset($data['weight'])) {
                $id = $this->comps_model->saveWithEmptyValues(['id' => $data['id'], $data['approach'] . '_weight' => $data['weight']], true);
                $comp = $this->comps_model->findBy(['id' => $id]);
                if(isset($data['incremental']) && isset($data['market_value'])) {
                    if($data['approach'] === 'income') {
                        $approach = $comp['income_approach'];
                        if(isset($approach['incremental_value']['annual'])) {
                            $approach['incremental_value']['annual'] = $data['incremental'];
                        }

                        $this->comps_model->saveWithEmptyValues(['id' => $data['id'], 'income_approach' => json_encode($approach)]);
                    } elseif($data['approach'] === 'sales') {
                        $approach = $comp['sales_approach'];
                        if(isset($approach['incremental_value'])) {
                            $approach['incremental_value'] = $data['incremental'];
                        }

                        $this->comps_model->saveWithEmptyValues(['id' => $data['id'], 'sales_approach' => json_encode($approach)]);
                    } elseif($data['approach'] === 'cost') {
                        $approach = $comp['cost_approach'];
                        if(isset($approach['incremental_value'])) {
                            $approach['incremental_value'] = $data['incremental'];
                        }

                        $this->comps_model->saveWithEmptyValues(['id' => $data['id'], 'cost_approach' => json_encode($approach)]);
                    }
                }
            }
        }
    }

    public function get_approaches() {
        if (!empty($this->input->get('id'))) {
            $comp = $this->comps_model->get($this->input->get('id'));
            if ($comp) {
                echo json_encode(
                    array(
                        'status' => 'success',
                        'valueIndicatedIncome' => isset($comp['income_approach']['indicated_value_range']['annual'])? $comp['income_approach']['indicated_value_range']['annual']:0,
                        'ppsfValueIndicatedIncome' => isset($comp['income_approach']['indicated_value_psf']['annual'])? $comp['income_approach']['indicated_value_psf']['annual']:0,
                        'valueIndicatedSales' => isset($comp['sales_approach']['sales_approach_value'])? $comp['sales_approach']['sales_approach_value']:0,
                        'ppsfValueIndicatedSales' => isset($comp['sales_approach']['averaged_adjusted_psf'])? $comp['sales_approach']['averaged_adjusted_psf']:0,
                        'valueIndicatedCost' => isset($comp['cost_approach_improvement']['total_cost_valuation'])? $comp['cost_approach_improvement']['total_cost_valuation']:0,
                        'ppsfValueIndicatedCost' => isset($comp['cost_approach_improvement']['indicated_value_psf'])?$comp['cost_approach_improvement']['indicated_value_psf']:0,
                        'incomeWeight' => isset($comp['income_weight'])?($comp['income_weight']/100):0.33,
                        'salesWeight' => isset($comp['sales_weight'])?($comp['sales_weight']/100):0.33,
                        'costWeight' => isset($comp['cost_weight'])?($comp['cost_weight']/100):0.33,
                    )
                );
            }
        } else {
            echo json_encode(array('status' => 'fail'));
        }
    }

    public function search() {
        $needle = $this->input->get('needle');
        $limit = $this->input->get('limit');
        $id = $this->input->get('comp_id');

        if (isset($needle)) {
            $comp = $this->comps_model->search($needle, $limit, $id);
            if (isset($comp) && !empty($comp)) {
                echo json_encode($comp);
            }  else {
                echo json_encode([]);
            }
        } else {
            echo json_encode([]);
        }
    }

    public function setFieldToNull() {
        $comp_id = $this->input->get('id');
        $field_name = $this->input->get('fieldName');
        if (isset($comp_id) && isset($field_name)) {
            $data['id'] = $comp_id;
            $data[$field_name] = NULL;

            $id = $this->wggenerator->saveEntity($data, 'comps', FALSE);

            if (isset($id)) {
                echo json_encode(['status'=>'success']);
            }  else {
                echo json_encode(['status'=>'fail', 'message'=>'It was not possible to execute that action now.']);
            }
        } else {
            echo json_encode(['status'=>'fail', 'message'=>'No comp id provided. Try refreshing the page.']);
        }
    }

    public function setJsonFieldToNull() {
        $comp_id = $this->input->get('id');
        $fieldValue = $this->input->get('fieldValue');

        if (isset($comp_id) && isset($fieldValue)) {
            $data['id'] = $comp_id;
            $data['field_value'] = $fieldValue;

            $exhibits = $this->comps_model->removeFieldFromJson($data);

            $id = $this->wggenerator->saveEntity(array('id'=> $data['id'], 'exhibits' => $exhibits));

            if (isset($id)) {
                echo json_encode(['status'=>'success', 'id' => $id]);
            }  else {
                echo json_encode(['status'=>'fail', 'message'=>'It was not possible to execute that action now.']);
            }
        } else {
            echo json_encode(['status'=>'fail', 'message'=>'No comp id provided. Try refreshing the page.']);
        }
    }
}