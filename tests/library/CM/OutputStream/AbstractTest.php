<?php

class CM_OutputStream_AbstractTest extends CMTest_TestCase {

	public function testWriteln() {
		$outputStream = $this->getMockBuilder('CM_OutputStream_Abstract')->setMethods(array('write'))->getMockForAbstractClass();
		$outputStream->expects($this->once())->method('write')->with('foo' . PHP_EOL);

		/** @var $outputStream CM_OutputStream_Abstract */
		$outputStream->writeln('foo');
	}
}
