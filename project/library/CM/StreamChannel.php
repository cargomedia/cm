<?php

class CM_StreamChannel extends CM_Class_Abstract {

	/**
	 * @var CM_VideoStream_Publish
	 */
	private $_streamPublish;
	/**
	 * @var CM_VideoStreamDelegate
	 */
	private $_streamDelegate;

	/**
	 * @param CM_VideoStream_Publish $stream
	 */
	public function __construct(CM_VideoStream_Publish $stream) {
		$this->_streamPublish = $stream;
		$config = self::_getConfig();
		if (empty($config->delegates[$this->getStreamPublish()->getDelegateType()])) {
			throw new CM_Exception_Invalid('Invalid delegateType `' . $this->getStreamPublish()->getDelegateType());
		}
		$delegateClass = $config->delegates[$this->getStreamPublish()->getDelegateType()];
		$this->setStreamDelegate(new $delegateClass());
	}

	/**
	 * @return CM_VideoStream_Publish
	 */
	public function getStreamPublish() {
		return $this->_streamPublish;
	}

	/**
	 * @param CM_Params|null $params
	 * @return boolean
	 */
	public function onPublish(CM_Params $params = null) {
		return $this->_streamDelegate->onPublish($this->getStreamPublish(), $params);
	}

	/**
	 * @param CM_VideoStream_Subscribe $streamSubscribe
	 * @param CM_Params|null $params
	 * @return boolean
	 */
	public function onSubscribe(CM_VideoStream_Subscribe $streamSubscribe, CM_Params $params = null) {
		return $this->_streamDelegate->onSubscribe($streamSubscribe, $params);
	}

	public function onUnpublish() {
		return $this->_streamDelegate->onUnpublish($this->getStreamPublish());
	}

	/**
	 * @param CM_VideoStream_Subscribe $streamSubscribe
	 */
	public function onUnsubscribe(CM_VideoStream_Subscribe $streamSubscribe) {
		return $this->_streamDelegate->onUnsubscribe($streamSubscribe);
	}

	public function setStreamDelegate(CM_VideoStreamDelegate $streamDelegate) {
		$this->_streamDelegate = $streamDelegate;
	}
}
