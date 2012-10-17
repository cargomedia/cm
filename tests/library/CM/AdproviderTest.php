<?php

require_once __DIR__ . '/../../TestCase.php';

class CM_AdproviderTest extends TestCase {

	public function testGetHtml() {
		$configBackup = CM_Config::get();
		$adprovider = new CM_Adprovider();

		CM_Config::get()->CM_Adprovider->enabled = true;
		$this->assertNotEmpty($adprovider->getHtml(1));

		CM_Config::get()->CM_Adprovider->enabled = false;
		$this->assertSame('', $adprovider->getHtml(1));

		CM_Config::set($configBackup);
	}
}