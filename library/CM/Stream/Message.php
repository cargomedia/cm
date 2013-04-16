<?php

class CM_Stream_Message extends CM_Stream_Abstract {

	/** @var CM_Stream_Message */
	private static $_instance;

	public function startSynchronization() {
		if (!$this->getEnabled()) {
			throw new CM_Exception('Stream is not enabled');
		}
		$this->getAdapter()->startSynchronization();
	}

	public function synchronize() {
		if (!$this->getEnabled()) {
			throw new CM_Exception('Stream is not enabled');
		}
		$this->getAdapter()->synchronize();
	}

	/**
	 * @return array
	 */
	public function getOptions() {
		return $this->getAdapter()->getOptions();
	}

	/**
	 * @param string     $channel
	 * @param mixed|null $data
	 */
	public function publish($channel, $data = null) {
		if (!$this->getEnabled()) {
			return;
		}
		$this->getAdapter()->publish($channel, CM_Params::encode($data));
	}

	/**
	 * @return CM_Stream_Message
	 */
	public static function getInstance() {
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * @return CM_Stream_Adapter_Message_Abstract
	 */
	public function getAdapter() {
		return parent::getAdapter();
	}
}
