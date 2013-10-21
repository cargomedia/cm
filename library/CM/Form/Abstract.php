<?php

abstract class CM_Form_Abstract extends CM_View_Abstract {

	/** @var string */
	private $_name;

	/** @var array */
	private $_fields = array();

	/** @var CM_FormAction_Abstract[] */
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

	public static function factory($className, $params = null, CM_Model_User $viewer = null) {
		$className = (string) $className;
		if (!class_exists($className) || !is_subclass_of($className, __CLASS__)) {
			throw new CM_Exception_Invalid('Illegal form name `' . $className . '`.');
		}
		$form = new $className();
		return $form;
	}

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
	 * @param string                $fieldName
	 * @param CM_FormField_Abstract $field
	 * @throws CM_Exception_Invalid
	 */
	protected function registerField($fieldName, CM_FormField_Abstract $field) {
		$fieldName = (string) $fieldName;
		if (isset($this->_fields[$fieldName])) {
			throw new CM_Exception_Invalid('Form field `' . $fieldName . '` is already registered.');
		}

		$this->_fields[$fieldName] = $field;
	}

	/**
	 * @param CM_FormAction_Abstract $action
	 * @throws CM_Exception_Invalid
	 */
	protected function registerAction(CM_FormAction_Abstract $action) {
		$actionName = $action->getName();
		if (isset($this->_actions[$actionName])) {
			throw new CM_Exception_Invalid('Form action `' . $actionName . '` is already registered.');
		}
		$this->_actions[$actionName] = $action;
	}

	/**
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
	 * @param string $fieldName
	 * @return CM_FormField_Abstract
	 * @throws CM_Exception_Invalid
	 */
	public function getField($fieldName) {
		if (!isset($this->_fields[$fieldName])) {
			throw new CM_Exception_Invalid('Unrecognized field `' . $fieldName . '`.');
		}
		return $this->_fields[$fieldName];
	}

	/**
	 * @param string $id_value
	 * @return string
	 */
	final public function getTagAutoId($id_value) {
		return $this->getAutoId() . '-' . $id_value;
	}

	/**
	 * @param array                 $data
	 * @param string                $actionName
	 * @param CM_Response_View_Form $response
	 * @return mixed
	 */
	public function process(array $data, $actionName, CM_Response_View_Form $response) {
		$action = $this->getAction($actionName);

		$formData = array();
		foreach ($action->getFieldList() as $fieldName => $required) {
			$field = $this->getField($fieldName);

			if (array_key_exists($fieldName, $data) && !$field->isEmpty($data[$fieldName])) {
				try {
					$formData[$fieldName] = $field->validate($data[$fieldName], $response);
				} catch (CM_Exception_FormFieldValidation $e) {
					$response->addError($e->getMessagePublic($response->getRender()), $fieldName);
				}
			} else {
				if ($required) {
					$response->addError($response->getRender()->getTranslation('Required'), $fieldName);
				} else {
					$formData[$fieldName] = null;
				}
			}
		}

		if (!$response->hasErrors()) {
			$action->checkData($formData, $response, $this);
		}

		if ($response->hasErrors()) {
			return null;
		}

		return $action->process($formData, $response, $this);
	}
}
