<?php

class CM_Wowza_FactoryTest extends CMTest_TestCase {

    public function testCreateService() {
        $servers = [
            [
                'publicHost' => 'localhost',
                'publicIp'   => '127.0.0.1',
                'privateIp'  => '127.0.0.1',
                'httpPort'   => '8086',
                'wowzaPort'  => '1935',
            ],
        ];
        $factory = new CM_Wowza_Factory();
        $wowza = $factory->createService($servers);
        $this->assertInstanceOf('CM_Wowza_Service', $wowza);
        $this->assertCount(1, $wowza->getConfiguration()->getServers());
    }
}
