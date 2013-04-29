<?php

class CM_InputStream_NullTest extends CMTest_TestCase {

	public function testRead() {
		$input = new CM_InputStream_Null();
		try {
			$input->read();
			$this->fail('Could read from null input stream');
		} catch (CM_Exception_Invalid $e) {
			$this->assertSame('Cannot read input stream', $e->getMessage());
		}
	}
}
