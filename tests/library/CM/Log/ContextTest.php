<?php

class CM_Log_ContextTest extends CMTest_TestCase {

    public function testMergeContext() {
        $user = new CM_Model_User();
        $computerInfo = new CM_Log_Context_ComputerInfo();

        $mainContext = new CM_Log_Context(null, null, $computerInfo, ['foo' => 10, 'bar' => 10]);
        $targetContext = new CM_Log_Context($user, null, null, ['foo' => 20, 'foobar' => 42]);
        $mergedContext = $mainContext->merge($targetContext);

        $this->assertSame($user, $mergedContext->getUser());
        $this->assertSame($computerInfo, $mergedContext->getComputerInfo());
        $extra = $mergedContext->getExtra();
        ksort($extra);
        $this->assertSame(['bar' => 10, 'foo' => 20, 'foobar' => 42], $extra);
    }
}
