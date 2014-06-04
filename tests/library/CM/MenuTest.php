<?php

class CM_MenuTest extends CMTest_TestCase {

    public function testFindNull() {
        $menu = $this->_getMenu();
        $pageName = 'CM_Page_Mock3';
        $pageParams = new CM_Params();

        $entry = $menu->findEntry($pageName, $pageParams, 3, 3);
        $this->assertNull($entry);

        $entry = $menu->findEntry($pageName, $pageParams, 2);
        $this->assertNull($entry);
    }

    public function testFindEntry() {
        $menu = $this->_getMenu();
        $pageName = 'CM_Page_Mock3';
        $pageParams = new CM_Params();

        $entry = $menu->findEntry($pageName, $pageParams);
        $this->assertSame('CM_Page_Mock3', $entry->getPageName());
    }

    public function testGetInstance() {
        $menu = $this->_getMenu();
        $environment = new CM_Frontend_Environment();

        $this->assertInstanceOf('CM_Menu', $menu);

        $this->assertCount(2, $menu->getEntries($environment));
        $this->assertCount(2, $menu->getAllEntries());
    }

    public function testFindEntries() {
        $menu = $this->_getMenu();
        $pageName = 'CM_Page_Mock3';
        $pageParams = new CM_Params();

        $entries = $menu->findEntries($pageName, $pageParams);
        $this->assertSame(2, count($entries));
        $this->assertSame(array($menu->findEntry($pageName, $pageParams), $menu->findEntry($pageName, $pageParams, 1)), $entries);

        $this->assertSame(array(), $menu->findEntries('CM_Page_Mock6', $pageParams));
    }

    /**
     * @return CM_Menu
     */
    private function _getMenu() {
        $entriesData = array(array('label' => 'Home', 'page' => 'CM_Page_Mock4'),
            array('label' => 'Example', 'page' => 'CM_Page_Mock3', 'submenu' => array(array('label' => 'Example', 'page' => 'CM_Page_Mock3'))));
        return new CM_Menu($entriesData);
    }
}

class CM_Page_Mock3 extends CM_Page_Abstract {

}

class CM_Page_Mock4 extends CM_Page_Abstract {

}

class CM_Page_Mock6 extends CM_Page_Abstract {

}
