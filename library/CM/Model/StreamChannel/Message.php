<?php

class CM_Model_StreamChannel_Message extends CM_Model_StreamChannel_Abstract {

	const TYPE = 18;

	public function onPublish(CM_Model_Stream_Publish $streamPublish) {
	}

	public function onSubscribe(CM_Model_Stream_Subscribe $streamSubscribe) {
	}

	public function onUnpublish(CM_Model_Stream_Publish $streamPublish) {
	}

	public function onUnsubscribe(CM_Model_Stream_Subscribe $streamSubscribe) {
	}

	/**
	 * @param string $key
	 * @return CM_Model_StreamChannel_Message|null
	 */
	public static function findByKey($key) {
		$adapterType = CM_Stream_Message::getInstance()->getAdapter()->getType();
		return self::findByKeyAndAdapter($key, $adapterType);
	}

	/**
	 * @param string $channel
	 * @throws CM_Exception_Invalid
	 * @return array ['key' => string, 'type' => int]
	 */
	public static function extractStatusChannelData($channel) {
		$channelParts = explode(':', $channel);
		if (count($channelParts) !== 2) {
			throw new CM_Exception_Invalid('Cannot extract key, type from channel `' . $channel . '`');
		}
		return array('key' => $channelParts[0], 'type' => (int) $channelParts[1]);
	}

	/**
	 * @param string     $streamChannel
	 * @param string     $namespace
	 * @param mixed|null $data
	 */
	public static function publish($streamChannel, $namespace, $data = null) {
		$streamChannel = $streamChannel . ':' . static::TYPE;
		CM_Stream_Message::getInstance()->publish($streamChannel, array('namespace' => $namespace, 'data' => $data));
	}

	/**
	 * @param string             $streamChannel
	 * @param CM_Action_Abstract $action
	 * @param CM_Model_Abstract  $model
	 * @param mixed|null         $data
	 */
	public static function publishAction($streamChannel, CM_Action_Abstract $action, CM_Model_Abstract $model, $data = null) {
		$namespace = 'CM_Action_Abstract' . ':' . $action->getVerb() . ':' . $model->getType();
		self::publish($streamChannel, $namespace, array('action' => $action, 'model' => $model, 'data' => $data));
	}

	/**
	 * @param string     $namespace
	 * @param mixed|null $data
	 */
	protected function _publish($namespace, $data = null) {
		self::publish($this->getKey(), $namespace, $data);
	}
}
