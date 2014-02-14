<?php

/**
 * Uses 'phpredis' extension: https://github.com/nicolasff/phpredis
 */
class CM_Redis_Client extends CM_Class_Abstract {

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
	 * @param string $key
	 * @return string|false
	 */
	public function get($key) {
		return $this->_redis->get($key);
	}

	/**
	 * @param string $key
	 * @param string $value
	 * @return string|null
	 */
	public function set($key, $value) {
		$this->_redis->set($key, $value);
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function exists($key) {
		return $this->_redis->exists($key);
	}

	/**
	 * @param string $key
	 * @param int    $timestamp
	 */
	public function expireAt($key, $timestamp) {
		$this->_redis->expireAt($key, $timestamp);
	}

	/**
	 * Prepend a value to a list
	 *
	 * @param string $key
	 * @param string $value
	 * @throws CM_Exception_Invalid
	 */
	public function lPush($key, $value) {
		$length = $this->_redis->lPush($key, $value);
		if (false === $length) {
			throw new CM_Exception_Invalid('Cannot push to list `' . $key . '`.');
		}
	}

	/**
	 * Append a value to a list
	 *
	 * @param string $key
	 * @param string $value
	 * @throws CM_Exception_Invalid
	 */
	public function rPush($key, $value) {
		$length = $this->_redis->rPush($key, $value);
		if (false === $length) {
			throw new CM_Exception_Invalid('Cannot push to list `' . $key . '`.');
		}
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
	 * Return values from list
	 *
	 * @param string   $key
	 * @param int|null $start
	 * @param int|null $stop
	 * @return array
	 */
	public function lRange($key, $start = null, $stop = null) {
		if (null === $start) {
			$start = 0;
		}
		if (null === $stop) {
			$stop = -1;
		}
		return $this->_redis->lRange($key, $start, $stop);
	}

	/**
	 * @param string $key
	 * @return int
	 * @throws CM_Exception_Invalid
	 */
	public function lLen($key) {
		$length = $this->_redis->lLen($key);
		if (false === $length) {
			throw new CM_Exception_Invalid('Key `' . $key . '` does not contain a list');
		}
		return $length;
	}

	/**
	 * @param string $key
	 * @param int    $start
	 * @param int    $stop
	 * @throws CM_Exception_Invalid
	 */
	public function lTrim($key, $start, $stop) {
		$result = $this->_redis->lTrim($key, $start, $stop);
		if (false === $result) {
			throw new CM_Exception_Invalid('Key `' . $key . '` does not contain a list');
		}
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

	/**
	 * Add a value to a set
	 *
	 * @param string $key
	 * @param string $value
	 */
	public function sAdd($key, $value) {
		$this->_redis->sAdd($key, $value);
	}

	/**
	 * Remove a value from a set
	 *
	 * @param string $key
	 * @param string $value
	 */
	public function sRem($key, $value) {
		$this->_redis->sRem($key, $value);
	}

	/**
	 * Remove and return all members of a set
	 *
	 * @param string $key
	 * @return string[]
	 */
	public function sFlush($key) {
		$values = $this->_redis->multi()->sMembers($key)->delete($key)->exec();
		return $values[0];
	}

	/**
	 * @param string $channel
	 * @param string $msg
	 */
	public function publish($channel, $msg) {
		$this->_redis->publish($channel, $msg);
	}

	/**
	 * @param string $channels
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
			CM_Bootloader::getInstance()->getExceptionHandler()->handleException($e);
		}
	}

	public function flush() {
		$this->_redis->flushAll();
	}

	/**
	 * @return CM_Redis_Client
	 */
	public static function getInstance() {
		static $instance;
		if (!$instance) {
			$instance = new self();
		}
		return $instance;
	}
}
