<?php

require_once __DIR__ . '/../../../TestCase.php';

class CM_AdproviderAdapter_OpenxTest extends TestCase {

	public function testGetHtml() {
		$configBackup = CM_Config::get();
		CM_Config::get()->CM_AdproviderAdapter_Openx->host = 'www.foo.org';
		$openx = new CM_AdproviderAdapter_Openx();
		$html = $openx->getHtml('zone1');

		$this->assertContains('https://www.foo.org/delivery/ajs.php', $html);
		$this->assertContains('document.write ("?zoneid=zone1")', $html);
		$this->assertContains('http://www.foo.org/delivery/avw.php?zoneid=zone1', $html);

		CM_Config::set($configBackup);
	}
}
