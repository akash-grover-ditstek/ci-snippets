<?php

require_once(CLASSES_DIR  . "Client.php");
require_once(APPPATH . 'models/enum/UserRole.php');
require_once(APPPATH . 'models/enum/FlashMessageType.php');

class Clients extends MY_Controller {
    private $head;
    private $entity;

    public function __construct() {
        parent::__construct();


        if(!get_logged_user_account()) {
            redirect('/users');
        }

        $this->load->model('clients_model');
        $this->head['classname'] = $this->router->fetch_class();
        $this->entity = 'client';
    }

    public function index() {
        $data['clients'] = $this->clients_model->findAllObjects(null, true);

        $this->load->view('_partials/_head.php', $this->head);
        $this->load->view('_common/header.php');
        $this->load->view('clients/index.php', $data);
        $this->load->view('_common/footer.php');
    }

    public function show($id) {
        $data['client'] = $this->clients_model->findSharedClientObjectBy(['id' => $id]);

        if ($data['client']->getId()) {
            $this->load->view('_partials/_head.php', $this->head);
            $this->load->view('_common/header.php');
            $this->load->view('clients/show.php', $data);
            $this->load->view('_common/footer.php');
        } else {
            flash_message(FlashMessageType::NOT_FOUND, $this->entity);
            redirect('clients');
        }
    }

    public function create() {
        $data['client'] = new Client();

        $this->load->view('_partials/_head.php', $this->head);
        $this->load->view('_common/header.php');
        $this->load->view('clients/create.php', $data);
        $this->load->view('_common/footer.php');
    }

    public function edit($id) {
        $data['client'] = $this->clients_model->findOneObjectBy(['id' => $id]);

        if ($data['client']->getId()) {
            $this->load->view('_partials/_head.php', $this->head);
            $this->load->view('_common/header.php');
            $this->load->view('clients/edit.php', $data);
            $this->load->view('_common/footer.php');
        } else {
            flash_message(FlashMessageType::NO_PERMISSION, $this->entity);
            redirect('clients');
        }
    }

    public function submit() {
        if (!empty($this->input->post())) {
            $data = $this->input->post();

            $redirect = null;
            $action = null;

            if(isset($data['redirect'])) {
                $redirect = $data['redirect'];
                unset($data['redirect']);
            }

            if(isset($data['action'])) {
                $action = $data['action'];
                unset($data['action']);
            }

            if(!isset($data['shared'])) {
                $data['shared'] = false;
            }

            if(!isset($data['id'])) {
               $data['account_id'] = get_logged_user_account();
            }

            $id = $this->wggenerator->saveEntity($data);

            if (!$id) {
                redirect('clients/create');
            } else {
                if($redirect) {
                    if($action) {
                        redirect($redirect . '?action=' . $action . '&client_id=' . $id);
                    } else {
                        redirect($redirect);
                    }
                } else {
                    redirect('clients');
                }
            }
        }
    }

    public function delete($id) {
        $flash_message = FlashMessageType::NO_PERMISSION;

        if ($id) {
            $client = $this->clients_model->findOneObjectBy(['id' => $id]);

            if($client->canBeEdited()){
                $flash_message = $this->remove($id);
            }
        }

        flash_message($flash_message, $this->entity);

        redirect('clients');
    }

    private function remove($id){
        if($this->clients_model->delete(['id' => $id])){
            return FlashMessageType::REMOVED;
        }

        return FlashMessageType::CANT_REMOVE;
    }

    public function search() {
        $needle = $this->input->get('needle');

        $limit = $this->input->get('limit');

        if (isset($needle)) {
            $client = $this->clients_model->search($needle, $limit);
            if (isset($client) && !empty($client)) {
                echo json_encode($client);
            }  else {
                echo json_encode([]);
            }
        } else {
            echo json_encode([]);
        }
    }

    public function table_get() {
        $max = $this->input->get('length');
        $offset = $this->input->get('start');
        $order = $this->input->get('order');
        $args = $this->input->get('search');

        $Total = $this->clients_model->table_countAllBy(null, $this->clients_model->getPermissions());
        $List = $this->clients_model->table_get($args, $max, $offset, $order);
        $TotalFiltered = isset($args) && !empty($args)? $this->clients_model->table_countAllBy($args, $this->clients_model->getPermissions()): $Total;

        $data['recordsTotal'] = $Total;
        $data['recordsFiltered'] = $TotalFiltered;
        $data['data'] = $List;

        echo json_encode($data);
    }
}