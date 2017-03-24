<?php

class CM_Janus_FactoryTest extends CMTest_TestCase {

    public function testCreateServiceFail() {
        $iceServerList = [
            ['url' => 'turn:example.com:3478', 'username' => 'foo', 'credential' => 'bar'],
            ['url' => 'turn:test.com:3478', 'username' => 'baz', 'credential' => 'quux'],
        ];

        $serversConfig = [
            5 => [
                'key'              => 'foo-bar',
                'httpAddress'      => 'http://cm-janus.dev:8080',
                'webSocketAddress' => 'ws://cm-janus.dev:8188',
                'pluginList'       => ['audio', 'audioHD'],
                'coordinates'      => [
                    'latitude'  => 51.51,
                    'longitude' => -0.13,
                ],
                'iceServerList'    => $iceServerList,
            ],
            6 => [
                'key'              => 'foo-bar-baz',
                'httpAddress'      => 'http://cm-janus.dev:8081',
                'webSocketAddress' => 'ws://cm-janus.dev:8189',
                'pluginList'       => [],
                'coordinates'      => [
                    'latitude'  => 53.43,
                    'longitude' => 14.53,
                ],
                'iceServerList'    => $iceServerList,
            ],
        ];
        $factory = new CM_Janus_Factory();
        $exception = $this->catchException(function () use ($factory, $serversConfig) {
            $factory->createService($serversConfig);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Server pluginList is empty', $exception->getMessage());
    }

    public function testCreateServiceSuccess() {
        $iceServerList = [
            ['url' => 'turn:example.com:3478', 'username' => 'foo', 'credential' => 'bar'],
            ['url' => 'turn:test.com:3478', 'username' => 'baz', 'credential' => 'quux'],
        ];

        $serversConfig = [
            5 => [
                'key'              => 'foo-bar',
                'httpAddress'      => 'http://cm-janus.dev:8080',
                'webSocketAddress' => 'ws://cm-janus.dev:8188',
                'pluginList'       => ['audio', 'audioHD'],
                'coordinates'      => [
                    'latitude'  => 51.51,
                    'longitude' => -0.13,
                ],
                'iceServerList'    => $iceServerList,
            ],
            6 => [
                'key'              => 'foo-bar-baz',
                'httpAddress'      => 'http://cm-janus.dev:8081',
                'webSocketAddress' => 'ws://cm-janus.dev:8189',
                'pluginList'       => ['video', 'audio', 'videoHD'],
                'coordinates'      => [
                    'latitude'  => 53.43,
                    'longitude' => 14.53,
                ],
                'iceServerList'    => $iceServerList,
            ],
        ];
        $factory = new CM_Janus_Factory();
        $janus = $factory->createService($serversConfig);
        $servers = $janus->getServerList()->getAll();
        $this->assertCount(2, $servers);
        $this->assertSame(5, $servers[0]->getId());
        $this->assertSame('foo-bar', $servers[0]->getKey());
        $this->assertSame('http://cm-janus.dev:8080/', (string) $servers[0]->getHttpAddress());
        $this->assertSame('ws://cm-janus.dev:8188/', (string) $servers[0]->getWebSocketAddress());
        $this->assertSame($serversConfig[5]['pluginList'], $servers[0]->getPluginList());
        $this->assertSame(51.51, $servers[0]->getLocation()->getLatitude());
        $this->assertSame(-0.13, $servers[0]->getLocation()->getLongitude());
        $this->assertSame($iceServerList, $servers[0]->getIceServerList());

        $this->assertSame(6, $servers[1]->getId());
        $this->assertSame('foo-bar-baz', $servers[1]->getKey());
        $this->assertSame('http://cm-janus.dev:8081/', (string) $servers[1]->getHttpAddress());
        $this->assertSame('ws://cm-janus.dev:8189/', (string) $servers[1]->getWebSocketAddress());
        $this->assertSame($serversConfig[6]['pluginList'], $servers[1]->getPluginList());
        $this->assertSame(53.43, $servers[1]->getLocation()->getLatitude());
        $this->assertSame(14.53, $servers[1]->getLocation()->getLongitude());
        $this->assertSame($iceServerList, $servers[1]->getIceServerList());
    }
}
