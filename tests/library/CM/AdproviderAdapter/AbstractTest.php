<?php

class CM_AdproviderAdapter_AbstractTest extends CMTest_TestCase {

    public function testFactory() {
        CM_Config::get()->CM_AdproviderAdapter_Abstract->class = CM_AdproviderAdapter_Revive::class;

        $this->assertInstanceOf(CM_AdproviderAdapter_Revive::class, CM_AdproviderAdapter_Abstract::factory());
    }
}
