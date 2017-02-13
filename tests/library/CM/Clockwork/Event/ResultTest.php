<?php

class CM_Clockwork_Event_ResultTest extends CMTest_TestCase {

    public function testIsSuccessful() {
        $result = new CM_Clockwork_Event_Result();
        $this->assertSame(false, $result->isSuccessful());

        $result->setSuccess();
        $this->assertSame(true, $result->isSuccessful());

        $result->setFailure();
        $this->assertSame(false, $result->isSuccessful());
    }
}
