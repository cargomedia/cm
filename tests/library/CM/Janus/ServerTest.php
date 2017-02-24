<?php

class CM_Janus_ServerTest extends CMTest_TestCase {

    public function testConstructorAndBasicGetters() {
        $serverId = 1;
        $key = 'server-key';
        $httpAddress = 'http://api';
        $webSocketAddress = 'ws://connect:8810';
        $pluginList = ['my-plugin'];
        $location = CMTest_TH::createGeoPoint();
        $iceServerList = ['ice-server'];

        $server = new CM_Janus_Server($serverId, $key, $httpAddress, $webSocketAddress, $pluginList, $location, $iceServerList);

        $this->assertSame($serverId, $server->getId());
        $this->assertSame($key, $server->getKey());
        $this->assertSame($httpAddress, $server->getHttpAddress());
        $this->assertSame($webSocketAddress, $server->getWebSocketAddress());
        $this->assertSame($pluginList, $server->getPluginList());
        $this->assertSame($location, $server->getLocation());
        $this->assertSame($iceServerList, $server->getIceServerList());
    }

    public function testGetWebSocketAddressSubscribeOnly() {
        $serverId = 1;
        $key = 'server-key';
        $httpAddress = 'http://api';
        $webSocketAddress = 'ws://connect:8810';
        $pluginList = ['my-plugin'];
        $location = CMTest_TH::createGeoPoint();

        $server = new CM_Janus_Server($serverId, $key, $httpAddress, $webSocketAddress, $pluginList, $location);

        $this->assertSame('ws://connect:8810?subscribeOnly=1', $server->getWebSocketAddressSubscribeOnly());
    }
}
