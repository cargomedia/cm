<?php

class CM_AdproviderAdapter_OpenxTest extends CMTest_TestCase {

	public function testGetHtml() {
		CM_Config::get()->CM_Adprovider->enabled = true;
		CM_Config::get()->CM_AdproviderAdapter_Openx->host = 'www.foo.org';
		$openx = new CM_AdproviderAdapter_Openx();
		$html = $openx->getHtml(array('zoneId' => 'zone1'), array('foo' => 'bar'));

		$this->assertContains('<div class="openx-ad" data-zone-id="zone1" data-host="www.foo.org" data-variables="{&quot;foo&quot;:&quot;bar&quot;}"', $html);

		CMTest_TH::clearConfig();
	}
}
