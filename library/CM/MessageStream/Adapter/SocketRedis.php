<?php

class CM_MessageStream_Adapter_SocketRedis extends CM_MessageStream_Adapter_Abstract implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    const SYNCHRONIZE_DELAY = 10;

    /** @var array */
    private $_servers;

    /**
     * @param array $servers
     */
    public function __construct(array $servers) {
        $this->_servers = $servers;
    }

    public function getOptions() {
        $servers = $this->_servers;
        if (empty($servers)) {
            throw new CM_Exception_Invalid('No servers configured');
        }
        $server = $servers[array_rand($servers)];
        $sockjsUrls = $server['sockjsUrls'];
        $sockjsUrl = $sockjsUrls[array_rand($sockjsUrls)];
        return array('sockjsUrl' => $sockjsUrl);
    }

    public function publish($channel, $event, $data) {
        $event = array('type' => 'publish', 'data' => array('channel' => $channel, 'event' => $event, 'data' => $data));
        $this->_getRedis()->publish('socket-redis-down', json_encode($event));
    }

    public function startSynchronization() {
        $this->_getRedis()->subscribe('socket-redis-up', function ($channel, $message) {
            $this->onRedisMessage($message);
        });
    }

    public function synchronize() {
        $startStampLimit = time() - self::SYNCHRONIZE_DELAY;
        $socketRedisStatus = $this->_fetchStatus();

        /** @var $channelsPersistenceItems CM_Model_Stream_Subscribe[] */
        $subscribesPersistenceArray = array();
        /** @var $subscribe CM_Model_Stream_Subscribe */
        foreach (new CM_Paging_StreamSubscribe_AdapterType($this->getType()) as $subscribe) {
            try {
                $channel = $subscribe->getStreamChannel();
                $statusChannelKey = $channel->getKey() . ':' . $channel->getType();
            } catch (CM_Exception_Nonexistent $e) {
                // For cases when streamChannel has been deleted during this iteration
                continue;
            }
            if (!isset($socketRedisStatus[$statusChannelKey]) || !isset($socketRedisStatus[$statusChannelKey]['subscribers'][$subscribe->getKey()])) {
                $subscribe->delete();
            } else {
                $subscribesPersistenceArray[$statusChannelKey . '/' . $subscribe->getKey()] = $subscribe;
            }
        }

        /** @var $channelsPersistenceArray CM_Model_StreamChannel_Abstract[] */
        $channelsPersistenceArray = array();
        /** @var $channel CM_Model_StreamChannel_Message */
        foreach (new CM_Paging_StreamChannel_AdapterType($this->getType()) as $channel) {
            $statusChannelKey = $channel->getKey() . ':' . $channel->getType();
            if (!isset($socketRedisStatus[$statusChannelKey])) {
                try {
                    $channel->delete();
                } catch (CM_Exception_Invalid $e) {
                    // For cases when streamSubscribe has been added to this streamChannel in midtime
                }
            } else {
                $channelsPersistenceArray[$statusChannelKey] = $channel;
            }
        }

        foreach ($socketRedisStatus as $statusChannelKey => $statusChannelData) {
            try {
                $extractedChannelData = CM_Model_StreamChannel_Message::extractStatusChannelData($statusChannelKey);
                $channelKey = $extractedChannelData['key'];
                $channelType = $extractedChannelData['type'];
                $statusChannelSubscribers = $statusChannelData['subscribers'];

                if (isset($channelsPersistenceArray[$statusChannelKey])) {
                    $streamChannel = $channelsPersistenceArray[$statusChannelKey];
                    if ($streamChannel->getType() != $channelType) {
                        throw new CM_Exception_Invalid(
                            'StreamChannel type `' . $streamChannel->getType() . '` doesn\'t match expected value `' . $channelType . '`');
                    }
                } else {
                    $oldSubscribers = array_filter($statusChannelSubscribers, function ($subscriber) use ($startStampLimit) {
                        return $subscriber['subscribeStamp'] / 1000 <= $startStampLimit;
                    });
                    if (!count($oldSubscribers)) {
                        continue;
                    }
                    $streamChannel = CM_Model_StreamChannel_Message::createType($channelType, array(
                        'key'         => $channelKey,
                        'adapterType' => $this->getType(),
                    ));
                }
                foreach ($statusChannelSubscribers as $subscriber) {
                    try {
                        $clientKey = (string) $subscriber['clientKey'];
                        if (!isset($subscribesPersistenceArray[$statusChannelKey . '/' . $clientKey])) {
                            $data = CM_Params::factory((array) $subscriber['data'], true);
                            $user = null;
                            if ($data->has('sessionId')) {
                                if ($session = CM_Session::findById($data->getString('sessionId'))) {
                                    $user = $session->getUser(false);
                                }
                            }
                            $start = (int) ($subscriber['subscribeStamp'] / 1000);
                            $allowedUntil = null;
                            if ($start <= $startStampLimit) {
                                CM_Model_Stream_Subscribe::createStatic(array('user'          => $user, 'start' => $start,
                                                                              'streamChannel' => $streamChannel, 'key' => $clientKey));
                            }
                        }
                    } catch (CM_Exception $e) {
                        $this->getServiceManager()->getLogger()->warning('Cannot add socket redis subscriber when synchronizing status', (new CM_Log_Context())->setException($e));
                    }
                }
            } catch (CM_Exception $e) {
                $this->getServiceManager()->getLogger()->warning('Error synchronizing socket redis status', (new CM_Log_Context())->setException($e));
            }
        }
    }

    /**
     * @param string $message
     * @throws CM_Exception_Invalid
     */
    public function onRedisMessage($message) {
        $message = CM_Params::jsonDecode($message);
        $type = $message['type'];
        $data = $message['data'];
        switch ($type) {
            case 'subscribe':
                $channel = $data['channel'];
                $clientKey = $data['clientKey'];
                $start = time();
                $data = CM_Params::factory((array) $data['data'], true);
                $user = null;
                if ($data->has('sessionId')) {
                    if ($session = CM_Session::findById($data->getString('sessionId'))) {
                        $user = $session->getUser(false);
                    }
                }
                $this->_subscribe($channel, $clientKey, $start, $user);
                break;
            case 'unsubscribe':
                $channel = $data['channel'];
                $clientKey = $data['clientKey'];
                $this->_unsubscribe($channel, $clientKey);

                break;
            case 'message':

                break;
            default:
                throw new CM_Exception_Invalid('Invalid socket-redis event type');
        }
    }

    /**
     * @param string             $channel
     * @param string             $clientKey
     * @param int                $start
     * @param CM_Model_User|null $user
     * @throws CM_Exception_Invalid
     */
    protected function _subscribe($channel, $clientKey, $start, CM_Model_User $user = null) {
        $channelData = CM_Model_StreamChannel_Message::extractStatusChannelData($channel);
        $channelKey = $channelData['key'];
        $channelType = $channelData['type'];
        $streamChannel = CM_Model_StreamChannel_Message::findByKeyAndAdapter($channelKey, $this->getType());
        if ($streamChannel && $streamChannel->getType() != $channelType) {
            throw new CM_Exception_Invalid(
                'StreamChannel type `' . $streamChannel->getType() . '` doesn\'t match expected value `' . $channelType . '`');
        }
        if (!$streamChannel) {
            /** @var $streamChannel CM_Model_StreamChannel_Message */
            $streamChannel = CM_Model_StreamChannel_Message::createType($channelType, array('key' => $channelKey, 'adapterType' => $this->getType()));
        }
        $streamChannelSubscribes = $streamChannel->getStreamSubscribes();
        if ($streamChannelSubscribes->findKey($clientKey)) {
            return;
        }
        CM_Model_Stream_Subscribe::createStatic(array('user'          => $user, 'start' => $start,
                                                      'streamChannel' => $streamChannel, 'key' => $clientKey));
    }

    /**
     * @param string $channel
     * @param string $clientKey
     * @throws CM_Exception_Invalid
     */
    protected function _unsubscribe($channel, $clientKey) {
        $channelData = CM_Model_StreamChannel_Message::extractStatusChannelData($channel);
        $channelKey = $channelData['key'];
        $channelType = $channelData['type'];
        $streamChannel = CM_Model_StreamChannel_Message::findByKeyAndAdapter($channelKey, $this->getType());
        if (!$streamChannel) {
            return;
        }
        if ($streamChannel->getType() != $channelType) {
            throw new CM_Exception_Invalid(
                'StreamChannel type `' . $streamChannel->getType() . '` doesn\'t match expected value `' . $channelType . '`');
        }
        $streamChannelSubscribe = $streamChannel->getStreamSubscribes()->findKey($clientKey);
        if ($streamChannelSubscribe) {
            $streamChannelSubscribe->delete();
        }
        if ($streamChannel->getStreamSubscribes()->getCount() === 0) {
            $streamChannel->delete();
        }
    }

    /**
     * @return array
     */
    protected function _fetchStatus() {
        $statusData = array();
        foreach ($this->_servers as $server) {
            $statusData = array_merge_recursive($statusData, CM_Params::jsonDecode(CM_Util::getContents(
                'http://' . $server['httpHost'] . ':' . $server['httpPort'], null, null, null, ['Authorization: token ' . $server['httpSecret']])));
        }
        return $statusData;
    }

    /**
     * @return CM_Redis_Client
     */
    private function _getRedis() {
        return CM_Service_Manager::getInstance()->getRedis();
    }
}
