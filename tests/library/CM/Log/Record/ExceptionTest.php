<?php

class CM_Log_Record_ExceptionTest extends CMTest_TestCase {

    public function testConstructor() {
        $exception = new CM_Exception('Bad news', CM_Exception::WARN);
        $recordException = new CM_Log_Record_Exception(CM_Log_Logger::WARNING, new CM_Log_Context(), $exception);
        $this->assertInstanceOf('CM_Log_Record_Exception', $recordException);
        $this->assertSame('CM_Exception: Bad news', $recordException->getMessage());
        $this->assertSame(CM_Log_Logger::WARNING, $recordException->getLevel());

        $this->assertInstanceOf('CM_Exception', $recordException->getException());
        $this->assertInstanceOf('CM_ExceptionHandling_SerializableException', $recordException->getSerializableException());

        $exception = $this->catchException(function () {
            new CM_Log_Record_Exception(11, new CM_Log_Context(), new Exception('error'));
        });

        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Log level `11` does not exist.', $exception->getMessage());
    }
}
