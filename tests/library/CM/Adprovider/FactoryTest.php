<?php

class CM_Adprovider_FactoryTest extends CMTest_TestCase {

    public function testCreateAdapter() {
        $adapterClass = $this->mockClass('CM_Adprovider_Adapter_Abstract');
        $constructor = $adapterClass->mockMethod('__construct')->set(function ($foo) {
            $this->assertSame('bar', $foo);
        });
        $factory = new CM_Adprovider_Factory();
        $factory->createAdapter($adapterClass->getClassName(), ['foo' => 'bar']);
        $this->assertSame(1, $constructor->getCallCount());
    }

    public function testCreateClient() {
        $factory = $this->mockObject('CM_Adprovider_Factory');
        $createAdapterMethod = $factory->mockMethod('createAdapter')->set(function ($class, $arguments) {
            $this->assertSame('adapterName', $class);
            $this->assertSame(['adapterArgument1' => 'foo'], $arguments);
            return $this->mockObject('CM_Adprovider_Adapter_Abstract');
        });
        /** @var CM_Adprovider_Factory $factory */
        $client = $factory->createClient(true, ['foo' => 'bar'], ['adapterName' => ['adapterArgument1' => 'foo']]);
        $this->assertInstanceOf('CM_Adprovider_Client', $client);
        $this->assertSame(1, $createAdapterMethod->getCallCount());
    }
}
