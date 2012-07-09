<?php

abstract class CM_FormAction_Abstract {
	/**
	 * The name of an action.
	 *
	 * @var string
	 */
	private $name;

	/**
	 * The list of $field_key=>$required pairs for the this action.
	 *
	 * @var array
	 */
	private $_fields = array();

	/**
	 * The list of fields which are required for this action.
	 *
	 * @var array
	 */
	protected $required_fields = array();

	/**
	 * Confirmation message language address.
	 *
	 * @var string
	 */
	private $confirm_msg_lang_addr;

	/**
	 * Constructor.
	 *
	 * @param string $action_name
	 */
	protected function __construct($action_name) {
		$this->name = $action_name;
	}

	/**
	 * Set a confirmation message for the action.
	 *
	 * @param string $lang_addr
	 */
	protected function setConfirmation($lang_addr) {
		$this->confirm_msg_lang_addr = $lang_addr;
	}

	/**
	 * Get a confirmation message of the action.
	 *
	 * @return string
	 */
	public function getConfirmation() {
		return $this->confirm_msg_lang_addr;
	}

	/**
	 * Returns the name of an action.
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return array name => required
	 */
	public function getFields() {
		return $this->_fields;
	}

	public function setup(CM_Form_Abstract $form) {
		foreach ($form->getFields() as $fieldName => $field) {
			$this->_fields[$fieldName] = in_array($fieldName, $this->required_fields);
		}

	}

	final public function js_presentation() {
		$data = array();
		$data['fields'] = $this->_fields;

		if ($this->confirm_msg_lang_addr) {
			$data['confirm_msg'] = $this->confirm_msg_lang_addr;
		}

		return json_encode($data);

	}

	/**
	 * An optional abstraction method for action entry data validation.
	 *
	 * @param array $data
	 * @param CM_Response_View_Form $response
	 * @param CM_Form_Abstract $form
	 */
	public function checkData(array $data, CM_Response_View_Form $response, CM_Form_Abstract $form) {
		$this->_checkData(CM_Params::factory($data), $response, $form);
	}

	/**
	 * @param array                 $data
	 * @param CM_Response_View_Form $response
	 * @param CM_Form_Abstract      $form
	 */
	public function process(array $data, CM_Response_View_Form $response, CM_Form_Abstract $form) {
		$this->_process(CM_Params::factory($data), $response, $form);
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
	 */
	protected function _process(CM_Params $params, CM_Response_View_Form $response, CM_Form_Abstract $form) {

	}

}
