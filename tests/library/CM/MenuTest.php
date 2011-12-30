<?php
require_once dirname(__FILE__) . '/../../TestCase.php';

class CM_MenuTest extends TestCase {

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testFindNull() {
		$menu = $this->_getMenu();
		$page = new CM_Page_Error_NotFound($menu->getRequest());

		$entry = $menu->findEntry($page, 3, 3);
		$this->assertNull($entry);

		$entry = $menu->findEntry($page, 1, 1);
		$this->assertNull($entry);
	}

	public function testFindEntry() {
		$menu = $this->_getMenu();
		$page = new CM_Page_Error_NotFound($menu->getRequest());

		$entry = $menu->findEntry($page);

		$this->assertInstanceOf('CM_Page_Error_NotFound', $entry->getPage());
	}

	public function testGetInstance() {
		$menu = $this->_getMenu();

		$this->assertInstanceOf('CM_Menu', $menu);

		$this->assertCount(2, $menu->getEntries());
		$this->assertCount(2, $menu->getAllEntries());
	}

	/**
	 * @param CM_Request_Abstract|null $request
	 * @return CM_Menu
	 */
	private function _getMenu(CM_Request_Abstract $request = null) {
		$entriesData = array(
			array('label' => 'Home', 'page' => 'CM_Page_Error_NotFound'),
			array('label' => 'Example', 'page' => 'CM_Page_Error_NotFound')
		);
		if (!$request) {
			$request = new CM_Request_Get('/test');
		}
		return new CM_Menu($entriesData, $request);
	}

}
