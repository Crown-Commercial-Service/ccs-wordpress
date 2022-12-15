<?php

class cscMessageApi{

    public function getMessage(){

        $field = get_field("csc_message", 'option');
        $cscMessage = empty($field) ? "CCS customer services team is available Monday to Friday, 9am to 5pm." : $field;

        header('Content-Type: application/json');
        return rest_ensure_response(['csc_message' => $cscMessage]);
    }
}