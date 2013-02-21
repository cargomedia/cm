<?php

class CM_InputStream_AbstractTest extends CMTest_TestCase {

	public function testRead() {
		$input = $this->getMockBuilder('CM_InputStream_Abstract')->setMethods(array('_read'))->getMockForAbstractClass();
		$input->expects($this->exactly(3))->method('_read')->will($this->onConsecutiveCalls('foo', 'foo', 'bar'));
		$input->expects($this->at(0))->method('_read')->with('Hint ');
		$input->expects($this->at(1))->method('_read')->with(null);
		$input->expects($this->at(2))->method('_read')->with(null);

		/** @var $input CM_InputStream_Abstract */
		$this->assertSame('foo', $input->read('Hint'));
		$this->assertSame('foo', $input->read());
		$this->assertSame('bar', $input->read(null, 'bar'));
	}

	public function testConfirm() {
		$input = $this->getMockBuilder('CM_InputStream_Abstract')->setMethods(array('read'))->getMockForAbstractClass();
		$input->expects($this->exactly(3))->method('read')->with($this->equalTo('Hint (y/n)'), $this->equalTo('default'))->will($this->onConsecutiveCalls('', 'y', 'n'));

		/** @var $input CM_InputStream_Abstract */
		$this->assertTrue($input->confirm('Hint', 'default'));
		$this->assertFalse($input->confirm('Hint', 'default'));
	}
}
