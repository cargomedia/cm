<?php

class CM_InputStream_AbstractTest extends CMTest_TestCase {

    public function testRead() {
        $input = $this->mockClass('CM_InputStream_Abstract')->newInstance();
        $readMethod = $input->mockMethod('_read')
            ->at(0, function ($hint) {
                $this->assertSame('Hint ', $hint);
                return 'foo';
            })
            ->at(1, function ($hint) {
                $this->assertNull($hint);
                return '';
            });
        /** @var $input CM_InputStream_Abstract */
        $this->assertSame('foo', $input->read('Hint'));
        $this->assertSame('bar', $input->read(null, 'bar'));
        $this->assertSame(2, $readMethod->getCallCount());
    }

    public function testConfirm() {
        $input = $this->getMockBuilder('CM_InputStream_Abstract')->setMethods(array('read'))->getMockForAbstractClass();
        $input->expects($this->exactly(3))->method('read')->with('Hint (y/n)', 'default')->will($this->onConsecutiveCalls('invalid value', 'y', 'n'));

        /** @var $input CM_InputStream_Abstract */
        $this->assertTrue($input->confirm('Hint', 'default'));
        $this->assertFalse($input->confirm('Hint', 'default'));
    }
}
