<?php

class CM_Cache_Storage_RuntimeTest extends CMTest_TestCase {

    public function testDeleteExpired() {
        CMTest_TH::timeInit();
        $runtime = new CM_Cache_Storage_Runtime();
        $runtime->set('foo', true);
        $this->assertTrue($runtime->get('foo'));
        CMTest_TH::timeForward(5);
        $runtime->set('bar', true);
        $this->assertFalse($runtime->get('foo'));
    }
}
