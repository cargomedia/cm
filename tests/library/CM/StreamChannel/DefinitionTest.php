<?php

class CM_StreamChannel_DefinitionTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testConstruct() {
        $definition = new CM_StreamChannel_Definition('foo', 12, 13);

        $this->assertSame('foo', $definition->getKey());
        $this->assertSame(12, $definition->getType());
        $this->assertSame(13, $definition->getAdapterType());
    }

    public function testConstructOptionalAdapterType() {
        $definition = new CM_StreamChannel_Definition('foo', 12);

        $this->assertSame(null, $definition->getAdapterType());
    }

    public function testGetStreamChannel() {
        $channel = CMTest_TH::createStreamChannel();
        $definition = $channel->getDefinition();

        $this->assertEquals($channel, $definition->getStreamChannel());
    }

    /**
     * @expectedException CM_Exception
     */
    public function testGetStreamChannelNonexistent() {
        $definition = new CM_StreamChannel_Definition('foo', 12);

        $definition->getStreamChannel();
    }

    public function testExists() {
        $channel = CMTest_TH::createStreamChannel();
        $definition = $channel->getDefinition();

        $this->assertSame(true, $definition->exists());
    }

    public function testExistsNonexistent() {
        $definition = new CM_StreamChannel_Definition('foo', 12);

        $this->assertSame(false, $definition->exists());
    }

    public function testFindStreamChannel() {
        $channel = CMTest_TH::createStreamChannel();
        $definition = $channel->getDefinition();

        $this->assertEquals($channel, $definition->findStreamChannel());
    }

    public function testFindStreamChannelNonexistent() {
        $definition = new CM_StreamChannel_Definition('foo', 12);

        $this->assertSame(null, $definition->findStreamChannel());
    }

    public function testArrayConvertible() {
        $definition = new CM_StreamChannel_Definition('foo', 12);

        $array = $definition->toArrayIdOnly();
        $this->assertEquals(CM_StreamChannel_Definition::fromArray($array), $definition);
    }
}
