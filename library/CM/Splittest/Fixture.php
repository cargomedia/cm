<?php

class CM_Splittest_Fixture extends CM_Class_Abstract {

	const TYPE_CLIENT = 1;
	const TYPE_USER = 2;

	/** @var int */
	protected $_type;

	/** @var  int */
	protected $_id;

	/**
	 * @param CM_Request_Abstract|CM_Model_User $fixture
	 * @throws CM_Exception_Invalid
	 */
	public function __construct($fixture) {
		if ($fixture instanceof CM_Request_Abstract) {
			$this->_id = $fixture->getClientId();
			$this->_type = self::TYPE_CLIENT;
		} elseif ($fixture instanceof CM_Model_User) {
			$this->_id = (int) $fixture->getId();
			$this->_type = self::TYPE_USER;
		} else {
			throw new CM_Exception_Invalid('Invalid fixture type');
		}
	}

	/**
	 * @return int
	 */
	public function getFixtureType() {
		return $this->_type;
	}

	/**
	 * @return int
	 */
	public function getId() {
		return $this->_id;
	}
}
