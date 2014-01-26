<?php

class CM_ExceptionHandling_SerializableExceptionTest extends CMTest_TestCase {

	public function testExtractTraceRow() {
		$trace = array(
			array(
				'file'     => 'test.php',
				'line'     => 18,
				'function' => 'foo',
				'args'     =>
				array(),
			),
			array(
				'file'     => 'test.php',
				'line'     => 26,
				'function' => 'bar',
				'class'    => 'Foo',
				'type'     => '->',
				'args'     =>
				array(),
			),
			array(
				'file'     => 'test.php(28) : eval()\'d code',
				'line'     => 1,
				'function' => '{closure}',
				'args'     =>
				array(),
			),
			array(
				'file'     => 'test.php',
				'line'     => 28,
				'function' => 'eval',
			),
		);
		$expected = array(
			array(
				'code' => 'foo()',
				'file' => 'test.php',
				'line' => 18,
			),
			array(
				'code' => 'Foo->bar()',
				'file' => 'test.php',
				'line' => 26,
			),
			array(
				'code' => '{closure}()',
				'file' => 'test.php(28) : eval()\'d code',
				'line' => 1,
			),
			array(
				'code' => 'eval',
				'file' => 'test.php',
				'line' => 28,
			),
		);
		$exception = $this->getMockBuilder('Exception')->setMethods(array('getTrace'))->getMockForAbstractClass();
		$exception->expects($this->any())->method('getTrace')->will($this->returnValue($trace));

		$method = CMTest_TH::getProtectedMethod('CM_ExceptionHandling_SerializableException', '_extractTraceRow');
		foreach ($trace as $key => $traceRow) {
			$this->assertSame($expected[$key], $method->invoke(null, $traceRow));
		}
	}

	public function testGetters() {
		$exception = new CM_Exception('Foo bar');
		$serializableException = new CM_ExceptionHandling_SerializableException($exception);
		$this->assertSame('Foo bar', $serializableException->getMessage());
		$this->assertSame('CM_Exception', $serializableException->getClass());
		$this->assertSame(__FILE__, $serializableException->getFile());
		$this->assertInternalType('int', $serializableException->getLine());
	}
}