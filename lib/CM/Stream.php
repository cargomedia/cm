<?php

class CM_Stream {
	private static $_instance;
	private $_strategy;

	private function __construct() {
		if (Config::get()->stream->socketio->enabled) {
			$this->_strategy = new CM_Stream_Node();
		} else {
			$this->_strategy = new CM_Stream_Apache();
		}
	}

	/**
	 * Helper function to publish data with the method of the selected strategy. 
	 * This function should never be called outside of this class, it serves as an instance 
	 * function that is needed for the static publish function.
	 * @param string $channel
	 * @param mixed $data
	 */
	public function _publish($channel, $data) {
		if (!Config::get()->stream->enabled) {
			return;
		}
		$data = CM_Params::encode($data);
		
		$this->_strategy->publish($channel, $data);
	}

	/**
	 * @param string $channel
	 * @param int|null $idMin
	 * @return array|null
	 */
	private function _subscribe($channel, $idMin = null) {
		if (!Config::get()->stream->enabled) {
			return;
		}
		return $this->_strategy->subscribe($channel, $idMin);
	}

	/**
	 * Function to publish data $data through the channel with id $channel with the specified strategy (config option)
	 * @param string $channel
	 * @param mixed $data
	 */
	public static function publish($channel, $data) {
		self::_getInstance()->_publish($channel, $data);
	}

	/**
	 * @param string $channel
	 * @param int|null $idMin
	 * @return array|null
	 */
	public static function subscribe($channel, $idMin = null) {
		return self::_getInstance()->_subscribe($channel, $idMin);
	}

	/**
	 * @param CM_Model_User $recipient
	 * @param SK_Action_Abstract $action
	 * @param SK_Entity_Abstract $entity
	 * @param array $data OPTIONAL
	 */
	public static function publishAction(CM_Model_User $recipient, SK_Action_Abstract $action, SK_Entity_Abstract $entity, array $data = null) {
		if (!is_array($data)) {
			$data = array();
		}
		self::publishUser($recipient,
				array('namespace' => 'SK_Action_Abstract', 'data' => array('action' => $action, 'entity' => $entity, 'data' => $data)));
	}

	/**
	 * @param CM_Model_User $user
	 * @param array $data
	 */
	public static function publishUser(CM_Model_User $user, array $data) {
		if (!$user->getOnline()) {
			return;
		}
		self::publish(self::getStreamChannel($user), $data);
	}
	/**
	 * Returns the computed communication channel for the provided profile
	 * @param CM_Model_User $user
	 * @return string hash
	 */
	public static function getStreamChannel(CM_Model_User $user) {
		$salt = 'd98*2jflq74fçr8gföqwm&dsöwrds93"2d93tp+ihwd.20trl';
		$hash = hash('md5', $salt . ':' . $user->getId());
		return $hash;
	}

	protected static function _getInstance() {
		if (self::$_instance === null) {
			$_instance = new self();
		}
		return $_instance;
	}

}
