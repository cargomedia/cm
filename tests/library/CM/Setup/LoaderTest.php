<?php

class CM_Setup_LoaderTest extends CMTest_TestCase {

    public function testOrderSetupScriptList() {
        $script1 = $this->mockObject('CM_Setup_Script_Abstract');
        $script2 = $this->mockObject('CM_Setup_Script_Abstract');
        $script2->mockMethod('getOrder')->set(2);
        $script3 = $this->mockObject('CM_Setup_Script_Abstract');
        $script3->mockMethod('getOrder')->set(1);
        $script4 = $this->mockObject('CM_Setup_Script_Abstract');
        $setupScriptList = [$script1, $script2, $script3, $script4];

        $loader = new CM_Setup_Loader();
        $sortedList = CMTest_TH::callProtectedMethod($loader, '_orderSetupScriptList', [$setupScriptList]);

        $expectedList = [$script3, $script2, $script1, $script4];
        $this->assertSame($expectedList, $sortedList);
    }

    public function testLoad() {
        $fooSetupScriptClass = new \Mocka\ClassMock('Foo_SetupScript', 'CM_Setup_Script_Abstract');
        $barSetupScriptClass = new \Mocka\ClassMock('Bar_SetupScript', 'CM_Setup_Script_Abstract');

        $script1 = $fooSetupScriptClass->newInstance();
        $script2 = $barSetupScriptClass->newInstance();
        $script3 = $barSetupScriptClass->newInstance();
        $script4 = $fooSetupScriptClass->newInstance();
        $script5 = $barSetupScriptClass->newInstance();
        $scriptList = [$script1, $script2, $script3, $script4, $script5];

        $loader = $this->mockObject('CM_Setup_Loader');
        $loader->mockMethod('_getSetupScriptList')->set($scriptList);
        $orderSetupScriptListMethod = $loader->mockMethod('_orderSetupScriptList')
            ->at(0, function ($list) {
                $this->assertContainsOnlyInstancesOf('Foo_SetupScript', $list);
                return $list;
            })
            ->at(1, function ($list) {
                $this->assertContainsOnlyInstancesOf('Bar_SetupScript', $list);
                return $list;
            });

        /** @var CM_Setup_Loader $loader */
        $loader->setServiceManager(new CM_Service_Manager());
        $loader->load();
        $this->assertSame(2, $orderSetupScriptListMethod->getCallCount());
    }
}
