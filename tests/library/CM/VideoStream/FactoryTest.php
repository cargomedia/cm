<?php

class CM_MediaStream_FactoryTest extends CMTest_TestCase {

    public function testCreateService() {
        $adapterClass = $this->mockClass('CM_MediaStream_Adapter_Abstract');
        $adapterConstructor = $adapterClass->mockMethod('__construct')->set(function ($argument) {
            $this->assertSame('foo', $argument);
        });
        $adapterClassName = $adapterClass->getClassName();

        $factory = new CM_MediaStream_Factory();
        $service = $factory->createService($adapterClassName, ['foo']);
        $this->assertInstanceOf('CM_MediaStream_Service', $service);
        $this->assertSame(1, $adapterConstructor->getCallCount());
    }
}
