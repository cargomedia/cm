<?php

class CM_Janus_FactoryTest extends CMTest_TestCase {

    public function testCreateService() {
        $serversConfig = [
            5 => [
                'key'            => 'foo-bar',
                'httpAddress'      => 'http://cm-janus.dev:8080',
                'webSocketAddress' => 'ws://cm-janus.dev:8188',
            ],
        ];
        $factory = new CM_Janus_Factory();
        $janus = $factory->createService($serversConfig);
        $servers = $janus->getConfiguration()->getServers();
        $this->assertCount(1, $servers);
        $this->assertSame(5, $servers[0]->getId());
        $this->assertSame('foo-bar', $servers[0]->getKey());
        $this->assertSame('http://cm-janus.dev:8080', $servers[0]->getHttpAddress());
        $this->assertSame('ws://cm-janus.dev:8188', $servers[0]->getWebSocketAddress());
    }
}
