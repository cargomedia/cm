<?php

class CM_Http_Response_Resource_LayoutTest extends CMTest_TestCase {

    public function testProcess() {
        $filePath = 'img/logo.png';
        $response = $this->getResponseResourceLayout($filePath);
        $this->assertContains('Content-Type: image/png', $response->getHeaders());
    }

    public function testFiletypeForbidden() {
        $filePath = 'browserconfig.xml.smarty';
        try {
            $this->getResponseResourceLayout($filePath);
        } catch (CM_Exception_Nonexistent $ex) {
            $this->assertSame('Forbidden filetype', $ex->getMessage());
            $this->assertSame(['path' => '/browserconfig.xml.smarty'], $ex->getMetaInfo(true));
        }
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage Cannot find `resource/browserconfig.xml`
     */
    public function testRendering() {
        $filePath = 'browserconfig.xml';
        /** @var CM_Http_Response_Resource_Layout $response */
        $response = $this->getResponseResourceLayout($filePath);
        $this->assertContains('Content-Type: application/xml', $response->getHeaders());
        $this->assertTrue((boolean) preg_match('!src="http://cdn\.default\.dev/layout/.+?/img/mstile-70x70\.png"!', $response->getContent()));
        $response->getRender()->getLayoutFile('resource/' . $filePath);
    }

    public function testNonexistentFile() {
        $filePath = 'nonExistent.css';
        try {
            $this->getResponseResourceLayout($filePath);
        } catch (CM_Exception_Nonexistent $ex) {
            $this->assertSame('Invalid filename', $ex->getMessage());
            $this->assertSame(['path' => '/nonExistent.css'], $ex->getMetaInfo(true));
        }
    }

}
