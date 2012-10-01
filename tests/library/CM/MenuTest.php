<?php
require_once __DIR__ . '/../../TestCase.php';

class CM_MenuTest extends TestCase {

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testFindNull() {
		$menu = $this->_getMenu();
		$page = new CM_Page_Mock3();

		$entry = $menu->findEntry($page, 3, 3);
		$this->assertNull($entry);

		$entry = $menu->findEntry($page, 1, 1);
		$this->assertNull($entry);
	}

	public function testFindEntry() {
		$menu = $this->_getMenu();
		$page = new CM_Page_Mock3();

		$entry = $menu->findEntry($page);

		$this->assertInstanceOf('CM_Page_Mock3', $entry->getPage());
	}

	public function testGetInstance() {
		$menu = $this->_getMenu();

		$this->assertInstanceOf('CM_Menu', $menu);

		$this->assertCount(2, $menu->getEntries());
		$this->assertCount(2, $menu->getAllEntries());
	}

	/**
	 * @return CM_Menu
	 */
	private function _getMenu() {
		$entriesData = array(array('label' => 'Home', 'page' => 'CM_Page_Mock4'), array('label' => 'Example', 'page' => 'CM_Page_Mock3'));
		return new CM_Menu($entriesData);
	}

}

class CM_Page_Mock3 extends CM_Page_Abstract {
}

class CM_Page_Mock4 extends CM_Page_Abstract {
}
