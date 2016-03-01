<?php

class CM_Janus_ConfigurationTest extends CMTest_TestCase {

    public function testFindServerByKey() {
        $server1 = $this->mockClass('CM_Janus_Server')->newInstanceWithoutConstructor();
        $server1->mockMethod('getKey')->set('foo');
        $server2 = $this->mockClass('CM_Janus_Server')->newInstanceWithoutConstructor();
        $server2->mockMethod('getKey')->set('bar');

        $configuration = new CM_Janus_Configuration([$server1, $server2]);

        $this->assertSame($server1, $configuration->findServerByKey('foo'));
        $this->assertSame($server2, $configuration->findServerByKey('bar'));
        $this->assertSame(null, $configuration->findServerByKey('zoo'));
    }

    public function testFindServerByPlugin() {
        $server1 = $this->mockClass('CM_Janus_Server')->newInstanceWithoutConstructor();
        $server1->mockMethod('getPluginList')->set(['audio', 'audioHD', 'video']);
        $server2 = $this->mockClass('CM_Janus_Server')->newInstanceWithoutConstructor();
        $server2->mockMethod('getPluginList')->set(['video', 'videoHD']);

        $configuration = new CM_Janus_Configuration([$server1, $server2]);

        $this->assertSame($server1, $configuration->findServerByPlugin('audio'));
        $this->assertSame($server1, $configuration->findServerByPlugin('audioHD'));
        $this->assertSame($server2, $configuration->findServerByPlugin('videoHD'));
        mt_srand(1);
        $this->assertSame($server2, $configuration->findServerByPlugin('video'));
        mt_srand(3);
        $this->assertSame($server1, $configuration->findServerByPlugin('video'));
        $this->assertSame(null, $configuration->findServerByKey('bar'));
    }


    public function testGetServer() {
        $server = $this->mockClass('CM_Janus_Server')->newInstanceWithoutConstructor();
        $server->mockMethod('getId')->set(1);
        $configuration = new CM_Janus_Configuration([$server]);

        $this->assertSame($server, $configuration->getServer(1));

        $exception = $this->catchException(function () use ($configuration) {
            $configuration->getServer(2);
        });
        $this->assertTrue($exception instanceof CM_Exception_Invalid);
        $this->assertSame('Cannot find server with id `2`', $exception->getMessage());
    }
}
