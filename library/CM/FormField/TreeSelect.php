<?php

class CM_FormField_TreeSelect extends CM_FormField_Abstract {

    /** @var CM_Tree_Abstract */
    protected $_tree;

    public function initialize() {
        $this->_tree = $this->_params->get('tree');
        parent::initialize();
    }

    public function validate($userInput, CM_Response_Abstract $response) {
        if (!$this->_tree->findNodeById($userInput)) {
            throw new CM_Exception_FormFieldValidation('Invalid value');
        }
        return $userInput;
    }

    public function prepare(CM_Params $renderParams, CM_Frontend_ViewResponse $viewResponse) {
        $viewResponse->set('tree', $this->_tree);
    }
}
