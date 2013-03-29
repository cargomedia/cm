<?php

class CM_Exception extends Exception {

	const WARN = 1;
	const ERROR = 2;
	const FATAL = 3;

	/** @var string|null */
	private $_messagePublic;

	/** @var array */
	private $_variables;

	/** @var int */
	protected $_severity = self::ERROR;

	/**
	 * @param string|null $message
	 * @param string|null $messagePublic
	 * @param array|null  $variables
	 * @param int|null    $severity
	 */
	public function __construct($message = null, $messagePublic = null, array $variables = null, $severity = null) {
		$this->_messagePublic = $messagePublic;
		$this->_variables = (array) $variables;
		if (null !== $severity) {
			$this->setSeverity($severity);
		}
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

	/**
	 * @return int
	 */
	public function getSeverity() {
		return $this->_severity;
	}

	/**
	 * @param int $severity
	 * @throws CM_Exception_Invalid
	 */
	public function setSeverity($severity) {
		if (!in_array($severity, array(self::WARN, self::ERROR, self::FATAL), true)) {
			throw new CM_Exception_Invalid('Invalid severity `' . $severity . '`');
		}
		$this->_severity = $severity;
	}

	/**
	 * @return CM_Paging_Log_Error|CM_Paging_Log_Fatal|CM_Paging_Log_Warn
	 */
	public function getLog() {
		switch ($this->getSeverity()) {
			case self::WARN:
				return new CM_Paging_Log_Warn();
				break;
			case self::ERROR:
				return new CM_Paging_Log_Error();
				break;
			case self::FATAL:
			default:
				return new CM_Paging_Log_Fatal();
				break;
		}
	}
}
