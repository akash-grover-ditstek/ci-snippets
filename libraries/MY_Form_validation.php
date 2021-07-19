<?php

class MY_Form_validation extends CI_Form_validation {

    public function __construct()
    {
        parent::__construct();

        $this->_error_prefix = '<p class="red-text">';
        $this->_error_suffix = '</p>';
    }
}