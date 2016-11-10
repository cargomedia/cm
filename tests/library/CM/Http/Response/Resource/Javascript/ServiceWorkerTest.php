<?php

class CM_Http_Response_Resource_Javascript_ServiceWorkerTest extends CMTest_TestCase {

    protected function setUp() {
        $mockBundler = $this->mockClass('CM_Frontend_Bundler_Client');
        $mockBundler->mockMethod('_sendRequest')->set(function ($data) {
            return CM_Util::jsonEncode($data);
        });
        $mockBundler->mockMethod('_parseResponse')->set(function ($rawResponse) {
            return $rawResponse;
        });
        $bundler = $mockBundler->newInstanceWithoutConstructor();
        $this->getServiceManager()->replaceInstance('cm-bundler', $bundler);
    }

    protected function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testProcess() {
        $render = new CM_Frontend_Render(new CM_Frontend_Environment());
        $url = $render->getUrlServiceWorker();
        $this->assertSame(1, substr_count(parse_url($url, PHP_URL_PATH), '/'));

        $request = $this->createRequest($url);
        $response = $this->processRequest($request);

        $this->assertContains('Content-Type: application/x-javascript', $response->getHeaders());
        $this->assertContains('Cache-Control: max-age=31536000', $response->getHeaders());
        $this->assertContains('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000), $response->getHeaders());
        $this->assertContains('"client-vendor\/serviceworker\/cm.js"', $response->getContent());
        $this->assertContains('"worker\/config"', $response->getContent());
    }

}
