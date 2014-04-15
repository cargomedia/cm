<?php

class CM_FormField_Set_Select_Radio extends CM_FormField_Set_Select {

    public function validate($userInput, CM_Response_Abstract $response) {
        return $userInput;
    }

    public function prepare(CM_Params $renderParams, CM_ViewResponse $viewResponse) {
        $viewResponse->set('itemValue', $renderParams->getString('item'));
    }
}
