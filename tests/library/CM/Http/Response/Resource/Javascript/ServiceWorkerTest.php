<?php

class CM_Http_Response_Resource_Javascript_ServiceWorkerTest extends CMTest_TestCase {

    public function testProcess() {
        $render = $this->getDefaultRender();
        $url = $render->getUrlServiceWorker();
        $this->assertSame(1, substr_count(parse_url($url, PHP_URL_PATH), '/'));

        $request = $this->createRequest($url);
        $response = $this->processRequest($request);

        $this->assertContains('Content-Type: application/x-javascript', $response->getHeaders());
        $this->assertContains('Cache-Control: max-age=31536000', $response->getHeaders());
        $this->assertContains('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000), $response->getHeaders());
        $this->assertContains('self.addEventListener("install"', $response->getContent());
    }

}
