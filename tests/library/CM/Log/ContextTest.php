<?php

class CM_Log_ContextTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testGetHttpRequest() {
        /** @var CM_Http_Request_Abstract $request */
        $request = $this->mockClass('CM_Http_Request_Abstract')->newInstanceWithoutConstructor();
        $context = new CM_Log_Context();
        $context->setHttpRequest($request);
        $this->assertSame($request, $context->getHttpRequest());
    }
}
