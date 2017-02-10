<?php

class CM_Clockwork_EventTest extends CMTest_TestCase {

    public function testConstruct() {
        $event = new CM_Clockwork_Event('foo', '1second');
        $this->assertSame('foo', $event->getName());
        $this->assertSame('1second', $event->getDateTimeString());
    }
}
