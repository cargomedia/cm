<?php

class CM_OutputStream_Stream_AbstractTest extends CMTest_TestCase {

  public function tearDown() {
    CMTest_TH::clearEnv();
  }

  public function testWrite() {
    $streamPath = CM_Bootloader::getInstance()->getDirTmp() . 'bar';
    $outputStream = $this->getMockBuilder('CM_OutputStream_Stream_Abstract')->setConstructorArgs(array($streamPath))->getMockForAbstractClass();

    /** @var CM_OutputStream_Stream_Abstract $outputStream */
    $outputStream->write('foo');
    $this->assertSame('foo', file_get_contents($streamPath));
  }
}
