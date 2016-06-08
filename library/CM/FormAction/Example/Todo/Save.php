<?php

class CM_FormAction_Example_Todo_Save extends CM_FormAction_Abstract {

    protected function _getRequiredFields() {
        return [
            'title',
            'description',
            'state',
        ];
    }

    protected function _process(CM_Params $params, CM_Http_Response_View_Form $response, CM_Form_Abstract $form) {
        $todo = $params->has('todoId') ? new CM_Model_Example_Todo($params->getInt('todoId')) : new CM_Model_Example_Todo();
        $todo->setTitle($params->getString('title'));
        $todo->setDescription($params->getString('description'));
        $todo->setState($params->getInt('state'));
        $todo->commit();
    }
}
