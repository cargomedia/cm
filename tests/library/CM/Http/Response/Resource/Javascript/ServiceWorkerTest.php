<?php

class CM_Http_Response_Resource_Javascript_ServiceWorkerTest extends CMTest_TestCase {

    public function testProcess() {
        $render = new CM_Frontend_Render(new CM_Frontend_Environment());
        $url = $render->getUrlServiceWorker();
        $this->assertSame(1, substr_count(parse_url($url, PHP_URL_PATH), '/'));

        $request = $this->createRequest($url);
        $response = $this->processRequest($request);

        $this->assertContains('Content-Type: application/x-javascript', $response->getHeaders());
        $this->assertContains('self.addEventListener("install"', $response->getContent());
    }

}
