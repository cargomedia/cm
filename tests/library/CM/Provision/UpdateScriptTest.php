<?php

class CM_Provision_UpdateScriptTest extends CMTest_TestCase {

    public function testRun() {
        $functionMock = new \Mocka\FunctionMock();
        $updateScript = new CM_Provision_UpdateScript($functionMock);
        $updateScript->run();
        $this->assertSame(1, $functionMock->getCallCount());
    }

    public function testIsBlocking() {
        $updateScript = new CM_Provision_UpdateScript(function() {});
        $this->assertSame(false, $updateScript->isBlocking());
        $updateScript = new CM_Provision_UpdateScript(function() {}, false);
        $this->assertSame(false, $updateScript->isBlocking());
        $updateScript = new CM_Provision_UpdateScript(function() {}, true);
        $this->assertSame(true, $updateScript->isBlocking());
    }
}
