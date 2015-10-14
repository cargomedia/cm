<?php

class CM_MediaStream_Adapter_Wowza extends CM_MediaStream_Adapter_Abstract {

    /** @var array */
    protected $_config;

    /**
     * @param array|null $servers
     * @param array|null $config
     */
    public function __construct(array $servers = null, array $config = null) {
        parent::__construct($servers);
        $this->_config = (array) $config;
    }

    public function synchronize() {
        $startStampLimit = time() - 3;
        $status = array();
        foreach ($this->_servers as $serverId => $wowzaServer) {
            $singleStatus = CM_Params::decode($this->_fetchStatus($wowzaServer['privateIp']), true);
            foreach ($singleStatus as $streamName => $publish) {
                $publish['serverId'] = $serverId;
                $publish['serverHost'] = $wowzaServer['privateIp'];
                $status[$streamName] = $publish;
            }
        }

        $streamChannels = $this->_getStreamChannels();
        foreach ($status as $streamName => $publish) {
            /** @var CM_Model_StreamChannel_Abstract $streamChannel */
            $streamChannel = CM_Model_StreamChannel_Abstract::findByKeyAndAdapter($streamName, $this->getType());
            if (!$streamChannel || !$streamChannel->getStreamPublishs()->findKey($publish['clientId'])) {
                $this->_stopClient($publish['clientId'], $publish['serverHost']);
            }

            foreach ($publish['subscribers'] as $clientId => $subscribe) {
                if (!$streamChannel || !$streamChannel->getStreamSubscribes()->findKey($clientId)) {
                    $this->_stopClient($clientId, $publish['serverHost']);
                }
            }
        }

        /** @var CM_Model_StreamChannel_Abstract $streamChannel */
        foreach ($streamChannels as $streamChannel) {
            if (!$streamChannel->hasStreams()) {
                $streamChannel->delete();
                continue;
            }

            /** @var CM_Model_Stream_Publish $streamPublish */
            $streamPublish = $streamChannel->getStreamPublishs()->getItem(0);
            if ($streamPublish) {
                if ($streamPublish->getStart() > $startStampLimit) {
                    continue;
                }
                if (!isset($status[$streamChannel->getKey()])) {
                    $this->unpublish($streamChannel->getKey());
                }
            }
            /** @var CM_Model_Stream_Subscribe $streamSubscribe */
            foreach ($streamChannel->getStreamSubscribes() as $streamSubscribe) {
                if ($streamSubscribe->getStart() > $startStampLimit) {
                    continue;
                }
                if (!isset($status[$streamChannel->getKey()]['subscribers'][$streamSubscribe->getKey()])) {
                    $this->unsubscribe($streamChannel->getKey(), $streamSubscribe->getKey());
                }
            }
        }
    }

    public function getServerId(CM_Http_Request_Abstract $request) {
        $ipAddress = long2ip($request->getIp());

        $servers = $this->_servers;
        foreach ($servers as $serverId => $server) {
            if ($server['publicIp'] == $ipAddress || $server['privateIp'] == $ipAddress) {
                return (int) $serverId;
            }
        }
        throw new CM_Exception_Invalid('No video server with ipAddress `' . $ipAddress . '` found');
    }

    /**
     * @param string $wowzaHost
     * @return string
     */
    protected function _fetchStatus($wowzaHost) {
        return CM_Util::getContents('http://' . $wowzaHost . ':' . $this->_config['httpPort'] . '/status');
    }

    /**
     * @param CM_Model_Stream_Abstract $stream
     */
    protected function _stopStream(CM_Model_Stream_Abstract $stream) {
        /** @var $streamChannel CM_Model_StreamChannel_Video */
        $streamChannel = $stream->getStreamChannel();
        $privateHost = $this->getPrivateHost($streamChannel->getServerId());
        $this->_stopClient($stream->getKey(), $privateHost);
    }

    protected function _stopClient($clientId, $serverHost) {
        CM_Util::getContents('http://' . $serverHost . ':' . $this->_config['httpPort'] . '/stop', array('clientId' => (string) $clientId), true);
    }
}
