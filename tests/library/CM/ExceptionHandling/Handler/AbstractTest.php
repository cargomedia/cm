<?php

class CM_ExceptionHandling_Handler_AbstractTest extends CMTest_TestCase {

	public function testLogException() {
		$log = $this->getMockBuilder('CM_Paging_Log_Error')->setMethods(array('add'))->disableOriginalConstructor()->getMock();
		$log->expects($this->once())->method('add')->will($this->returnValue(null));

		$exception = $this->getMockBuilder('CM_Exception')->setMethods(array('getLog'))->disableOriginalConstructor()->getMock();
		$exception->expects($this->any())->method('getLog')->will($this->returnValue($log));

		$method = CMTest_TH::getProtectedMethod('CM_ExceptionHandling_Handler_Abstract', '_logException');
		$exceptionHandler = $this->getMockBuilder('CM_ExceptionHandling_Handler_Abstract')->getMockForAbstractClass();
		$method->invoke($exceptionHandler, $exception);
	}

	public function testLogExceptionFileLog() {
		$errorLog = DIR_DATA_LOG . 'error.log';

		$log = $this->getMockBuilder('CM_Paging_Log_Error')->setMethods(array('add'))->disableOriginalConstructor()->getMock();
		$log->expects($this->any())->method('add')->will($this->throwException(new Exception('foo')));

		$exception = $this->getMockBuilder('CM_Exception')->setMethods(array('getLog'))->disableOriginalConstructor()->getMock();
		$exception->expects($this->any())->method('getLog')->will($this->returnValue($log));

		$this->assertFileNotExists($errorLog);

		$method = CMTest_TH::getProtectedMethod('CM_ExceptionHandling_Handler_Abstract', '_logException');
		$exceptionHandler = $this->getMockBuilder('CM_ExceptionHandling_Handler_Abstract')->getMockForAbstractClass();
		$method->invoke($exceptionHandler, $exception);

		$logContents = file_get_contents($errorLog);
		$this->assertNotEmpty($logContents);
		$this->assertContains('### Cannot log error: ', $logContents);
		$this->assertContains('### Original Exception: ', $logContents);
	}

	public function testPrintException() {
		$errorException = new CM_Exception();
		$nativeException = new Exception();
		$fatalException = new CM_Exception(null, null, null, CM_Exception::FATAL);

		$exceptionHandler = $this->getMockBuilder('CM_ExceptionHandling_Handler_Abstract')
				->setMethods(array('_logException', '_printException'))->getMockForAbstractClass();
		$exceptionHandler->expects($this->at(1))->method('_printException')->with($errorException);
		$exceptionHandler->expects($this->at(3))->method('_printException')->with($nativeException);
		$exceptionHandler->expects($this->at(5))->method('_printException')->with($fatalException);
		/** @var CM_ExceptionHandling_Handler_Abstract $exceptionHandler */

		$exceptionHandler->handleException($errorException);
		$exceptionHandler->handleException($nativeException);
		$exceptionHandler->handleException($fatalException);
	}

	public function testPrintExceptionPrintSeverity() {
		$errorException = new CM_Exception();
		$nativeException = new Exception();
		$fatalException = new CM_Exception(null, null, null, CM_Exception::FATAL);

		$exceptionHandler = $this->getMockBuilder('CM_ExceptionHandling_Handler_Abstract')
				->setMethods(array('_logException', '_printException'))->getMockForAbstractClass();
		$exceptionHandler->expects($this->any())->method('_logException');
		$exceptionHandler->expects($this->at(2))->method('_printException')->with($nativeException);
		$exceptionHandler->expects($this->at(4))->method('_printException')->with($fatalException);
		/** @var CM_ExceptionHandling_Handler_Abstract $exceptionHandler */

		$exceptionHandler->setPrintSeverityMin(CM_Exception::FATAL);
		$exceptionHandler->handleException($errorException);
		$exceptionHandler->handleException($nativeException);
		$exceptionHandler->handleException($fatalException);
	}

	/**
	 * @expectedException ErrorException
	 * @expectedExceptionMessage E_USER_ERROR: Raw error message
	 */
	public function testHandleErrorRaw() {
		trigger_error('Raw error message', E_USER_ERROR);
	}
}
