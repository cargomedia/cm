<?php

class CM_Process_WorkloadResultTest extends CMTest_TestCase {

    public function testConstructor() {
        $result = new CM_Process_WorkloadResult();
        $this->assertSame(null, $result->getReturnCode());

        $result = new CM_Process_WorkloadResult(0);
        $this->assertSame(0, $result->getReturnCode());
    }

    public function testSetReturnCode() {
        $result = new CM_Process_WorkloadResult();
        $this->assertSame(null, $result->getReturnCode());
        $result->setReturnCode(0);
        $this->assertSame(0, $result->getReturnCode());

        $result->setReturnCode(null);
        $this->assertSame(null, $result->getReturnCode());
    }

    public function testIsSuccess() {
        $result = new CM_Process_WorkloadResult();
        $this->assertSame(false, $result->isSuccess());

        $result->setReturnCode(0);
        $this->assertSame(true, $result->isSuccess());

        $result->setReturnCode(1);
        $this->assertSame(false, $result->isSuccess());
    }
}
