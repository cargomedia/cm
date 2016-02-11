<?php

class CM_Log_Record_ExceptionTest extends CMTest_TestCase {

    public function testConstructor() {
        $exception = new CM_Exception('Bad news', CM_Exception::WARN);
        $recordException = new CM_Log_Record_Exception($exception, new CM_Log_Context());
        $this->assertInstanceOf('CM_Log_Record_Exception', $recordException);

        $this->assertSame('CM_Exception: Bad news', $recordException->getMessage());
        $this->assertSame(CM_Log_Logger::WARNING, $recordException->getLevel());
        $this->assertSame(CM_Paging_Log_Warn::getTypeStatic(), $recordException->getType());

        $exception2 = new CM_Exception('Bad empty severity news');
        $recordException = new CM_Log_Record_Exception($exception2, new CM_Log_Context());
        $this->assertInstanceOf('CM_Log_Record_Exception', $recordException);
        $this->assertSame(CM_Log_Logger::ERROR, $recordException->getLevel());
        $this->assertSame(CM_Paging_Log_Error::getTypeStatic(), $recordException->getType());

        $exception3 = new Exception('Standart');
        $recordException = new CM_Log_Record_Exception($exception3, new CM_Log_Context());
        $this->assertInstanceOf('CM_Log_Record_Exception', $recordException);
        $this->assertSame(CM_Log_Logger::ERROR, $recordException->getLevel());
        $this->assertSame(CM_Paging_Log_Fatal::getTypeStatic(), $recordException->getType());

        $recordException = new CM_Log_Record_Exception($exception3, new CM_Log_Context(), 11);
        $this->assertInstanceOf('CM_Log_Record_Exception', $recordException);
        $this->assertSame(CM_Log_Logger::ERROR, $recordException->getLevel());
        $this->assertSame(11, $recordException->getType());
    }
}
