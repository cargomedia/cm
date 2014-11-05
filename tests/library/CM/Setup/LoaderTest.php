<?php

class CM_Provision_LoaderTest extends CMTest_TestCase {

    public function testLoad() {
        $serviceManager = new CM_Service_Manager();

        $script = $this->mockObject('CM_Provision_Script_Abstract');
        $loadMethod = $script->mockMethod('load')->set(function (CM_Service_Manager $manager) use ($serviceManager) {
            $this->assertSame($serviceManager, $manager);
        });
        /** @var CM_Provision_Script_Abstract $script */

        $loader = new CM_Provision_Loader();
        $loader->setServiceManager($serviceManager);
        $loader->registerScript($script);
        $loader->load();
        $this->assertSame(1, $loadMethod->getCallCount());
    }

    public function testGetScriptList() {

        $script1 = $this->mockObject('CM_Provision_Script_Abstract');
        $script2 = $this->mockObject('CM_Provision_Script_Abstract');
        $script2->mockMethod('getRunLevel')->set(10);
        $script3 = $this->mockObject('CM_Provision_Script_Abstract');
        $script3->mockMethod('getRunLevel')->set(1);

        $loader = new CM_Provision_Loader();
        $loader->registerScript($script1);
        $loader->registerScript($script2);
        $loader->registerScript($script3);

        $scriptList = CMTest_TH::callProtectedMethod($loader, '_getScriptList');
        $expected = [$script3, $script1, $script2];
        $this->assertSame($expected, $scriptList);
    }
}
