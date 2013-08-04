<?php

abstract class CM_Form_Abstract extends CM_View_Abstract {

	/**
	 * The name of a form.
	 *
	 * @var string
	 */
	private $name;

	/**
	 * An array of fields objets.
	 *
	 * @var array
	 */
	private $_fields = array();

	/**
	 * Form actions.
	 *
	 * @var array
	 */
	private $actions = array();

	public function __construct() {
		if (!preg_match('/^\w+_Form_(.+)$/', get_class($this), $matches)) {
			throw new CM_Exception("Cannot detect namespace from forms class-name");
		}
		$namespace = lcfirst($matches[1]);
		$namespace = preg_replace('/([A-Z])/', '_\1', $namespace);
		$namespace = strtolower($namespace);
		$this->name = $namespace;
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
	 * Register and setup a form field.
	 *
	 * @param CM_FormField_Abstract $field
	 */
	protected function registerField(CM_FormField_Abstract $field) {
		$field_key = $field->getName();

		if (isset($this->_fields[$field_key])) {
			throw new CM_Exception_Invalid('Form field `' . $field_key . '` is already registered.');
		}

		$this->_fields[$field_key] = $field;
	}

	/**
	 * @param CM_FormAction_Abstract $action
	 */
	protected function registerAction(CM_FormAction_Abstract $action) {
		$action->setup($this);
		$action_name = $action->getName();
		if (isset($this->actions[$action_name])) {
			throw new CM_Exception_Invalid('Form action `' . $action_name . '` is already registered.');
		}
		$this->actions[$action_name] = $action;
	}

	/**
	 * Get the name of a form.
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}

	/**
	 * @return CM_FormAction_Abstract[]
	 */
	public function getActions() {
		return $this->actions;
	}

	/**
	 * Get the reference to a form action object.
	 *
	 * @param string $name
	 * @return CM_FormAction_Abstract
	 * @throws CM_Exception_Invalid
	 */
	public function getAction($name) {
		if (!isset($this->actions[$name])) {
			throw new CM_Exception_Invalid('Unrecognized action `' . $name . '`.');
		}
		return $this->actions[$name];
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
		foreach ($action->getFields() as $field_name => $required) {
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
