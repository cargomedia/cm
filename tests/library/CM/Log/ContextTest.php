<?php

class CM_Log_ContextTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testGetHttpRequest() {
        /** @var CM_Http_Request_Abstract $request */
        $request = $this->mockClass('CM_Http_Request_Abstract')->newInstanceWithoutConstructor();
        $context = new CM_Log_Context(null, $request);
        $this->assertSame($request, $context->getHttpRequest());

        /** @var CM_Http_Request_Abstract $request2 */
        $request2 = $this->mockClass('CM_Http_Request_Abstract')->newInstanceWithoutConstructor();
        $context->setHttpRequest($request2);
        $this->assertSame($request2, $context->getHttpRequest());
    }

    public function testMergeContext() {
        $user = CMTest_TH::createUser();
        $computerInfo = new CM_Log_Context_ComputerInfo('foo.dev', '42.0');

        $mainContext = new CM_Log_Context(null, null, $computerInfo, ['foo' => 10, 'bar' => 10]);
        $targetContext = new CM_Log_Context($user, null, null, ['foo' => 20, 'foobar' => 42]);
        $mergedContext = $mainContext->merge($targetContext);

        $this->assertEquals($user, $mergedContext->getUser());
        $this->assertEquals($computerInfo, $mergedContext->getComputerInfo());
        $extra = $mergedContext->getExtra();
        ksort($extra);
        $this->assertSame(['bar' => 10, 'foo' => 20, 'foobar' => 42], $extra);
    }
}
