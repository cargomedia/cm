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
	private $fields = array();

	/**
	 * The list of fields which are acts in this action.
	 *
	 * @var array
	 */
	protected $process_fields = array();

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
	 * The getter for {@link $this->fields}.
	 *
	 * @return array
	 */
	public function getProcessFields() {
		return $this->fields;
	}

	public function setup(CM_Form_Abstract $form) {
		if (empty($this->process_fields)) {
			$this->process_fields = array_keys($form->getFields());
		}

		foreach ($this->process_fields as $field_key) {
			$this->fields[$field_key] = in_array($field_key, $this->required_fields);
		}

	}

	final public function js_presentation() {
		$data = array();
		$data['fields'] = $this->fields;

		if ($this->confirm_msg_lang_addr) {
			$data['confirm_msg'] = $this->confirm_msg_lang_addr;
		}

		return json_encode($data);

	}

	/**
	 * The getter for $this->required_fields.
	 *
	 * @return array
	 */
	public function required_fields() {
		return $this->required_fields;
	}

	/**
	 * An optional abstraction method for action entry data validation.
	 *
	 * @param array $data
	 * @param CM_RequestHandler_Component_Form $response
	 * @param CM_Form_Abstract $form
	 */
	public function checkData(array $data, CM_RequestHandler_Component_Form $response, CM_Form_Abstract $form) {
	}

	abstract public function process(array $data, CM_RequestHandler_Component_Form $response, CM_Form_Abstract $form);

}
