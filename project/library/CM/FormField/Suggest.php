<?php

abstract class CM_FormField_Suggest extends CM_FormField_Abstract {

	/**
	 * @param string $name
	 * @param int	$cardinality OPTIONAL
	 */
	public function __construct($name, $cardinality = null) {
		parent::__construct($name);
		$this->_options['cardinality'] = isset($cardinality) ? ((int) $cardinality) : null;
	}

	/**
	 * @param string $term
	 * @param array  $options
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

	public function prepare(array $params) {
		$this->setTplParam('class', isset($params['class']) ? (string) $params['class'] : null);
		if ($this->getValue()) {
			$this->setTplParam('prePopulate', array_map(array('static', '_getSuggestion'), $this->getValue()));
		}
	}

	/**
	 * @param string $userInput
	 * @return string[]
	 */
	function validate($userInput, CM_Response_Abstract $response) {
		$values = explode(',', $userInput);
		$values = array_unique($values);
		if ($this->_options['cardinality'] && count($values) > $this->_options['cardinality']) {
			throw new CM_Exception_FormFieldValidation('Too many elements.');
		}
		return $values;
	}

	public static function ajax_getSuggestions(CM_Params $params, CM_ComponentFrontendHandler $handler, CM_Response_View_Ajax $response) {
		$suggestions = static::_getSuggestions($params->getString('term'), $params->getArray('options'));
		return $suggestions;
	}
}
