<?php

class CM_MenuEntryTest extends CMTest_TestCase {

    public function testCompare() {
        $pageName = 'CM_Page_Mock';
        $label = 'helloworld';
        $menu = $this->_getMenu();

        // no param
        $entry = new CM_MenuEntry(['label' => $label, 'page' => $pageName], $menu);
        $this->assertTrue($entry->compare('/mock'));
        $this->assertFalse($entry->compare('/foo'));
        $this->assertTrue($entry->compare('/mock', ['param1' => 'foo']));

        // one param
        $entry = new CM_MenuEntry(['label' => $label, 'page' => $pageName, 'params' => ['param1' => 'foo']], $menu);
        $this->assertFalse($entry->compare('/mock'));
        $this->assertTrue($entry->compare('/mock', ['param1' => 'foo']));
        $this->assertFalse($entry->compare('/mock', ['param2' => 'foo']));
        $this->assertTrue($entry->compare('/mock', ['param2' => 'bar', 'param1' => 'foo']));

        // two params
        $entry = new CM_MenuEntry(['label' => $label, 'page' => $pageName, 'params' => ['param1' => 'foo', 'param2' => 'bar']], $menu);
        $this->assertFalse($entry->compare('/mock', ['param1' => 'foo']));
        $this->assertFalse($entry->compare('/mock', ['param2' => 'bar']));
        $this->assertTrue($entry->compare('/mock', ['param2' => 'bar', 'param1' => 'foo']));

        // array-param associative
        $entry = new CM_MenuEntry(['label' => $label, 'page' => $pageName, 'params' => ['param1' => ['foo' => 'bar', 'bar' => 'baz']]], $menu);
        $this->assertTrue($entry->compare('/mock', ['param1' => ['foo' => 'bar', 'bar' => 'baz']]));
        $this->assertTrue($entry->compare('/mock', ['param1' => ['bar' => 'baz', 'foo' => 'bar']]));
        $this->assertFalse($entry->compare('/mock', ['param1' => ['foo' => 'bar', 'bar' => 'baz', 'baz' => 'bar']]));
        $this->assertFalse($entry->compare('/mock', ['param1' => ['foo' => 'baz', 'bar' => 'foo']]));
        $this->assertFalse($entry->compare('/mock', ['param1' => ['foo' => 'bar', 'bar' => 'foo']]));
        $this->assertFalse($entry->compare('/mock', ['param1' => ['foo' => 'bar']]));
        $this->assertFalse($entry->compare('/mock', ['param1' => 'foo']));

        // array-param numeric
        $entry = new CM_MenuEntry(['label' => $label, 'page' => $pageName, 'params' => ['param1' => ['foo', 'bar']]], $menu);
        $this->assertFalse($entry->compare('/mock'));
        $this->assertTrue($entry->compare('/mock', ['param1' => ['foo', 'bar']]));
        $this->assertFalse($entry->compare('/mock', ['param1' => ['bar', 'foo']]));
        $this->assertFalse($entry->compare('/mock', ['param1' => 'foo']));

        // strict/nonstrict comparison
        $params = ['param1' => ''];
        $entry = new CM_MenuEntry(['label' => $label, 'page' => $pageName, 'params' => ['param1' => 0]], $menu);
        $this->assertTrue($entry->compare('/mock', ['param1' => 0]));
        $this->assertFalse($entry->compare('/mock', ['param1' => '']));
        $this->assertTrue($entry->compare('/mock', ['param1' => '0']));

        $entry = new CM_MenuEntry(['label' => $label, 'page' => $pageName, 'params' => ['param1' => ['foo' => 0]]], $menu);
        $this->assertTrue($entry->compare('/mock', ['param1' => ['foo' => 0]]));
        $this->assertFalse($entry->compare('/mock', ['param1' => ['foo' => '']]));
        $this->assertTrue($entry->compare('/mock', ['param1' => ['foo' => '0']]));
    }

    public function testGetters() {
        $pageName = 'CM_Page_Mock';
        $label = 'helloworld';

        $entry = new CM_MenuEntry(array('label' => $label, 'page' => $pageName), $this->_getMenu());

        $this->assertSame($pageName, $entry->getPageName());
        $this->assertEquals($label, $entry->getLabel());
    }

    public function testIsActive() {
        $pageName = 'CM_Page_Mock';
        $label = 'helloworld';

        $entry = new CM_MenuEntry(array('label' => $label, 'page' => $pageName), $this->_getMenu());
        $this->assertFalse($entry->isActive('/test', new CM_Params()));

        $entry = new CM_MenuEntry(array('label' => $label, 'page' => $pageName), $this->_getMenu());
        $this->assertTrue($entry->isActive('/mock', new CM_Params()));
    }

    public function testGetParent() {
        $pageName = 'CM_Page_Mock';
        $label = 'helloworld';

        $entry1 = new CM_MenuEntry(array('label' => $label, 'page' => $pageName), $this->_getMenu());

        $entry2 = new CM_MenuEntry(array('label' => $label, 'page' => $pageName), $this->_getMenu(), $entry1);

        $this->assertFalse($entry1->hasParent());
        $this->assertTrue($entry2->hasParent());
        $this->assertEquals($entry1, $entry2->getParent());
    }

    public function testGetParentFalse() {
        $pageName = 'CM_Page_Mock';
        $label = 'helloworld';

        $entry = new CM_MenuEntry(array('label' => $label, 'page' => $pageName), $this->_getMenu());
        $this->assertFalse($entry->hasParent());
    }

    public function testGetParents() {
        $pageName = 'CM_Page_Mock';
        $label = 'helloworld';

        $entry1 = new CM_MenuEntry(array('label' => $label . '1', 'page' => $pageName), $this->_getMenu());
        $entry2 = new CM_MenuEntry(array('label' => $label . '2', 'page' => $pageName), $this->_getMenu(), $entry1);
        $entry3 = new CM_MenuEntry(array('label' => $label . '3', 'page' => $pageName), $this->_getMenu(), $entry2);

        $this->assertCount(0, $entry1->getParents());
        $this->assertCount(1, $entry2->getParents());
        $this->assertCount(2, $entry3->getParents());

        $parents = $entry3->getParents();

        $this->assertEquals($entry2, $parents[1]);
        $this->assertEquals($entry1, $parents[0]);
    }

    public function testGetHash() {
        $menuArray = array();
        $menuArray[] = array('label' => 'Home1', 'page' => 'CM_Page_Mock2', 'params' => array('foo' => 1));
        $menuArray[] = array('label' => 'Home2', 'page' => 'CM_Page_Mock2', 'params' => array('foo' => 1));
        $menuArray[] = array('label' => 'Home3', 'page' => 'CM_Page_Mock2', 'params' => array('foo' => 2));
        $menuArray[] = array('label' => 'Example', 'page' => 'CM_Page_Mock', 'params' => array('foo' => 1));
        $menu = new CM_Menu($menuArray);

        $autoIds = array_map(function (CM_MenuEntry $entry) {
            return $entry->getHash();
        }, $menu->getAllEntries());

        $this->assertInternalType('string', $autoIds[0]);
        $this->assertSame(8, strlen($autoIds[0]));

        $this->assertSame($autoIds[0], $autoIds[1]);

        $this->assertNotSame($autoIds[0], $autoIds[2]);

        $this->assertNotSame($autoIds[0], $autoIds[3]);
    }

    /**
     * @return CM_Menu
     */
    private function _getMenu() {
        $entriesData = array(array('label' => 'Home', 'page' => 'CM_Page_Mock2'), array('label' => 'Example', 'page' => 'CM_Page_Mock'));
        return new CM_Menu($entriesData);
    }
}

class CM_Page_Mock extends CM_Page_Abstract {

}

class CM_Page_Mock2 extends CM_Page_Abstract {

}
