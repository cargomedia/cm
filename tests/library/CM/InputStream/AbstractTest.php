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

    public function testReadWithValidateCallback() {
        $output = $this->mockClass('CM_OutputStream_Abstract')->newInstance();
        $writeMethod = $output->mockMethod('writeln')->set(function ($content) {
            $this->assertSame('message', $content);
        });

        $input = $this->mockClass('CM_InputStream_Abstract')->newInstance();
        $input->mockMethod('_getStreamOutput')->set($output);
        $readMethod = $input->mockMethod('_read')
            ->at(0, 'foo')
            ->at(1, 'bar');

        /** @var $input CM_InputStream_Abstract */
        $this->assertSame('bar', $input->read('hint', null, function ($value) {
            if ($value !== 'bar') {
                throw new CM_InputStream_InvalidValueException('message');
            }
        }));

        $this->assertSame(2, $readMethod->getCallCount());
        $this->assertSame(1, $writeMethod->getCallCount());
    }

    public function testConfirm() {
        $input = $this->getMockBuilder('CM_InputStream_Abstract')->setMethods(array('_read'))->getMockForAbstractClass();
        $input->expects($this->exactly(3))->method('_read')->with('Hint (Y/n) ')->will($this->onConsecutiveCalls('invalid value', 'y', 'n'));

        /** @var $input CM_InputStream_Abstract */
        $this->assertTrue($input->confirm('Hint', 'y'));
        $this->assertFalse($input->confirm('Hint', 'y'));
    }
}
