<?php

class CM_MessageStream_ServiceTest extends CMTest_TestCase {

    public function testGetAdapter() {
        $stream = new CM_MessageStream_Service();
        $this->assertNull($stream->getAdapter());

        $adapter = $this->mockObject('CM_MessageStream_Adapter_Abstract');
        $streamWithAdapter = new CM_MessageStream_Service($adapter);
        $this->assertSame($adapter, $streamWithAdapter->getAdapter());
    }

    public function testGetEnabled() {
        $streamDisabled = new CM_MessageStream_Service();
        $this->assertFalse($streamDisabled->getEnabled());

        $adapter = $this->mockObject('CM_MessageStream_Adapter_Abstract');
        $streamEnabled = new CM_MessageStream_Service($adapter);
        $this->assertTrue($streamEnabled->getEnabled());
    }

    public function testGetClientOptions() {
        $streamDisabled = new CM_MessageStream_Service();
        $this->assertSame([
            'enabled' => false,
        ], $streamDisabled->getClientOptions());

        $adapterOptions = ['foo' => 'bar'];
        $adapter = $this->mockObject('CM_MessageStream_Adapter_Abstract');
        $adapter->mockMethod('getOptions')->set($adapterOptions);
        $streamEnabled = new CM_MessageStream_Service($adapter);
        $this->assertSame([
            'enabled' => true,
            'adapter' => get_class($adapter),
            'options' => $adapterOptions,
        ], $streamEnabled->getClientOptions());
    }

    public function testPublish() {
        $adapter = $this->mockObject('CM_MessageStream_Adapter_Abstract');
        $publishMethod = $adapter->mockMethod('publish')->set(function ($channel, $event, $data) {
            $this->assertSame('channel', $channel);
            $this->assertSame('event', $event);
            $this->assertSame(['foo' => 'bar'], CM_Params::decode($data));
        });
        /** @var CM_MessageStream_Service|\Mocka\AbstractClassTrait $stream */
        $stream = $this->mockObject('CM_MessageStream_Service', [$adapter]);
        $stream->publish('channel', 'event', ['foo' => 'bar']);
        $this->assertSame(1, $publishMethod->getCallCount());

        $stream->mockMethod('getEnabled')->set(false);
        $stream->publish('channel', 'event', ['foo' => 'bar']);
        $this->assertSame(1, $publishMethod->getCallCount());
    }

    public function testSetServiceManager_AdapterNotServiceManagerAware() {
        $serviceManager = new CM_Service_Manager();
        $adapter = $this->mockObject('CM_MessageStream_Adapter_Abstract');
        $stream = new CM_MessageStream_Service($adapter);
        $stream->setServiceManager($serviceManager);
        $this->assertSame($serviceManager, $stream->getServiceManager());
    }

    public function testSetServiceManager_AdapterServiceManagerAware() {
        $serviceManager = new CM_Service_Manager();
        $adapter = new CM_MessageStream_Adapter_SocketRedis([]);
        $stream = new CM_MessageStream_Service($adapter);
        $stream->setServiceManager($serviceManager);
        $this->assertSame($serviceManager, $stream->getServiceManager());
        $this->assertSame($serviceManager, $adapter->getServiceManager());
    }
}
