<?php

class CM_Stream_Adapter_Video_WowzaTest extends CMTest_TestCase {

    public function setUp() {
        CM_Config::get()->CM_Stream_Video->servers = array(1 => array('publicHost' => 'video.example.com', 'publicIp' => '10.0.3.109',
                                                                      'privateIp'  => '10.0.3.108'));
    }

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testSynchronizeMissingInWowza() {
        $streamChannel = CMTest_TH::createStreamChannel();
        $streamPublish = CMTest_TH::createStreamPublish(null, $streamChannel);
        $streamSubscribe = CMTest_TH::createStreamSubscribe(null, $streamChannel);

        $wowza = $this->getMock('CM_Stream_Adapter_Video_Wowza', array('_fetchStatus'));
        $json = $this->_generateWowzaData(array());
        $wowza->expects($this->any())->method('_fetchStatus')->will($this->returnValue($json));
        /** @var $wowza CM_Stream_Video */

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

        $wowza = $this->getMock('CM_Stream_Adapter_Video_Wowza', array('_stopClient', '_fetchStatus'));
        $json = $this->_generateWowzaData(array($streamChannel));
        $wowza->expects($this->any())->method('_fetchStatus')->will($this->returnValue($json));
        $wowza->expects($this->at(1))->method('_stopClient')->with($streamPublish->getKey(), $streamChannel->getPrivateHost());
        $wowza->expects($this->at(2))->method('_stopClient')->with($streamSubscribe->getKey(), $streamChannel->getPrivateHost());
        $wowza->expects($this->exactly(2))->method('_stopClient');

        /** @var $wowza CM_Stream_Adapter_Video_Wowza */
        $wowza->unpublish($streamChannel->getKey());
        $wowza->unsubscribe($streamChannel->getKey(), $streamSubscribe->getKey());
        $wowza->synchronize();
    }

    public function testGetServerId() {
        $adapter = new CM_Stream_Adapter_Video_Wowza();
        $ipAddresses = array('10.0.3.109', '10.0.3.108');
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';
        foreach ($ipAddresses as $ipAddress) {
            $_SERVER['REMOTE_ADDR'] = $ipAddress;
            $this->assertEquals(1, $adapter->getServerId());
        }
        try {
            $_SERVER['REMOTE_ADDR'] = '66.66.66.66';
            $adapter->getServerId();
            $this->fail('Found server with incorrect ipAddress');
        } catch (CM_Exception_Invalid $e) {
            $this->assertContains('No video server', $e->getMessage());
            $this->assertContains('`66.66.66.66`', $e->getMessage());
        }
        unset($_SERVER['REQUEST_METHOD']);
        unset($_SERVER['REQUEST_URI']);
        unset($_SERVER['REMOTE_ADDR']);
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
