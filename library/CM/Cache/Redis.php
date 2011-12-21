<?php

/**
 * Local redis cache
 * Uses 'phpredis' extension: https://github.com/nicolasff/phpredis
 *
 */

class CM_Cache_Redis extends CM_Cache_Abstract {
	protected static $_instance;
	private $_redis = null;

	public function __construct() {
		$this->_redis = new Redis();
		$server = Config::get()->cache->redis->server;
		try {
			$this->_redis->connect($server[0], $server[1]);
		} catch (RedisException $e) {
			throw new CM_Exception('Cannot connect to redis server: ' . $e->getMessage());
		}
	}

	protected static function _enabled() {
		return Config::get()->cache->redis->enabled;
	}

	protected function _getName() {
		return 'Redis';
	}

	protected function _set($key, $data, $lifeTime = null) {
		throw new CM_Exception_NotImplemented();
	}

	protected function _get($key) {
		throw new CM_Exception_NotImplemented();
	}

	protected function _delete($key) {
		throw new CM_Exception_NotImplemented();
	}

	/**
	 * Add a value to a set
	 *
	 * @param string $key
	 * @param string $value
	 */
	protected function _sAdd($key, $value) {
		return $this->_redis->sAdd($key, $value);
	}
	
	/**
	 * @param string $channel
	 * @param string $msg
	 */
	protected function _publish($channel, $msg) {
		$this->_redis->publish($channel, $msg);
	}

	protected function _flush() {
		return $this->_redis->flushAll();
	}

}
