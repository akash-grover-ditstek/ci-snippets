<?php
use Dompdf\Dompdf;
use Dompdf\Options;

require_once(APPPATH . 'models/enum/UserRole.php');
require_once(CLASSES_DIR . 'File.php');
require_once(CLASSES_DIR . 'Evaluation.php');
require_once(CLASSES_DIR . 'Comparative.php');
require_once(ENUM_DIR . 'FileOrigin.php');
require_once(ENUM_DIR . 'EvaluationImageKey.php');

class Evaluations extends MY_Controller
{
    private $head;

    public function __construct()
    {
        parent::__construct(true);

        if (!get_logged_user_account()) {
            redirect('/users');
        }

        if (!can_access_eval()) {
            redirect('/');
        }

        $this->load->model('evaluations_model');
        $this->load->model('evaluations_metadata_model');
        $this->load->model('comps_model');
        $this->load->model('listings_model');
        $this->load->model('zoning_model');
        $this->load->model('clients_model');
        $this->load->model('income_model');
        $this->load->model('sales_model');
        $this->load->model('cost_model');
        $this->head['classname'] = $this->router->fetch_class();
    }
    
    public function index()
    {
        $data = array();
        if(is_user(UserRole::ROLE_SUPER_ADMINISTRATOR) || is_user(UserRole::ROLE_DEV)) {
            $data['evaluations'] = $this->evaluations_model->findAllBy(null, null, null, ['report_date' => 'desc','id' => 'desc'], true);
        } else if(is_user(UserRole::ROLE_ADMINISTRATOR)){
             $data['evaluations'] = $this->evaluations_model->findAllBy(['account_id' => $_SESSION['account_id']], null, null, ['report_date' => 'desc','id' => 'desc'], true);
        }else{
            $data['evaluations'] = $this->evaluations_model->findAllBy(['user_id' => $_SESSION['user_id']], null, null, ['report_date' => 'desc','id' => 'desc'], true);
        }
        
        $clients = $this->clients_model->findAllObjects();

        $options = [null => 'Select a client'];

        foreach ($clients as $client) {
            if ($client->getName() && $client->getCompany()) {
                $options[$client->getId()] = $client->getName() . ' (' . $client->getCompany() . ')';
            } elseif ($client->getName() && !$client->getCompany()) {
                $options[$client->getId()] = $client->getName();
            } elseif (!$client->getName() && $client->getCompany()) {
                $options[$client->getId()] = $client->getCompany();
            }
        }

        $data['clients'] = $options;

        $this->load->view('_partials/_head.php', $this->head);
        $this->load->view('_common/header.php');
        $this->load->view('evaluations/list.php', $data);
        $this->load->view('_common/footer.php');
    }
    
    public function overview($id = null)
    {
        $data = array();

        $data['isEval'] = true;
        $data['step'] = 'overview';
        $data['next'] = 'images';

        $data['evaluation'] = new Evaluation();

        if ($id) {
            $data['evaluation'] = $this->evaluations_model->findOneObjectBy(['id' => $id]);
            $this->evaluations_model->save(array(
                'position' => 'overview',
                'id' => $id
            ));
        } else {
            if ($this->input->get('client')) {
                $data['evaluation']->setAccountId($this->session->userdata('account_id'));
                $data['evaluation']->setUserId($this->session->userdata('user_id'));
                $data['evaluation']->setClientId($this->input->get('client'));
            } else {
                $this->session->set_flashdata('flash_message', 'No client or evaluation selected.');
                redirect('evaluations');
            }
        }

        $this->load->view('_partials/_head.php', $this->head);
        $this->load->view('_common/header.php');
        $this->load->view('evaluations/_partials/eval_menu.php', ['evaluation' => $data['evaluation']]);
        $this->load->view('evaluations/overview/overview.php', $data);
        $this->load->view('_common/footer.php');
    }
    
    public function images($id = null)
    {
        $data = array();

        $data['step'] = 'images';
        $data['next'] = 'property-boundaries';
        $data['previous'] = 'overview';

        $data['evaluation'] = new Evaluation();

        if ($id) {
            $data['evaluation'] = $this->evaluations_model->findOneObjectBy(['id' => $id]);
            $data['images'] = $data['evaluation']->getImages();

            $this->evaluations_model->save(array(
                'position' => $data['step'],
                'id' => $id
            ));
        } else {
            if ($this->input->get('client')) {
                $data['evaluation']->setAccountId($this->session->userdata('account_id'));
                $data['evaluation']->setUserId($this->session->userdata('user_id'));
                $data['evaluation']->setClientId($this->input->get('client'));
                $data['evaluation']->setAccountId($this->session->userdata('account_id'));
                $data['evaluation']->setUserId($this->session->userdata('user_id'));
            } else {
                $this->session->set_flashdata('flash_message', 'No client or evaluation selected.');
                redirect('evaluations');
            }
        }

        $this->load->view('_partials/_head.php', $this->head);
        $this->load->view('_common/header.php');
        $this->load->view('evaluations/_partials/eval_menu.php', $data);
        $this->load->view('_partials/comps/images.php', $data);
        $this->load->view('_common/footer.php');
    }
    
    public function property_boundaries($id = null)
    {
        $data = array();

        $data['step'] = 'property-boundaries';
        $data['next'] = 'income';
        $data['previous'] = 'images';

        $data['evaluation'] = new Evaluation();

        if ($id) {
            $data['evaluation'] = $this->evaluations_model->findOneObjectBy(['id' => $id]);

            if ($data['evaluation']->hasIncomeApproach()) {
                $data['next'] = 'income';
            } else {
                if ($data['evaluation']->hasSalesApproach()) {
                    $data['next'] = 'sales';
                } else {
                    if ($data['evaluation']->hasCostApproach()) {
                        $data['next'] = 'cost';
                    } else {
                        $data['next'] = 'exhibits';
                    }
                }
            }

            $this->evaluations_model->save(array(
                'position' => $data['step'],
                'id' => $id
            ));
        } else {
            if ($this->input->get('client')) {
                $data['evaluation']->setAccountId($this->session->userdata('account_id'));
                $data['evaluation']->setUserId($this->session->userdata('user_id'));
                $data['evaluation']->setClientId($this->input->get('client'));
            } else {
                $this->session->set_flashdata('flash_message', 'No client or evaluation selected.');
                redirect('evaluations');
            }
        }

        $this->load->view('_partials/_head.php', $this->head);
        $this->load->view('_common/header.php');
        $this->load->view('evaluations/_partials/eval_menu.php', $data);
        $this->load->view('_partials/comps/propertyBoundaries.php', $data);
        $this->load->view('_common/footer.php');
    }
    
    public function income($id = null)
    {
        $data = array();

        $data['step'] = 'income';
        $data['next'] = 'sales';
        $data['previous'] = 'property-boundaries';

        $data['evaluation'] = new Evaluation();
        if ($id) {
            $data['evaluation'] = $this->evaluations_model->findOneObjectBy(['id' => $id]);

            if ($data['evaluation']->hasSalesApproach()) {
                $data['next'] = 'sales';
            } else {
                if ($data['evaluation']->hasCostApproach()) {
                    $data['next'] = 'cost';
                } else {
                    $data['next'] = 'exhibits';
                }
            }

            if (!$data['evaluation']->hasIncomeApproach()) {
                if ($data['evaluation']->hasSalesApproach()) {
                    redirect('evaluations/sales/' . $id);
                } else {
                    if ($data['evaluation']->hasCostApproach()) {
                        redirect('evaluations/cost/' . $id);
                    } else {
                        redirect('evaluations/exhibits/' . $id);
                    }
                }
            }

            $this->evaluations_model->save(array(
                'position' => $data['step'],
                'id' => $id
            ));
        } else {
            if ($this->input->get('client')) {
                $data['evaluation']->setAccountId($this->session->userdata('account_id'));
                $data['evaluation']->setUserId($this->session->userdata('user_id'));
                $data['evaluation']->setClientId($this->input->get('client'));
                $data['evaluation']->setAccountId($this->session->userdata('account_id'));
                $data['evaluation']->setUserId($this->session->userdata('user_id'));
            } else {
                $this->session->set_flashdata('flash_message', 'No client or evaluation selected.');
                redirect('evaluations');
            }
        }

        $this->load->view('_partials/_head.php', $this->head);
        $this->load->view('_common/header.php');
        $this->load->view('evaluations/approaches/income/index.php', $data);
        $this->load->view('_common/footer.php');
    }

    public function sales($id = null)
    {
        $data = array();

        $data['step'] = 'sales';
        $data['next'] = 'cost';
        $data['previous'] = 'income';

        $data['evaluation'] = new Evaluation();

        if ($id) {
            $data['evaluation'] = $this->evaluations_model->findOneObjectBy(['id' => $id]);

            if ($data['evaluation']->hasCostApproach()) {
                $data['next'] = 'cost';
            } else {
                $data['next'] = 'exhibits';
            }

            if ($data['evaluation']->hasIncomeApproach()) {
                $data['previous'] = 'income';
            } else {
                $data['previous'] = 'property-boundaries';
            }

            if (!$data['evaluation']->hasSalesApproach()) {
                if ($data['evaluation']->hasCostApproach()) {
                    redirect('evaluations/cost/' . $id);
                } else {
                    redirect('evaluations/exhibits/' . $id);
                }
            }

            $this->evaluations_model->save(array(
                'position' => $data['step'],
                'id' => $id
            ));
        } else {
            if ($this->input->get('client')) {
                $data['evaluation']->setAccountId($this->session->userdata('account_id'));
                $data['evaluation']->setUserId($this->session->userdata('user_id'));
                $data['evaluation']->setClientId($this->input->get('client'));
                $data['evaluation']->setAccountId($this->session->userdata('account_id'));
                $data['evaluation']->setUserId($this->session->userdata('user_id'));
            } else {
                $this->session->set_flashdata('flash_message', 'No client or evaluation selected.');
                redirect('evaluations');
            }
        }

        $this->load->view('_partials/_head.php', $this->head);
        $this->load->view('_common/header.php');
        $this->load->view('evaluations/approaches/sales/index.php', $data);
        $this->load->view('_common/footer.php');
    }

    public function cost($id = null)
    {
        $data = array();

        $data['step'] = 'cost';
        $data['next'] = 'cost-improvement';
        $data['previous'] = 'sales';

        $data['evaluation'] = new Evaluation();

        if ($id) {
            $data['evaluation'] = $this->evaluations_model->findOneObjectBy(['id' => $id]);

            if ($data['evaluation']->hasSalesApproach()) {
                $data['previous'] = 'sales';
            } else {
                if ($data['evaluation']->hasIncomeApproach()) {
                    $data['previous'] = 'income';
                } else {
                    $data['previous'] = 'property-boundaries';
                }
            }

            if (!$data['evaluation']->hasCostApproach()) {
                redirect('evaluations/exhibits/' . $id);
            }

            $this->evaluations_model->save(array(
                'position' => $data['step'],
                'id' => $id
            ));
        } else {
            if ($this->input->get('client')) {
                $data['evaluation']->setAccountId($this->session->userdata('account_id'));
                $data['evaluation']->setUserId($this->session->userdata('user_id'));
                $data['evaluation']->setClientId($this->input->get('client'));
                $data['evaluation']->setAccountId($this->session->userdata('account_id'));
                $data['evaluation']->setUserId($this->session->userdata('user_id'));
            } else {
                $this->session->set_flashdata('flash_message', 'No client or evaluation selected.');
                redirect('evaluations');
            }
        }
        $this->load->view('_partials/_head.php', $this->head);
        $this->load->view('_common/header.php');
        $this->load->view('evaluations/approaches/cost/index.php', $data);
        $this->load->view('_common/footer.php');
    }

    public function improvement($id = null)
    {
        $data = array();

        $data['step'] = 'cost-improvement';
        $data['next'] = 'exhibits';
        $data['previous'] = 'cost';

        $data['evaluation'] = new Evaluation();

        if ($id) {
            $data['evaluation'] = $this->evaluations_model->findOneObjectBy(['id' => $id]);

            $this->evaluations_model->save(
                array(
                    'position' => $data['step'],
                    'id' => $id
                ));
        } else {
            if ($this->input->get('client')) {
                $data['evaluation']->setAccountId($this->session->userdata('account_id'));
                $data['evaluation']->setUserId($this->session->userdata('user_id'));
                $data['evaluation']->setClientId($this->input->get('client'));
                $data['evaluation']->setAccountId($this->session->userdata('account_id'));
                $data['evaluation']->setUserId($this->session->userdata('user_id'));
            } else {
                $this->session->set_flashdata('flash_message', 'No client or evaluation selected.');
                redirect('evaluations');
            }
        }
        $this->load->view('_partials/_head.php', $this->head);
        $this->load->view('_common/header.php');
        $this->load->view('evaluations/approaches/cost/improvement.php', $data);
        $this->load->view('_common/footer.php');
    }

    public function exhibits($id = null)
    {
        $this->load->model('files_model');
        $data = array();

        $data['step'] = 'exhibits';
        $data['next'] = 'review';
        $data['previous'] = 'cost-improvement';

        $data['evaluation'] = new Evaluation();

        if ($id) {
            $data['evaluation'] = $this->evaluations_model->findOneObjectBy(['id' => $id]);

            if ($data['evaluation']->hasCostApproach()) {
                $data['previous'] = 'cost-improvement';
            } else {
                if ($data['evaluation']->hasSalesApproach()) {
                    $data['previous'] = 'sales';
                } else {
                    if ($data['evaluation']->hasIncomeApproach()) {
                        $data['previous'] = 'income';
                    } else {
                        $data['previous'] = 'property-boundaries';
                    }
                }
            }
            $this->evaluations_model->save(array(
                'position' => $data['step'],
                'id' => $id
            ));

        } else {
            if ($this->input->get('client')) {
                $data['evaluation']->setAccountId($this->session->userdata('account_id'));
                $data['evaluation']->setUserId($this->session->userdata('user_id'));
                $data['evaluation']->setClientId($this->input->get('client'));
                $data['evaluation']->setAccountId($this->session->userdata('account_id'));
                $data['evaluation']->setUserId($this->session->userdata('user_id'));
            } else {
                $this->session->set_flashdata('flash_message', 'No client or evaluation selected.');
                redirect('evaluations');
            }
        }

        $this->load->view('_partials/_head.php', $this->head);
        $this->load->view('_common/header.php');
        $this->load->view('evaluations/exhibits/index.php', $data);
        $this->load->view('_common/footer.php');
    }

    public function review($id = null)
    {
        $data = array();
        $data['step'] = 'review';

        $data['evaluation'] = new Evaluation();

        if ($id) {
            $data['evaluation'] = $this->evaluations_model->findOneObjectBy(['id' => $id]);

            $this->evaluations_model->save(array(
                'position' => 'review',
                'id' => $id
            ));

        } else {
            if ($this->input->get('client')) {
                $data['evaluation']->setAccountId($this->session->userdata('account_id'));
                $data['evaluation']->setUserId($this->session->userdata('user_id'));
                $data['evaluation']->setClientId($this->input->get('client'));
                $data['evaluation']->setAccountId($this->session->userdata('account_id'));
                $data['evaluation']->setUserId($this->session->userdata('user_id'));
            } else {
                $this->session->set_flashdata('flash_message', 'No client or evaluation selected.');
                redirect('evaluations');
            }
        }

        $this->load->view('_partials/_head.php', $this->head);
        $this->load->view('_common/header.php');
        $this->load->view('evaluations/review/index.php', $data);
        $this->load->view('_common/footer.php');
    }
    
    public function save() {
        $data = $this->input->post();
        if(isset($data['evaluation']['street_address']) && (isset($data['evaluation']['map_pin_lat']) && isset($data['evaluation']['map_pin_lng'])) && (empty($data['evaluation']['map_pin_lat']) || empty($data['evaluation']['map_pin_lng'])))
        {
            $message = [
                'status' => 'fail',
                'message' => 'Your location does not map correctly. Please enter a valid address or place the map pin manually.'
            ];
            echo json_encode($message);die;
        }
        if (isset($data['evaluation'])) {
            if($data['evaluation']['id']) {
                $res = $this->evaluations_model->put($data);
            } else {
                $res = $this->evaluations_model->post($data);
            }

            if(isset($_POST[EvaluationTextKey::INCOME_APPROACH_INCOME_NOTES])) {

            $metadata = $this->evaluations_metadata_model->retrieveOrCreate(['evaluation_id' => $data['evaluation']['id'], 'name' => EvaluationTextKey::INCOME_APPROACH_INCOME_NOTES]);
            $defaultIncome = 'Income is based on a blend of actual rates received on the property, adjusted for the type of property and quoted on a modified gross basis.';

            $income = trim($this->input->post(EvaluationTextKey::INCOME_APPROACH_INCOME_NOTES)) != ''
                ? trim($this->input->post(EvaluationTextKey::INCOME_APPROACH_INCOME_NOTES)) : $defaultIncome;

            if(isset($metadata['id'])) {
                $this->evaluations_metadata_model->save([
                    'id' => $metadata['id'],
                    'value' => $income
                ]);
            } else {

                $this->evaluations_metadata_model->save([
                    'evaluation_id' => $data['evaluation']['id'],
                    'name' => EvaluationTextKey::INCOME_APPROACH_INCOME_NOTES,
                    'value' => $income
                ]);
            }
            }

            if(isset($_POST[EvaluationTextKey::INCOME_APPROACH_EXPENSE_NOTES])) {
                $metadata = $this->evaluations_metadata_model->retrieveOrCreate(['evaluation_id' => $data['evaluation']['id'], 'name' => EvaluationTextKey::INCOME_APPROACH_EXPENSE_NOTES]);
                $defaultExpense = 'Expense are derived from actual costs and estimates incurred on the property and assumptions made by the author all values are deemed reliable but not guaranteed.';
                $expense = trim($this->input->post(EvaluationTextKey::INCOME_APPROACH_EXPENSE_NOTES)) != '' ?
                    trim($this->input->post(EvaluationTextKey::INCOME_APPROACH_EXPENSE_NOTES)) : $defaultExpense;

                if (isset($metadata['id'])) {

                    $this->evaluations_metadata_model->save([
                        'id' => $metadata['id'],
                        'value' => $expense
                    ]);
                } else {
                    $this->evaluations_metadata_model->save([
                        'evaluation_id' => $data['evaluation']['id'],
                        'name' => EvaluationTextKey::INCOME_APPROACH_EXPENSE_NOTES,
                        'value' => $expense
                    ]);
                }
            }

        }

        echo json_encode($res);

    }

    public function saveWeight($id = null) {
        $data = $this->input->post();
        if (isset($data['comp'])) {
            if($data['comp']['id']) {
                $res = $this->evaluations_model->put($data);
                echo json_encode($res);
            } else {
                $res = $this->evaluations_model->post($data);
                echo json_encode($res);
            }
        }
    }
    
    public function get_income_approach() {
        $id = $this->input->get('id');
        if (!empty($id)) {
            $income = $this->income_model->get($id);
            if ($income->getId()) {
                if($income->getAnnualIndicatedValuePsf() && $income->getAnnualIndicatedValueRange()) {
                    echo json_encode([
                        'status' => 'success',
                        'income_approach_value' => $income->getAnnualIndicatedValueRange(),
                        'incremental_approach_value' => $income->getAnnualIncrementalValue(),
                        'income_approach_psf' => $income->getAnnualIndicatedValuePsf()
                    ]);
                } else {
                    echo json_encode(['status' => 'fail']);
                }
            }
        } else {
            echo json_encode(['status' => 'fail']);
        }
    }
    
    public function get_sales_approach() {
        $id = $this->input->get('id');
        if (!empty($id)) {
            $sales = $this->sales_model->get($id);
            if ($sales->getId()) {
                if($sales->getSalesApproachValue() && $sales->getAveragedAdjustedPsf()) {
                    echo json_encode([
                        'status' => 'success',
                        'sales_approach_value' => $sales->getSalesApproachValue(),
                        'incremental_value' => $sales->getIncrementalValue(),
                        'sales_approach_psf' => $sales->getAveragedAdjustedPsf()
                    ]);
                } else {
                    echo json_encode(['status' => 'fail']);
                }
            }
        } else {
            echo json_encode(['status' => 'fail']);
        }
    }
    
    public function get_cost_approach_improvement() {
        $id = $this->input->get('id');
        if (!empty($id)) {
            $cost = $this->cost_model->get($id);
            if ($cost->getId()) {
                echo json_encode(['status' => 'success', 'approach' => $cost]);
            }
        } else {
            echo json_encode(['status' => 'fail']);
        }
    }
    
    public function get_cost_approach() {
        $id = $this->input->get('id');
        if (!empty($id)) {
            $cost = $this->cost_model->get($id);
            if ($cost->getId()) {
                    echo json_encode([
                        'status' => 'success',
                        'approach' => $cost,
                        'final' => $cost
                    ]);
            }
        } else {
            echo json_encode(['status' => 'fail']);
        }
    }

    public function post_file()
    {
        if ($_FILES) {
            $result = $this->wggenerator->saveEntity($this->input->post(), 'comps');

            if (isset($result) && is_numeric($result)) {
                echo json_encode(array('status' => 'success', 'message' => 'The file was uploaded'));
            } else {
                echo json_encode(array(
                    'status' => 'fail',
                    'message' => 'It was not possible to upload the file at this moment. Try again later.'
                ));
            }
        } else {
            echo json_encode(array(
                'status' => 'fail',
                'message' => 'No file found to upload. Check if your browser supports this feature.'
            ));
        }
    }

    public function post_exhibit()
    {
        if (isset($_FILES['file'])) {
            $result = null;

            $this->load->model('files_model');

            $result = $this->files_model->addToServer($_FILES['file'], 'evaluations', $this->input->post('id'), null,
                'exhibit', FileOrigin::EVALUATION_EXHIBITS);

            if (isset($result['fileId'])) {
                echo json_encode(array(
                    'status' => 'success',
                    'message' => 'The file was uploaded',
                    'dir' => $result['dir'],
                    'filename' => $result['filename'],
                    'id' => $result['fileId']
                ));
            } else {
                echo json_encode(array(
                    'status' => 'fail',
                    'message' => 'It was not possible to upload the file at this moment. Try again later.'
                ));
            }
        } else {
            echo json_encode(array(
                'status' => 'fail',
                'message' => 'No file found to upload. Check if your browser supports this feature.'
            ));
        }
    }

    public function remove_file()
    {
        $id = $this->input->post('id');
        $field = $this->input->post('field');

        if (isset($id) && isset($field)) {
            $result = [];

            $this->load->model('files_model');
            $comp = $this->comps_model->findBy(['id' => $id]);

            if (array_key_exists($field, $comp)) {
                $dir = $comp[$field];

                $this->files_model->removeFromServer($dir);
                $result = $this->wggenerator->saveEntity(['id' => $id, $field => null], 'comps', false);
            }

            if (isset($result) && is_numeric($result)) {
                echo json_encode(array('status' => 'success', 'message' => 'The file was removed.'));
            } else {
                echo json_encode(array(
                    'status' => 'fail',
                    'message' => 'It was not possible to remove the file at this moment. Try again later.'
                ));
            }
        } else {
            echo json_encode(array(
                'status' => 'fail',
                'message' => 'It was not possible to remove the file at this moment. Try again later.'
            ));
        }
    }

    public function remove_exhibit()
    {
        $file_id = $this->input->post('file_id');

        if (isset($file_id)) {
            $this->load->model('files_model');
            $file = $this->files_model->findById($file_id);

            if ($this->files_model->remove($file)) {
                echo json_encode(array('status' => 'success', 'message' => 'The file was removed.'));
            } else {
                echo json_encode(array(
                    'status' => 'fail',
                    'message' => 'It was not possible to remove the file at this moment. Try again later.'
                ));
            }
        } else {
            echo json_encode(array(
                'status' => 'fail',
                'message' => 'It was not possible to remove the file at this moment. Try again later.'
            ));
        }
    }

    public function put_exhibit()
    {
        $file_id = $this->input->post('id');
        $data = array(
            'field' => $this->input->post('field'),
            'value' => $this->input->post('value')
        );

        if (isset($file_id) && $data['value'] !== '') {
            $this->load->model('files_model');
            $file = $this->files_model->findById($file_id);

            if ($this->files_model->update($file, $data)) {
                echo json_encode(array('status' => 'success', 'message' => 'The file was updated.'));
                exit;
            }
        }
        echo json_encode(array(
            'status' => 'fail',
            'message' => 'It was not possible to update the file at this moment. Try again later.'
        ));
        exit;
    }

    public function get()
    {
        if (!empty($this->input->get('id'))) {
            $id = $this->input->get('id');

            $evaluation = $this->evaluations_model->get($id);

            echo json_encode($evaluation);
        } else {
            echo json_encode('failure');
        }
    }
    
    public function upload_image()
    {
        $field = $this->input->post('field');
        $id = $this->input->post('id');

        $response = ['status' => 'fail'];

        if ($field && $id) {
            $response = $this->evaluations_model->updateOrCreateImage($id, $field);
        }

        echo json_encode($response);
    }
    
    public function remove_image()
    {
        $response = ['status' => 'fail'];
        $id = $this->input->post('id');
        $eval_id = $this->input->post('eval_id');
        $field = $this->input->post('field');
        $dir = $this->input->post('dir');

        if ($id) {
            $metadata = $this->evaluations_metadata_model->retrieveOrCreate(['evaluation_id' => $eval_id, 'name' => $field]);

            $file = $this->files_model->findBy([
                'id' => $id,
                'origin' => FileOrigin::EVALUATION_IMAGES,
                'entity_type' => 'evaluations'
            ]);

            if (isset($file['id'])) {
                if ($this->files_model->removeFromServer($file['dir'])) {
                    if ($this->files_model->delete(['id' => $file['id']])) {
                        $res = $this->evaluations_model->findBy(['id' => $eval_id],false);

                        $data = $res['data'];

                        if ($field) {
                            unset($data[$field]);
                        } elseif ($dir) {
                            foreach ($data as $index => $val) {
                                if (strval($val) === strval($dir)) {
                                    unset($data[$index]);
                                }
                            }
                        }

                        $this->evaluations_model->saveWithEmptyValues(['id' => $eval_id, 'data' => json_encode($data)]);

                        if(isset($metadata['id'])) {
                            $this->evaluations_metadata_model->delete(['evaluation_id' => $eval_id, 'name' => $field]);
                        }

                        $response['status'] = 'success';
                    }
                }
            }
        }

        echo json_encode($response);
    }
    public function createevaluation()
    {
        $client_id = $this->input->post('clients');
       $id = $this->evaluations_model->createevaluationdata($client_id);
        redirect('evaluations/overview/'.$id);
    }

    public function deleteBOV($bovid)
    {
        $res = $this->evaluations_model->delete(['id' => $bovid]);
        $response['status'] = 'success';
        echo json_encode($response);        
    }

    public function get_cities()
    {
        $state = $this->input->get('state');
        $cities = array();

        foreach ($this->evaluations_model->get_cities($state) as $index => $value) {
            if ($value['city']) {
                array_push($cities, array('val' => $value['city'], 'text' => $value['city']));
            }
        }

        array_multisort(array_column($cities, 'val'), SORT_ASC, $cities);

        echo json_encode($cities);
    }

    function get_comps()
    {
        $data = $_POST;
        $comps = $this->evaluations_model->get_Comps($data);
        $response="";
        if($comps)
        {
            foreach($comps as $comp)
            {
                if(isset($comp->lease_rate) && $comp->lease_rate > 0) {
                    $sale_p = $comp->lease_rate;
                    $sale_price = maskMoney($comp->lease_rate);
                }else if(isset($comp->sale_price) && $comp->sale_price > 0) {
                    $sale_p = $comp->sale_price;
                    $sale_price = maskMoney($comp->sale_price);
                } else {
                    $sale_p = 0;
                    $sale_price = '--';
                }
                if (isset($comp->building_size) && $comp->building_size > 0) {
                    $build_size = $comp->building_size;
                    $building_size = maskArea($comp->building_size);
                } else {
                    $build_size = 0;
                    $building_size = '--';
                }
                if (isset($comp->price_square_foot) && $comp->price_square_foot > 0) {
                    $price_square_foot = maskMoney($comp->price_square_foot);
                } else {
                    $price_square_foot = '--';
                    if(isset($sale_p) && is_number($sale_p)) {
                        if(isset($build_size) && is_number($build_size)) {
                            if($build_size==0)
                            {
                                $build_size=1;
                            }
                            $price_square_foot = maskMoney($sale_p/$build_size);
                        }
                    }
                }
                $response.="<tr id='".$comp->c_id ."'>   
                                <td class='text-center'><input type='checkbox' class='filled-in select_comp' name='select_comp".$comp->c_id."' id='select_comp".$comp->c_id."' value='".$comp->c_id."'><label for='select_comp".$comp->c_id."'></label></td>
                                <td class='text-center'><img src='".cdn_url($comp->property_image_url)."' width='70px'></td>
                                <td>".$comp->street_address.", ".$comp->city."</td>
                                <td data-sort='".$sale_price."'>".$sale_price."</td>
                                <td data-sort='".$price_square_foot."'>".$price_square_foot."</td>
                                <td data-sort='".$comp->cap_rate."'>".maskPercentage($comp->cap_rate)."</td>
                                <td data-sort='".$comp->building_size."'>".$building_size."</td>
                                <td>".maskAreaNumber($comp->land_size)."</td>
                                <td data-sort='".formatDate("Ymd", $comp->date_sold)."'>".date("m/d/Y",strtotime($comp->date_sold))."</td>
                                <td></td>
                            </tr>";
            }
        }
        echo json_encode($response);
    }
}