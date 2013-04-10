<?php

class CM_Stream_AbstractTest extends CMTest_TestCase {

	public function testGetAdapter() {
		$adapterMockClass = $this->getMockClass('CM_Stream_Adapter_Abstract');
		$streamMock = $this->getMockBuilder('CM_Stream_Abstract')->setMethods(array('getAdapterClass'))->getMock();
		$streamMock->expects($this->once())->method('getAdapterClass')->will($this->returnValue($adapterMockClass));

		/** @var CM_Stream_Abstract $streamMock */
		$this->assertInstanceOf($adapterMockClass, $streamMock->getAdapter());
		$this->assertInstanceOf($adapterMockClass, $streamMock->getAdapter());
	}
}
