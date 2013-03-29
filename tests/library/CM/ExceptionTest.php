<?php

class CM_ExceptionTest extends CMTest_TestCase {

	public function testGetSetSeverity() {
		$exception = new CM_Exception();
		$this->assertSame(CM_Exception::ERROR, $exception->getSeverity());

		$exception->setSeverity(CM_Exception::WARN);
		$this->assertSame(CM_Exception::WARN, $exception->getSeverity());
	}

	public function testGetSetSeverityInvalid() {
		$exception = new CM_Exception();

		try {
			$exception->setSeverity(9999);
			$this->fail('Could set invalid severity');
		} catch (CM_Exception_Invalid $e) {
			$this->assertSame('Invalid severity `9999`', $e->getMessage());
		}

		try {
			$exception->setSeverity('1');
			$this->fail('Could set invalid severity');
		} catch (CM_Exception_Invalid $e) {
			$this->assertSame('Invalid severity `1`', $e->getMessage());
		}
	}
}
