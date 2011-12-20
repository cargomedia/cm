<?php

abstract class CM_FormField_Suggest extends CM_FormField_Abstract {

	/**
	 * @param string $name
	 * @param int $cardinality OPTIONAL
	 * @param array $options OPTIONAL
	 */
	public function __construct($name, $cardinality = null) {
		parent::__construct($name);
		$this->_options['cardinality'] = isset($cardinality) ? ((int) $cardinality) : null;
	}

	/**
	 * @param string $term
	 * @param array $options
	 * @return array list(list('id' => $id, 'name' => $name[, 'description' => $description, 'img' => $img]))
	 */
	protected static function _getSuggestions($term, array $options) {
		throw new CM_Exception_NotImplemented();
	}

	/**
	 * @param mixed $item
	 * @return array list('id' => $id, 'name' => $name[, 'description' => $description, 'img' => $img])
	 */
	protected static function _getSuggestion($item) {
		throw new CM_Exception_NotImplemented();
	}

	/**
	 * @param string $term
	 * @param array $options
	 * @return array
	 */
	public static function rpc_suggest($term, array $options) {
		$suggestions = static::_getSuggestions($term, $options);
		return $suggestions;
	}

	public function render(array $params, CM_Form_Abstract $form) {
		$params['class'] = isset($params['class']) ? (string) $params['class'] : null;
		if ($this->_getValue()) {
			$params['prePopulate'] = array_map(array('static', '_getSuggestion'), $this->_getValue());
		}

		return parent::render($params, $form);
	}

	/**
	 * @param string $userInput
	 * @return string[]
	 */
	public function validate($userInput) {
		$values = explode(',', $userInput);
		$values = array_unique($values);
		if ($this->_options['cardinality'] && count($values) > $this->_options['cardinality']) {
			throw new CM_FormFieldValidationException('Too many elements.');
		}
		return $values;
	}
}
