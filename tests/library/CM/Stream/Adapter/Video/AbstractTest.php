<?php

class CM_Stream_Adapter_Video_AbstractTest extends CMTest_TestCase {

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

		$adapter = $this->getMockBuilder('CM_Stream_Adapter_Video_Abstract')
			->setMethods(array('_getStreamChannels', 'stopStream'))->getMockForAbstractClass();
		$adapter->expects($this->any())->method('_getStreamChannels')->will($this->returnValue(array($streamChannel)));
		$adapter->expects($this->at(1))->method('stopStream')->with($streamPublish);
		$adapter->expects($this->at(2))->method('stopStream')->with($streamSubscribe);
		/** @var CM_Stream_Adapter_Video_Abstract $adapter */

		$adapter->checkStreams();
	}
}
