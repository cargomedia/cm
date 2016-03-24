<?php

class CM_Log_Record_ExceptionTest extends CMTest_TestCase {

    public function testConstructor() {
        $exception = new CM_Exception('Bad news', CM_Exception::WARN);
        $recordException = new CM_Log_Record_Exception($exception, new CM_Log_Context());
        $this->assertInstanceOf('CM_Log_Record_Exception', $recordException);
        $this->assertSame('CM_Exception: Bad news', $recordException->getMessage());
        $this->assertSame(CM_Log_Logger::WARNING, $recordException->getLevel());

        $this->assertInstanceOf('CM_Exception', $recordException->getException());
        $this->assertInstanceOf('CM_ExceptionHandling_SerializableException', $recordException->getSerializableException());

        $exception2 = new CM_Exception('Bad empty severity news');
        $recordException = new CM_Log_Record_Exception($exception2, new CM_Log_Context());
        $this->assertInstanceOf('CM_Log_Record_Exception', $recordException);
        $this->assertSame(CM_Log_Logger::ERROR, $recordException->getLevel());

        $exception3 = new Exception('Native error');
        $recordException = new CM_Log_Record_Exception($exception3, new CM_Log_Context());
        $this->assertInstanceOf('CM_Log_Record_Exception', $recordException);
        $this->assertSame(CM_Log_Logger::ERROR, $recordException->getLevel());

        $exception = $this->catchException(function () use ($exception3) {
            new CM_Log_Record_Exception($exception3, new CM_Log_Context(), 11);
        });

        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Log level `11` does not exist.', $exception->getMessage());
    }
}
