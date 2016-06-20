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

    public function testFilterByChannelDefinition() {
        $channel = $this->mockClass('CM_Model_StreamChannel_Media', ['CM_Janus_StreamChannelInterface']);
        $channel->mockStaticMethod('getJanusPluginName')->set('plugin-name');

        $type = -1;
        CM_Config::get()->CM_Model_Abstract->types[$type] = $channel->getClassName(); 
        
        $channelDefinition = $this->mockClass('CM_StreamChannel_Definition')->newInstanceWithoutConstructor();
        $channelDefinition->mockMethod('getType')->set($type);
        /** @var CM_StreamChannel_Definition $channelDefinition */

        $serverList = $this->mockObject('CM_Janus_ServerList');
        $filterByPlugin = $serverList->mockMethod('filterByPlugin')->set('return-value');
        /** @var CM_Janus_ServerList $serverList */

        $returnValue = $serverList->filterByChannelDefinition($channelDefinition);
        $this->assertSame(1, $filterByPlugin->getCallCount());
        $this->assertSame(['plugin-name'], $filterByPlugin->getLastCall()->getArguments());
        $this->assertSame($filterByPlugin->getLastCall()->getReturnValue(), $returnValue);
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

    public function testFilterByClosestDistanceTo() {
        $location1 = new CM_Geo_Point(47, 41);
        $location2 = new CM_Geo_Point(50, 10);
        $serverLocation1 = new CM_Geo_Point(51, 0);
        $serverLocation2 = new CM_Geo_Point(55, 20);
        $serverLocation3 = new CM_Geo_Point(85, 25);
        
        $serverClass = $this->mockClass('CM_Janus_Server');
        $server1 = $serverClass->newInstanceWithoutConstructor();
        $server1->mockMethod('getLocation')->set($serverLocation1);

        $server2 = $serverClass->newInstanceWithoutConstructor();
        $server2->mockMethod('getLocation')->set($serverLocation2);
        $server3 = $serverClass->newInstanceWithoutConstructor();
        $server3->mockMethod('getLocation')->set($serverLocation2);
        
        $server4 = $serverClass->newInstanceWithoutConstructor();
        $server4->mockMethod('getLocation')->set($serverLocation3);
        
        $serverList = new CM_Janus_ServerList([
            $server1,
            $server2,
            $server3,
            $server4,
        ]);

        $this->assertSame([$server2, $server3], $serverList->filterByClosestDistanceTo($location1)->getAll());
        $this->assertSame([$server1], $serverList->filterByClosestDistanceTo($location2)->getAll());
    }
}
