<?php

class CM_Exception extends Exception {

	/** @var string|null */
	private $_messagePublic;

	/** @var array */
	private $_variables;

	/**
	 * @param string|null $message
	 * @param string|null $messagePublic
	 * @param array|null  $variables
	 */
	public function __construct($message = null, $messagePublic = null, array $variables = null) {
		$this->_messagePublic = $messagePublic;
		$this->_variables = (array) $variables;
		parent::__construct($message);
	}

	/**
	 * @param CM_Render $render
	 * @return string
	 */
	public function getMessagePublic(CM_Render $render) {
		if (!$this->isPublic()) {
			return 'Internal server error';
		}
		return $render->getTranslation($this->_messagePublic, $this->_variables);
	}

	/**
	 * @return boolean
	 */
	public function isPublic() {
		return (null !== $this->_messagePublic);
	}
}
