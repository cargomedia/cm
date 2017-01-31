<?php

class CM_OutputStream_AbstractTest extends CMTest_TestCase {

    public function testWriteln() {
        $outputStream = $this->getMockBuilder('CM_OutputStream_Abstract')->setMethods(array('write'))->getMockForAbstractClass();
        $outputStream->expects($this->once())->method('write')->with('foo' . PHP_EOL);

        /** @var $outputStream CM_OutputStream_Abstract */
        $outputStream->writeln('foo');
    }

    public function testWritef() {
        $outputStream = $this->getMockBuilder('CM_OutputStream_Abstract')->setMethods(array('write'))->getMockForAbstractClass();
        $outputStream->expects($this->once())->method('write')->with('foo | 1 | 3.45');

        /** @var $outputStream CM_OutputStream_Abstract */
        $outputStream->writef('%s | %d | %.2f', 'foo', 1.2, 3.4512);
    }

    public function testWritefln() {
        $outputStream = $this->getMockBuilder('CM_OutputStream_Abstract')->setMethods(array('write'))->getMockForAbstractClass();
        $outputStream->expects($this->once())->method('write')->with('bar | 1 | 3.45' . PHP_EOL);

        /** @var $outputStream CM_OutputStream_Abstract */
        $outputStream->writefln('%s | %d | %.2f', 'bar', 1.2, 3.4512);
    }
}
