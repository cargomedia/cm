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
}
