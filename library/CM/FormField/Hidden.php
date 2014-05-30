<?php

class CM_FormField_Hidden extends CM_FormField_Abstract {

    public function validate($userInput, CM_Response_Abstract $response) {
        return $userInput;
    }
}
