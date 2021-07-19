<?php

if (!function_exists('get_message')){
    function get_message($code, $entity = NULL) {
        $CI =& get_instance();
        $message = $CI->lang->line($code);

        if(isset($entity)) {
            $message = str_replace('{0}', $entity, $message);
        }

        return $message;
    }
}

if (!function_exists('flash_message')){
    function flash_message($code, $entity = NULL) {
        $CI =& get_instance();
        $CI->session->set_flashdata('message', get_message($code, $entity));
    }
}
