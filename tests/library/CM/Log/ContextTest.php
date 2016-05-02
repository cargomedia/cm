<?php

class CM_Log_ContextTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testGetHttpRequest() {
        /** @var CM_Http_Request_Abstract $request */
        $request = $this->mockClass('CM_Http_Request_Abstract')->newInstanceWithoutConstructor();
        $context = new CM_Log_Context($request);
        $this->assertSame($request, $context->getHttpRequest());

        /** @var CM_Http_Request_Abstract $request2 */
        $request2 = $this->mockClass('CM_Http_Request_Abstract')->newInstanceWithoutConstructor();
        $context->setHttpRequest($request2);
        $this->assertSame($request2, $context->getHttpRequest());
    }
}
