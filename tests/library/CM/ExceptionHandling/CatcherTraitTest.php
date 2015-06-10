<?php

class CM_ExceptionHandling_CatcherTraitTest extends CMTest_TestCase {

    public function testCatchException() {
        $exception = $this->catchException(function () {
            throw new CM_Exception_Invalid('foo');
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('foo', $exception->getMessage());
    }

    public function testCatchNoException() {
        $noException = $this->catchException(function () {
            // not throwing exception
        });
        $this->assertNull($noException);
    }
}
