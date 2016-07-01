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

    public function testSetExtra() {
        $extra = ['foo' => 'bar', 'baz' => ['quux' => 1, 'foo' => 0]];
        $context = new CM_Log_Context();
        $context->setExtra($extra);
        $this->assertSame($extra, $context->getExtra());

        $exception = $this->catchException(function () use ($context) {
            $context->setExtra(['foo' => 'bar', 'fooBar' => ['1' => ['2' => new stdClass()]]]);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Object can not be passed to "Extra"', $exception->getMessage());
    }
}
