<?php

class CM_VideoStream_Adapter_AbstractTest extends CMTest_TestCase {

    public function testCheckStreamsInvalid() {
        $streamPublish = $this->getMockBuilder('CM_Model_Stream_Publish')
            ->disableOriginalConstructor()->getMock();

        $streamSubscribe = $this->getMockBuilder('CM_Model_Stream_Subscribe')
            ->disableOriginalConstructor()->getMock();

        $streamChannel = $this->getMockBuilder('CM_Model_StreamChannel_Video')
            ->setMethods(array('isValid', 'hasStreamPublish', 'getStreamPublish', 'getStreamSubscribes'))->getMockForAbstractClass();
        $streamChannel->expects($this->any())->method('hasStreamPublish')->will($this->returnValue(true));
        $streamChannel->expects($this->any())->method('getStreamPublish')->will($this->returnValue($streamPublish));
        $streamChannel->expects($this->any())->method('getStreamSubscribes')->will($this->returnValue(array($streamSubscribe)));
        $streamChannel->expects($this->any())->method('isValid')->will($this->returnValue(false));
        /** @var CM_Model_StreamChannel_Video $streamChannel */

        $adapter = $this->getMockBuilder('CM_VideoStream_Adapter_Abstract')
            ->setMethods(array('_getStreamChannels', 'stopStream'))->getMockForAbstractClass();
        $adapter->expects($this->any())->method('_getStreamChannels')->will($this->returnValue(array($streamChannel)));
        $adapter->expects($this->at(1))->method('stopStream')->with($streamPublish);
        $adapter->expects($this->at(2))->method('stopStream')->with($streamSubscribe);
        /** @var CM_VideoStream_Adapter_Abstract $adapter */

        $adapter->checkStreams();
    }

    public function testServerGetters() {
        $servers = array(1 => ['publicHost' => 'video.example.com', 'publicIp' => '10.0.3.109', 'privateIp' => '10.0.3.108']);
        /** @var CM_VideoStream_Adapter_Abstract $adapter */
        $adapter = $this->mockObject('CM_VideoStream_Adapter_Abstract', [$servers]);

        $this->assertSame($servers[1], $adapter->getServer(1));
        $this->assertSame('video.example.com', $adapter->getPublicHost(1));
        $this->assertSame('10.0.3.108', $adapter->getPrivateHost(1));
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage No video server with id `5` found
     */
    public function testGetServerInvalid() {
        /** @var CM_VideoStream_Adapter_Abstract $adapter */
        $adapter = $this->mockObject('CM_VideoStream_Adapter_Abstract', []);
        $adapter->getServer(5);
    }
}
