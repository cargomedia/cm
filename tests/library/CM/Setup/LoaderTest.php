<?php

class CM_Provision_LoaderTest extends CMTest_TestCase {

    public function testGetScriptList() {
        $script1 = $this->mockClass('CM_Provision_Script_Abstract')->newInstanceWithoutConstructor();
        $script2 = $this->mockClass('CM_Provision_Script_Abstract')->newInstanceWithoutConstructor();
        $script2->mockMethod('getRunLevel')->set(10);
        $script3 = $this->mockClass('CM_Provision_Script_Abstract')->newInstanceWithoutConstructor();
        $script3->mockMethod('getRunLevel')->set(1);

        $loader = new CM_Provision_Loader();
        $loader->registerScript($script1);
        $loader->registerScript($script2);
        $loader->registerScript($script3);

        $scriptList = CMTest_TH::callProtectedMethod($loader, '_getScriptList');
        $expected = [$script3, $script1, $script2];
        $this->assertSame($expected, $scriptList);
    }

    public function testLoad() {
        $outputStream = new CM_OutputStream_Null();

        $script = $this->mockClass('CM_Provision_Script_Abstract')->newInstanceWithoutConstructor();
        $script->mockMethod('shouldBeLoaded')->set(true);
        $loadMethod = $script->mockMethod('load')->set(function ($output) use ($outputStream) {
            $this->assertSame($outputStream, $output);
        });
        /** @var CM_Provision_Script_Abstract $script */

        $loader = new CM_Provision_Loader($outputStream);
        $loader->registerScript($script);
        $loader->load();
        $this->assertSame(1, $loadMethod->getCallCount());
    }

    public function testUnload() {
        $outputStream = new CM_OutputStream_Null();

        $script = $this->mockClass('CM_Provision_Script_Abstract', ['CM_Provision_Script_UnloadableInterface'])->newInstanceWithoutConstructor();
        $script->mockMethod('shouldBeUnloaded')
            ->at(0, true)
            ->at(1, false)
            ->at(2, true);
        $unloadMethod = $script->mockMethod('unload')->set(function ($output) use ($outputStream) {
            $this->assertSame($outputStream, $output);
        });
        /** @var CM_Provision_Script_Abstract $script */

        $loader = new CM_Provision_Loader($outputStream);
        $loader->registerScript($script);
        $loader->registerScript($script);
        $loader->registerScript($script);
        $loader->unload();
        $this->assertSame(2, $unloadMethod->getCallCount());
    }
}
