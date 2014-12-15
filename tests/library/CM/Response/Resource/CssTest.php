<?php

class CM_Http_Response_Resource_CssTest extends CMTest_TestCase {

    public function testProcess() {
        $render = new CM_Frontend_Render(new CM_Frontend_Environment());
        foreach (['library.css', 'vendor.css'] as $path) {
            $request = new CM_Http_Request_Get($render->getUrlResource('css', $path));
            $response = new CM_Http_Response_Resource_Css($request);
            $response->process();
            $this->assertContains('body{', $response->getContent());
        }
    }
}
