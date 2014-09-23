<?php

class CM_Response_Resource_LayoutTest extends CMTest_TestCase {

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

    public function testRendering() {
        $filePath = 'browserconfig.xml';
        /** @var CM_Response_Resource_Layout $response */
        $response = $this->getResponseResourceLayout($filePath);
        $this->assertContains('Content-Type: application/xml', $response->getHeaders());
        try {
            $response->getRender()->getLayoutFile('resource/' . $filePath);
            $this->fail('File to be rendered actually exists');
        } catch (CM_Exception_Invalid $ex) {
            $this->assertContains('Cannot find `resource/browserconfig.xml`', $ex->getMessage());
        }
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
