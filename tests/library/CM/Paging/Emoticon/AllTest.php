<?php

class CM_Paging_Emoticon_AllTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testAll() {
        $paging = new CM_Paging_Emoticon_All();
        /** @var CM_Emoticon $emoticonFromPaging */
        $emoticonFromPaging = $paging->getItem(0);
        $this->assertInstanceOf('CM_Emoticon', $emoticonFromPaging);
        $emoticonRegular = new CM_Emoticon($emoticonFromPaging->getName());
        $this->assertEquals($emoticonFromPaging, $emoticonRegular);
    }
}
