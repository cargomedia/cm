<?php

class CM_FormField_TreeSelect extends CM_FormField_Abstract {

	/** @var CM_Tree_Abstract */
	protected $_tree;

	/**
	 * @param string           $name
	 * @param CM_Tree_Abstract $tree
	 */
	public function __construct($name, CM_Tree_Abstract $tree) {
		$this->_tree = $tree;
		parent::__construct($name);
	}

	public function validate($userInput, CM_Response_Abstract $response) {
		if (!$this->_tree->findNodeById($userInput)) {
			throw new CM_Exception_FormFieldValidation('Invalid value');
		}
		return $userInput;
	}

	public function prepare(array $params) {
		$this->setTplParam('tree', $this->_tree);
	}
}
