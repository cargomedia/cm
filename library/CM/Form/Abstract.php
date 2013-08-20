<?php

abstract class CM_Form_Abstract extends CM_View_Abstract {

	/** @var string */
	private $_name;

	/** @var array */
	private $_fields = array();

	/** @var array */
	private $_actions = array();

	public function __construct() {
		if (!preg_match('/^\w+_Form_(.+)$/', get_class($this), $matches)) {
			throw new CM_Exception("Cannot detect namespace from forms class-name");
		}
		$namespace = lcfirst($matches[1]);
		$namespace = preg_replace('/([A-Z])/', '_\1', $namespace);
		$namespace = strtolower($namespace);
		$this->_name = $namespace;
	}

	/**
	 * @param string $className
	 * @return CM_Form_Abstract
	 * @throws CM_Exception
	 */
	public static function factory($className) {
		$className = (string) $className;
		if (!class_exists($className) || !is_subclass_of($className, __CLASS__)) {
			throw new CM_Exception('Illegal form name `' . $className . '`.');
		}
		$form = new $className();
		return $form;
	}

	/**
	 * Register a form fields and actions.
	 */
	abstract public function setup();

	/**
	 * @param array|null $params
	 */
	final public function renderStart(array $params = null) {
		$this->_renderStart(CM_Params::factory($params));
	}

	/**
	 * @param CM_Params $params
	 */
	protected function _renderStart(CM_Params $params) {
	}

	/**
	 * @param string                $fieldname
	 * @param CM_FormField_Abstract $field
	 * @throws CM_Exception_Invalid
	 */
	protected function registerField($fieldname, CM_FormField_Abstract $field) {
		$fieldname = (string) $fieldname;
		if (isset($this->_fields[$fieldname])) {
			throw new CM_Exception_Invalid('Form field `' . $fieldname . '` is already registered.');
		}

		$this->_fields[$fieldname] = $field;
	}

	/**
	 * @param CM_FormAction_Abstract $action
	 */
	protected function registerAction(CM_FormAction_Abstract $action) {
		$action_name = $action->getName();
		if (isset($this->_actions[$action_name])) {
			throw new CM_Exception_Invalid('Form action `' . $action_name . '` is already registered.');
		}
		$this->_actions[$action_name] = $action;
	}

	/**
	 * Get the name of a form.
	 *
	 * @return string
	 */
	public function getName() {
		return $this->_name;
	}

	/**
	 * @return CM_FormAction_Abstract[]
	 */
	public function getActions() {
		return $this->_actions;
	}

	/**
	 * Get the reference to a form action object.
	 *
	 * @param string $name
	 * @return CM_FormAction_Abstract
	 * @throws CM_Exception_Invalid
	 */
	public function getAction($name) {
		if (!isset($this->_actions[$name])) {
			throw new CM_Exception_Invalid('Unrecognized action `' . $name . '`.');
		}
		return $this->_actions[$name];
	}

	/**
	 * @return CM_FormField_Abstract[]
	 */
	public function getFields() {
		return $this->_fields;
	}

	/**
	 * Get the reference to a form field object.
	 *
	 * @param string $field_name
	 * @return CM_FormField_Abstract
	 * @throws CM_Exception_Invalid
	 */
	public function getField($field_name) {
		if (!isset($this->_fields[$field_name])) {
			throw new CM_Exception_Invalid('Unrecognized field `' . $field_name . '`.');
		}
		return $this->_fields[$field_name];
	}

	/**
	 * Get auto id prefixed id value for a form html element.
	 *
	 * @param string $id_value
	 * @return string
	 */
	final public function getTagAutoId($id_value) {
		return $this->getAutoId() . '-' . $id_value;
	}

	/**
	 * @param array                 $data
	 * @param string                $action_name
	 * @param CM_Response_View_Form $response
	 * @return mixed
	 */
	public function process(array $data, $action_name, CM_Response_View_Form $response) {
		$action = $this->getAction($action_name);

		$form_data = array();
		foreach ($action->getFieldList() as $field_name => $required) {
			$field = $this->getField($field_name);

			if (array_key_exists($field_name, $data) && !$field->isEmpty($data[$field_name])) {
				try {
					$form_data[$field_name] = $field->validate($data[$field_name], $response);
				} catch (CM_Exception_FormFieldValidation $e) {
					$response->addError($e->getMessagePublic($response->getRender()), $field_name);
				}
			} else {
				if ($required) {
					$response->addError($response->getRender()->getTranslation('Required'), $field_name);
				} else {
					$form_data[$field_name] = null;
				}
			}
		}

		if (!$response->hasErrors()) {
			$action->checkData($form_data, $response, $this);
		}

		if ($response->hasErrors()) {
			return null;
		}

		return $action->process($form_data, $response, $this);
	}
}
