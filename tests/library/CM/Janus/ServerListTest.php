<?php

class CM_Janus_ServerListTest extends CMTest_TestCase {

    public function testFindByKey() {
        $server1 = $this->mockClass('CM_Janus_Server')->newInstanceWithoutConstructor();
        $server1->mockMethod('getKey')->set('foo');
        $server2 = $this->mockClass('CM_Janus_Server')->newInstanceWithoutConstructor();
        $server2->mockMethod('getKey')->set('bar');

        $serverList = new CM_Janus_ServerList([$server1, $server2]);

        $this->assertSame($server1, $serverList->findByKey('foo'));
        $this->assertSame($server2, $serverList->findByKey('bar'));
        $this->assertSame(null, $serverList->findByKey('zoo'));
    }

    public function testFilterByPlugin() {
        $server1 = $this->mockClass('CM_Janus_Server')->newInstanceWithoutConstructor();
        $server1->mockMethod('getPluginList')->set(['audio', 'audioHD', 'video']);
        $server2 = $this->mockClass('CM_Janus_Server')->newInstanceWithoutConstructor();
        $server2->mockMethod('getPluginList')->set(['video', 'videoHD']);

        $serverList = new CM_Janus_ServerList([$server1, $server2]);

        $this->assertSame([$server1], $serverList->filterByPlugin('audio')->getAll());
        $this->assertSame([$server1], $serverList->filterByPlugin('audioHD')->getAll());
        $this->assertSame([$server1, $server2], $serverList->filterByPlugin('video')->getAll());
        $this->assertSame([], $serverList->filterByPlugin('bar')->getAll());
    }

    public function testGetById() {
        $server = $this->mockClass('CM_Janus_Server')->newInstanceWithoutConstructor();
        $server->mockMethod('getId')->set(1);
        $serverList = new CM_Janus_ServerList([$server]);

        $this->assertSame($server, $serverList->getById(1));

        $exception = $this->catchException(function () use ($serverList) {
            $serverList->getById(2);
        });
        $this->assertTrue($exception instanceof CM_Exception_Invalid);
        $this->assertSame('Cannot find server with id `2`', $exception->getMessage());
    }
}
