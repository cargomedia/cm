<?php

class CM_Stream_Video extends CM_Stream_Abstract {

	/** @var CM_Stream_Video */
	private static $_instance;

	public function checkStreams() {
		$this->getAdapter()->checkStreams();
	}

	public function synchronize() {
		$this->getAdapter()->synchronize();
	}

	/**
	 * @param CM_Model_Stream_Abstract $stream
	 * @throws CM_Exception_Invalid
	 */
	public function stopStream(CM_Model_Stream_Abstract $stream) {
		$this->getAdapter()->stopStream($stream);
	}

	/**
	 * @return array[]
	 */
	public function getServers() {
		return (array) self::_getConfig()->servers;
	}

	/**
	 * @param string|null $serverId
	 * @throws CM_Exception_Invalid
	 * @return array
	 */
	public function getServer($serverId = null) {
		$servers = $this->getServers();
		if (null === $serverId) {
			$serverId = array_rand($servers);
		}

		$serverId = (int) $serverId;
		if (!array_key_exists($serverId, $servers)) {
			throw new CM_Exception_Invalid("No video server with id `$serverId` found");
		}
		return $servers[$serverId];
	}

	/**
	 * @return CM_Stream_Adapter_Video_Abstract
	 */
	public function getAdapter() {
		return parent::getAdapter();
	}

	/**
	 * @param string  $streamName
	 * @param string  $clientKey
	 * @param int     $start
	 * @param int     $width
	 * @param int     $height
	 * @param int     $thumbnailCount
	 * @param string  $data
	 * @return bool
	 */
	public static function rpc_publish($streamName, $clientKey, $start, $width, $height, $thumbnailCount, $data) {
		$request = CM_Request_Abstract::getInstance();
		$serverId = self::getInstance()->getAdapter()->getServerId($request);

		$channelId = self::getInstance()->getAdapter()->publish($streamName, $clientKey, $start, $width, $height, $serverId, $thumbnailCount, $data);
		return $channelId;
	}

	/**
	 * @param string   $streamName
	 * @param int      $thumbnailCount
	 * @return bool
	 */
	public static function rpc_unpublish($streamName, $thumbnailCount) {
		$adapter = self::getInstance()->getAdapter();
		$adapter->getServerId(CM_Request_Abstract::getInstance());
		$adapter->unpublish($streamName, $thumbnailCount);
		return true;
	}

	/**
	 * @param string $streamName
	 * @param string $clientKey
	 * @param string $start
	 * @param string $data
	 * @return boolean
	 */
	public static function rpc_subscribe($streamName, $clientKey, $start, $data) {
		$adapter = self::getInstance()->getAdapter();
		$adapter->getServerId(CM_Request_Abstract::getInstance());
		$adapter->subscribe($streamName, $clientKey, $start, $data);
		return true;
	}

	/**
	 * @param string $streamName
	 * @param string $clientKey
	 * @return boolean
	 */
	public static function rpc_unsubscribe($streamName, $clientKey) {
		$adapter = self::getInstance()->getAdapter();
		$adapter->getServerId(CM_Request_Abstract::getInstance());
		$adapter->unsubscribe($streamName, $clientKey);
		return true;
	}

	/**
	 * @return CM_Stream_Video
	 */
	public static function getInstance() {
		if (!self::$_instance) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
}
