<?php

class CM_Usertext_UsertextTest extends CMTest_TestCase {

    public function testProcess() {
        $usertext = new CM_Usertext_Usertext(new CM_Frontend_Render());
        $this->assertSame('foo bar', $usertext->transform('foo bar'));
    }
}
