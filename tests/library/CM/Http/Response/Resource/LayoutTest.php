<?php

class CM_Http_Response_Resource_LayoutTest extends CMTest_TestCase {

    public function testProcess() {
        $site = $this->getMockSite();
        $render = new CM_Frontend_Render(new CM_Frontend_Environment($site));
        $url = $render->getUrlResource('layout', 'img/logo.png');
        $request = new CM_Http_Request_Get($url, ['host' => $site->getHost()]);
        $response = $this->processRequest($request);

        $this->assertInstanceOf('CM_Http_Response_Resource_Layout', $response);
        $this->assertContains('Content-Type: image/png', $response->getHeaders());
        $this->assertContains('Cache-Control: max-age=31536000', $response->getHeaders());
        $this->assertContains('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000), $response->getHeaders());
    }

    public function testFiletypeForbidden() {
        $site = $this->getMockSite();
        $render = new CM_Frontend_Render(new CM_Frontend_Environment($site));
        $url = $render->getUrlResource('layout', 'browserconfig.xml.smarty');
        $request = new CM_Http_Request_Get($url, ['host' => $site->getHost()]);

        try {
            $this->processRequest($request);
        } catch (CM_Exception_Nonexistent $ex) {
            $this->assertSame('Forbidden filetype', $ex->getMessage());
            $this->assertSame(['path' => '/browserconfig.xml.smarty'], $ex->getMetaInfo());
        }
    }

    public function testSmartyTemplate() {
        $site = $this->getMockSite();
        $render = new CM_Frontend_Render(new CM_Frontend_Environment($site));
        $url = $render->getUrlResource('layout', 'browserconfig.xml');
        $request = new CM_Http_Request_Get($url, ['host' => $site->getHost()]);
        $response = $this->processRequest($request);

        $this->assertContains('Content-Type: application/xml', $response->getHeaders());
        $this->assertContains('Cache-Control: max-age=31536000', $response->getHeaders());
        $this->assertContains('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000), $response->getHeaders());
        $imgUrl = $render->getUrlResource('layout', 'img/meta/tile-medium-270x270-transparent.png');
        $this->assertContains("src=\"{$imgUrl}\"", $response->getContent());

        $exception = $this->catchException(function () use ($render) {
            $render->getLayoutFile('resource/browserconfig.xml');
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        /** @var CM_Exception_Invalid $exception */
        $this->assertSame('Can\'t find template', $exception->getMessage());
        $this->assertSame('resource/browserconfig.xml', $exception->getMetaInfo()['template']);
    }

    public function testNonexistentFile() {
        $site = $this->getMockSite();
        $render = new CM_Frontend_Render(new CM_Frontend_Environment($site));
        $url = $render->getUrlResource('layout', 'nonExistent.css');
        $request = new CM_Http_Request_Get($url, ['host' => $site->getHost()]);

        try {
            $this->processRequest($request);
        } catch (CM_Exception_Nonexistent $ex) {
            $this->assertSame('Invalid filename', $ex->getMessage());
            $this->assertSame(['path' => '/nonExistent.css'], $ex->getMetaInfo());
        }
    }

}
