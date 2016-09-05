<?php

class CM_Redis_Client extends CM_Class_Abstract implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /** @var Predis\Client */
    private $_redis = null;

    /** @var  array */
    private $_config;

    /**
     * @param array $config ['host' => string, 'port' => int, 'database' => int|null]
     * @throws CM_Exception
     */
    public function __construct(array $config) {
        $this->_config = $this->_parseConfig($config);
        $this->_redis = $this->_createPredisClient($this->_config['host'], $this->_config['port'], $this->_config['database']);
    }

    /**
     * @param array $config ['host' => string, 'port' => int, 'database' => int|null]
     * @return array
     */
    private function _parseConfig(array $config) {
        $config = array_merge([
            'host'     => '127.0.0.1',
            'port'     => 6379,
            'database' => null,
        ], $config);

        if (null !== $config['database']) {
            $config['database'] = (int) $config['database'];
        }

        return [
            'host'     => (string) $config['host'],
            'port'     => (int) $config['port'],
            'database' => $config['database'],
        ];
    }

    /**
     * @param string      $host
     * @param int         $port
     * @param string|null $database
     * @param float|null  $timeout            in seconds (default: 5.0)
     * @param float|null  $read_write_timeout in seconds, disabled with -1 (default: 60.0)
     * @return \Predis\Client
     * @throws CM_Exception
     */
    private function _createPredisClient($host, $port, $database = null, $timeout = null, $read_write_timeout = null) {
        if (null === $timeout) {
            $timeout = 5.0;
        }
        if (null === $read_write_timeout) {
            $timeout = 60.0;
        }
        try {
            $client = new Predis\Client([
                'scheme'             => 'tcp',
                'host'               => (string) $host,
                'port'               => (int) $port,
                'read_write_timeout' => (float) $read_write_timeout,
                'timeout'            => (float) $timeout,
            ], ['profile' => '2.4']);
        } catch (Predis\Connection\ConnectionException $e) {
            throw new CM_Exception('Cannot connect to redis server', null, [
                'host'                     => $host,
                'port'                     => $port,
                'originalExceptionMessage' => $e->getMessage(),
            ]);
        }
        if (null !== $database) {
            $client->select($database);
        }
        return $client;
    }

    /**
     * @param string $key
     * @return string|false
     */
    public function get($key) {
        $value = $this->_redis->get($key);
        return is_null($value) ? false : $value;
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
     * @param int    $ttl
     */
    public function expire($key, $ttl) {
        $this->_redis->expire($key, $ttl);
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
        try {
            $this->_redis->lPush($key, $value);
        } catch (Predis\Response\ServerException $e) {
            throw new CM_Exception_Invalid('Cannot push key to list.', null, ['key' => $key]);
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
        try {
            $this->_redis->rPush($key, $value);
        } catch (Predis\Response\ServerException $e) {
            throw new CM_Exception_Invalid('Cannot push key to list.', null, ['key' => $key]);
        }
    }

    /**
     * Remove and return a value from a list
     *
     * @param string $key
     * @return string|null
     */
    public function rPop($key) {
        return $this->_redis->rPop($key);
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
        try {
            $length = $this->_redis->lLen($key);
        } catch (Predis\Response\ServerException $e) {
            throw new CM_Exception_Invalid('Key does not contain a list', null, ['key' => $key]);
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
        try {
            $this->_redis->lTrim($key, $start, $stop);
        } catch (Predis\Response\ServerException $e) {
            throw new CM_Exception_Invalid('Key does not contain a list', null, ['key' => $key]);
        }
    }

    /**
     * @param string $key
     * @param float  $score
     * @param string $value
     */
    public function zAdd($key, $score, $value) {
        $this->_redis->zAdd($key, [$value => $score]);
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
     * @return int
     */
    public function zCard($key) {
        return $this->_redis->zCard($key);
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
     * Returns the set cardinality (number of elements) of the set stored at key.
     *
     * @param string $key
     * @return int
     */
    public function sCard($key) {
        return $this->_redis->sCard($key);
    }

    /**
     * Add a value to a set
     *
     * @param string $key
     * @param string $value
     * @return int
     */
    public function sAdd($key, $value) {
        return $this->_redis->sAdd($key, $value);
    }

    /**
     * Remove a value from a set
     *
     * @param string $key
     * @param string $value
     * @return int
     */
    public function sRem($key, $value) {
        return $this->_redis->sRem($key, $value);
    }

    /**
     * Remove and return all members of a set
     *
     * @param string $key
     * @return string[]
     */
    public function sFlush($key) {
        $this->_redis->multi();
        $this->_redis->sMembers($key);
        $this->_redis->del($key);
        return $this->_redis->exec()[0];
    }

    /**
     * @param string $channel
     * @param string $message
     * @return int
     */
    public function publish($channel, $message) {
        return $this->_redis->publish($channel, $message);
    }

    /**
     * @param string|string[] $channels
     * @param Closure         $callback
     * @return mixed return something else than null to exit the pubsub loop
     */
    public function subscribe($channels, Closure $callback) {
        $redisClient = $this->_createPredisClient($this->_config['host'], $this->_config['port'], $this->_config['database'], 60, -1);

        $pubsub = $redisClient->pubSubLoop(['subscribe' => $channels]);
        $response = null;

        /** @var stdClass $message */
        foreach ($pubsub as $message) {
            try {
                if ($message->kind == 'message') {
                    $response = $callback($message->channel, $message->payload);
                }
            } catch (Exception $e) {
                $this->getServiceManager()->getLogger()->logException($e, null, 'Cannot execute callback for redis message');
            }
            if (!is_null($response)) {
                break;
            }
        }
        return $response;
    }

    public function flush() {
        $this->_redis->flushDb();
    }

    public function flushAll() {
        $this->_redis->flushAll();
    }

    /**
     * @param int $database
     * @throws CM_Exception
     */
    protected function _select($database) {
        $database = (int) $database;
        if ('OK' !== $this->_redis->select($database)) {
            throw new CM_Exception('Cannot select database.', null, ['database' => $database]);
        }
    }
}
