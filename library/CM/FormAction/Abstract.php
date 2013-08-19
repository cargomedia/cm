<?php

abstract class CM_FormAction_Abstract {

	/** @var string */
	private $_name;

	/** @var CM_FormField_Abstract[] */
	private $_fieldList = array();

	/** @var string[] */
	protected $_fieldListRequired = array();

	/** @var string */
	private $confirm_msg_lang_addr;

	public function __construct() {
		if (!preg_match('/^\w+_FormAction_(.+_)*(.+)$/', get_class($this), $matches)) {
			throw new CM_Exception("Cannot detect action name from form action class name");
		}
		$actionName = lcfirst($matches[2]);
		$actionName = preg_replace('/([A-Z])/', '_\1', $actionName);
		$actionName = strtolower($actionName);
		$this->_name = $actionName;
	}

	/**
	 * @param string $lang_addr
	 */
	protected function setConfirmation($lang_addr) {
		$this->confirm_msg_lang_addr = $lang_addr;
	}

	/**
	 * @return string
	 */
	public function getConfirmation() {
		return $this->confirm_msg_lang_addr;
	}

	/**
	 * @return string
	 */
	public function getName() {
		return $this->_name;
	}

	/**
	 * @return array name => required
	 */
	public function getFieldList() {
		return $this->_fieldList;
	}

	/**
	 * @param CM_Form_Abstract $form
	 */
	public function setup(CM_Form_Abstract $form) {
		foreach ($form->getFields() as $fieldName => $field) {
			$this->_fieldList[$fieldName] = in_array($fieldName, $this->_fieldListRequired);
		}
	}

	/**
	 * @return string
	 */
	final public function js_presentation() {
		$data = array();
		$data['fields'] = $this->_fieldList;

		if ($this->confirm_msg_lang_addr) {
			$data['confirm_msg'] = $this->confirm_msg_lang_addr;
		}

		return json_encode($data);
	}

	/**
	 * @param array                 $data
	 * @param CM_Response_View_Form $response
	 * @param CM_Form_Abstract      $form
	 */
	final public function checkData(array $data, CM_Response_View_Form $response, CM_Form_Abstract $form) {
		$this->_checkData(CM_Params::factory($data), $response, $form);
	}

	/**
	 * @param array                 $data
	 * @param CM_Response_View_Form $response
	 * @param CM_Form_Abstract      $form
	 * @return mixed
	 */
	final public function process(array $data, CM_Response_View_Form $response, CM_Form_Abstract $form) {
		return $this->_process(CM_Params::factory($data), $response, $form);
	}

	/**
	 * @param CM_Params             $params
	 * @param CM_Response_View_Form $response
	 * @param CM_Form_Abstract      $form
	 */
	protected function _checkData(CM_Params $params, CM_Response_View_Form $response, CM_Form_Abstract $form) {
	}

	/**
	 * @param CM_Params             $params
	 * @param CM_Response_View_Form $response
	 * @param CM_Form_Abstract      $form
	 * @return mixed
	 */
	protected function _process(CM_Params $params, CM_Response_View_Form $response, CM_Form_Abstract $form) {
	}
}
