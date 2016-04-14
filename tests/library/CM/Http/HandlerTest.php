<?php

class CM_Http_HandlerTest extends CMTest_TestCase {

    public function testProcessRequestThrows() {
        $request = new CM_Http_Request_Get('/library-css/nonexistent');
        $handler = new CM_Http_Handler($this->getServiceManager());

        try {
            $handler->processRequest($request);
            $this->fail('Expected to throw');
        } catch (CM_Exception $e) {
            $this->assertSame(CM_Exception::WARN, $e->getSeverity());
        }
        $this->assertSame($request, $this->getServiceManager()->getLogger()->getContext()->getHttpRequest());
    }
}
