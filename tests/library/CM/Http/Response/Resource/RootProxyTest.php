<?php

class CM_Http_Response_Resource_RootProxyTest extends CMTest_TestCase {

    public function testProcess() {
        $render = new CM_Frontend_Render(new CM_Frontend_Environment());
        $url = $render->getUrlResource('layout', 'img/logo.png', ['root' => true]);
        $this->assertSame(1, substr_count(parse_url($url, PHP_URL_PATH), '/'));

        $request = $this->createRequest($url);
        $response = $this->processRequest($request);

        $this->assertContains('Content-Type: image/png', $response->getHeaders());
    }
}
