<?php

require_once __DIR__ . '/../../../TestCase.php';

class CM_InputStream_StreamTest extends TestCase {

	public function testRead() {
		$streamPath = '/tmp/foo';
		$stream = fopen($streamPath, 'w');
		fwrite($stream, 'foo');
		$stream = new CM_InputStream_Stream($streamPath);
		$this->assertSame('foo', $stream->read());
	}

}