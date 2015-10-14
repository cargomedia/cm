<?php

class CM_MediaStream_Adapter_WowzaTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testSynchronizeMissingInWowza() {
        $streamChannel = CMTest_TH::createStreamChannel();
        $streamPublish = CMTest_TH::createStreamPublish(null, $streamChannel);
        $streamSubscribe = CMTest_TH::createStreamSubscribe(null, $streamChannel);

        $servers = array(1 => array('publicHost' => 'video.example.com', 'publicIp' => '10.0.3.109', 'privateIp' => '10.0.3.108'));
        $wowza = $this->getMock('CM_MediaStream_Adapter_Wowza', array('_fetchStatus'), [$servers]);
        $json = $this->_generateWowzaData(array());
        $wowza->expects($this->any())->method('_fetchStatus')->will($this->returnValue($json));
        /** @var $wowza CM_MediaStream_Service */

        $wowza->synchronize();
        $this->assertEquals($streamChannel, CM_Model_StreamChannel_Abstract::findByKeyAndAdapter($streamChannel->getKey(), $wowza->getType()));
        $this->assertEquals($streamPublish, CM_Model_Stream_Publish::findByKeyAndChannel($streamPublish->getKey(), $streamChannel));
        $this->assertEquals($streamSubscribe, CM_Model_Stream_Subscribe::findByKeyAndChannel($streamSubscribe->getKey(), $streamChannel));

        CMTest_TH::timeForward(5);
        $wowza->synchronize();
        $this->assertNull(CM_Model_StreamChannel_Abstract::findByKeyAndAdapter($streamChannel->getKey(), $wowza->getType()));
        $this->assertNull(CM_Model_Stream_Publish::findByKeyAndChannel($streamPublish->getKey(), $streamChannel));
        $this->assertNull(CM_Model_Stream_Subscribe::findByKeyAndChannel($streamSubscribe->getKey(), $streamChannel));
    }

    public function testSynchronizeMissingInPhp() {

        /** @var CM_Model_StreamChannel_Video $streamChannel */
        $streamChannel = CMTest_TH::createStreamChannel();
        $streamPublish = CMTest_TH::createStreamPublish(null, $streamChannel);
        $streamSubscribe = CMTest_TH::createStreamSubscribe(null, $streamChannel);

        $servers = array(1 => array('publicHost' => 'video.example.com', 'publicIp' => '10.0.3.109', 'privateIp' => '10.0.3.108'));
        $adapter = $this->getMock('CM_MediaStream_Adapter_Wowza', array('_stopClient', '_fetchStatus'), [$servers]);
        $json = $this->_generateWowzaData(array($streamChannel));
        $adapter->expects($this->any())->method('_fetchStatus')->will($this->returnValue($json));
        $adapter->expects($this->at(1))->method('_stopClient')->with($streamPublish->getKey(), '10.0.3.108');
        $adapter->expects($this->at(2))->method('_stopClient')->with($streamSubscribe->getKey(), '10.0.3.108');
        $adapter->expects($this->exactly(2))->method('_stopClient');

        /** @var $adapter CM_MediaStream_Adapter_Wowza */
        $adapter->unpublish($streamChannel->getKey());
        $adapter->unsubscribe($streamChannel->getKey(), $streamSubscribe->getKey());
        $adapter->synchronize();
    }

    public function testGetServerId() {
        $servers = array(1 => array('publicHost' => 'video.example.com', 'publicIp' => '10.0.3.109', 'privateIp' => '10.0.3.108'));
        $adapter = new CM_MediaStream_Adapter_Wowza($servers);
        $ipAddresses = array('10.0.3.109', '10.0.3.108');
        foreach ($ipAddresses as $ipAddress) {
            $request = $this->getMockForAbstractClass('CM_Http_Request_Abstract', array($ipAddress), 'CM_Http_Request_Mock', true, true, true, array('getIp',
                'getHost'));
            $request->expects($this->any())->method('getIp')->will($this->returnValue(sprintf('%u', ip2long($ipAddress))));
            $this->assertEquals(1, $adapter->getServerId($request));
        }
        try {
            $ipAddress = '66.66.66.66';
            $request = $this->getMockForAbstractClass('CM_Http_Request_Abstract', array($ipAddress), 'CM_Http_Request_Mock', true, true, true, array('getIp',
                'getHost'));
            $request->expects($this->any())->method('getIp')->will($this->returnValue(sprintf('%u', ip2long($ipAddress))));
            $adapter->getServerId($request);
            $this->fail('Found server with incorrect ipAddress');
        } catch (CM_Exception_Invalid $e) {
            $this->assertContains('No video server', $e->getMessage());
            $this->assertContains('`66.66.66.66`', $e->getMessage());
        }
    }

    private function _generateWowzaData(array $streamChannels) {
        $jsonData = array();
        /** @var CM_Model_StreamChannel_Video $streamChannel */
        foreach ($streamChannels as $streamChannel) {
            $subscribes = array();
            /** @var CM_Model_Stream_Publish $streamPublish */
            $streamPublish = $streamChannel->getStreamPublish();
            /** @var CM_Model_Stream_Subscribe $streamSubscribe */
            foreach ($streamChannel->getStreamSubscribes() as $streamSubscribe) {
                $session = CMTest_TH::createSession($streamSubscribe->getUser());
                $subscribes[$streamSubscribe->getKey()] = array(
                    'startTimeStamp' => $streamSubscribe->getStart(),
                    'clientId'       => $streamSubscribe->getKey(),
                    'data'           => json_encode(array('sessionId' => $session->getId())),
                );
            }
            $session = CMTest_TH::createSession($streamPublish->getUser());
            $jsonData[$streamChannel->getKey()] = array(
                'startTimeStamp' => $streamPublish->getStart(),
                'clientId'       => $streamPublish->getKey(),
                'data'           => json_encode(array('sessionId' => $session->getId(), 'streamChannelType' => $streamChannel->getType())),
                'subscribers'    => $subscribes,
                'thumbnailCount' => 2,
                'width'          => 480,
                'height'         => 720,
                'wowzaIp'        => ip2long('192.168.0.1'));
        }
        return json_encode($jsonData);
    }
}
