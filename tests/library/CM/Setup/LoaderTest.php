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
}
