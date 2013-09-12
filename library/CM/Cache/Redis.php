<?php

/**
 * Uses 'phpredis' extension: https://github.com/nicolasff/phpredis
 */

class CM_Cache_Redis extends CM_Cache_Abstract {

	protected static $_instance;

	/** @var Redis */
	private $_redis = null;

	/** @var Closure|null */
	private $_subscribeCallback;

	public function __construct() {
		$this->_redis = new Redis();
		$server = self::_getConfig()->server;
		try {
			$this->_redis->connect($server['host'], $server['port']);
		} catch (RedisException $e) {
			throw new CM_Exception('Cannot connect to redis server `' . $server['host'] . '` on port `' . $server['port'] . '`: ' . $e->getMessage());
		}
	}

	/**
	 * Add a value to a list
	 *
	 * @param string $key
	 * @param string $value
	 */
	public function lPush($key, $value) {
		$this->_redis->lPush($key, $value);
	}

	/**
	 * Remove and return a value from a list
	 *
	 * @param string $key
	 * @return string|null
	 */
	public function rPop($key) {
		$result = $this->_redis->rPop($key);
		if (false === $result) {
			$result = null;
		}
		return $result;
	}

	/**
	 * @param string $key
	 * @param float  $score
	 * @param string $value
	 */
	public function zAdd($key, $score, $value) {
		$this->_redis->zAdd($key, $score, $value);
	}

	/**
	 * @param string       $key
	 * @param string       $start
	 * @param string       $end
	 * @param int|null     $count
	 * @param int|null     $offset
	 * @param boolean|null $returnScore
	 * @return array
	 */
	public function zRangeByScore($key, $start, $end, $count = null, $offset = null, $returnScore = null) {
		$options = array();
		if (null !== $count || null !== $offset) {
			$count = (null !== $count) ? (int) $count : -1;
			$offset = (null !== $offset) ? (int) $offset : 0;
			$options['limit'] = array($offset, $count);
		}
		if ($returnScore) {
			$options['withscores'] = true;
		}
		return $this->_redis->zRangeByScore($key, $start, $end, $options);
	}

	/**
	 * @param string $key
	 * @param string $value
	 */
	public function zRem($key, $value) {
		$this->_redis->zRem($key, $value);
	}

	/**
	 * @param string $key
	 * @param string $start
	 * @param string $end
	 */
	public function zRemRangeByScore($key, $start, $end) {
		$this->_redis->zRemRangeByScore($key, $start, $end);
	}

	/**
	 * @param string       $key
	 * @param string       $start
	 * @param string       $end
	 * @param boolean|null $returnScore
	 * @return array
	 */
	public function zPopRangeByScore($key, $start, $end, $returnScore = null) {
		$this->_redis->multi();
		$this->zRangeByScore($key, $start, $end, null, null, $returnScore);
		$this->zRemRangeByScore($key, $start, $end);
		$result = $this->_redis->exec();
		return $result[0];
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
		$this->_redis->sAdd($key, $value);
	}

	/**
	 * Remove a value from a set
	 *
	 * @param string $key
	 * @param string $value
	 */
	protected function _sRem($key, $value) {
		$this->_redis->sRem($key, $value);
	}

	/**
	 * Remove and return all members of a set
	 *
	 * @param string $key
	 * @return string[]
	 */
	protected function _sFlush($key) {
		$values = $this->_redis->multi()->sMembers($key)->delete($key)->exec();
		return $values[0];
	}

	/**
	 * @param string $channel
	 * @param string $msg
	 */
	protected function _publish($channel, $msg) {
		$this->_redis->publish($channel, $msg);
	}

	/**
	 * @param string|string[] $channels
	 * @param Closure         $callback
	 */
	public function subscribe($channels, Closure $callback) {
		$channels = (array) $channels;
		$this->_subscribeCallback = $callback;
		$this->_redis->setOption(Redis::OPT_READ_TIMEOUT, 86400 * 100);
		$this->_redis->subscribe($channels, array($this, '_subscribeCallback'));
	}

	/**
	 * @param Redis  $redis
	 * @param string $channel
	 * @param string $message
	 */
	public function _subscribeCallback($redis, $channel, $message) {
		try {
			$callback = $this->_subscribeCallback;
			$callback($channel, $message);
		} catch (Exception $e) {
			CM_Bootloader::getInstance()->handleException($e);
		}
	}

	protected function _flush() {
		$this->_redis->flushAll();
	}
}
