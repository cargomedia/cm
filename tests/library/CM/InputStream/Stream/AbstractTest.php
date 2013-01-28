<?php

require_once __DIR__ . '/../../../../TestCase.php';

class CM_InputStream_Stream_AbstractTest extends TestCase {

	public function testRead() {
		$streamPath = DIR_TMP . 'bar';
		$stream = fopen($streamPath, 'w');
		fwrite($stream, 'foo');
		/** @var $stream CM_InputStream_Stream_Abstract */
		$stream = $this->getMockForAbstractClass('CM_InputStream_Stream_Abstract', array($streamPath));
		$this->assertSame('foo', $stream->read('hint'));
	}

}
