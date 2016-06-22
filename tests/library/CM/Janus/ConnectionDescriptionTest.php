<?php

class CM_Janus_ConnectionDescriptionTest extends CMTest_TestCase {

    public function testConstructorAndBasicGetters() {
        /** @var CM_StreamChannel_Definition $channelDefinition */
        $channelDefinition = $this->mockClass('CM_StreamChannel_Definition')->newInstanceWithoutConstructor();
        /** @var CM_Janus_Server $janusServer */
        $janusServer = $this->mockClass('CM_Janus_Server')->newInstanceWithoutConstructor();
        $connectionDescription = new CM_Janus_ConnectionDescription($channelDefinition, $janusServer);
        $this->assertSame($channelDefinition, $connectionDescription->getChannelDefinition());
        $this->assertSame($janusServer, $connectionDescription->getServer());
    }

    public function testJsonSerialize() {
        $channelDefinition = $this->mockClass('CM_StreamChannel_Definition')->newInstanceWithoutConstructor();
        $channelDefinition->mockMethod('jsonSerialize')->set(['foo' => 'bar']);
        /** @var CM_StreamChannel_Definition $channelDefinition */

        $janusServer = $this->mockClass('CM_Janus_Server')->newInstanceWithoutConstructor();
        $janusServer->mockMethod('jsonSerialize')->set(['bar' => 'foo']);
        /** @var CM_Janus_Server $janusServer */
        $connectionDescription = new CM_Janus_ConnectionDescription($channelDefinition, $janusServer);
        $this->assertSame(['foo' => 'bar', 'server' => ['bar' => 'foo']], $connectionDescription->jsonSerialize());
    }
}
