<?php

class CM_Response_Resource_LayoutTest extends CMTest_TestCase {

    public function testFiletypeForbidden() {
        $filePath = 'browserconfig.xml.smarty';
        try {
            $this->getResponseResourceLayout($filePath);
        } catch (CM_Exception_Nonexistent $ex) {
            $this->assertSame('Forbidden file-type', $ex->getMessage());
            $this->assertSame(['path' => '/browserconfig.xml.smarty'], $ex->getMetaInfo(true));
        }
    }

}
