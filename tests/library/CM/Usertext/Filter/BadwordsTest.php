<?php

class CM_Usertext_Filter_BadwordsTest extends CMTest_TestCase {

    public function testTransformDefaultReplacement() {
        $filter = new CM_Usertext_Filter_Badwords();

        $badwords = new CM_Paging_ContentList_Badwords();
        $badwords->add('foo');

        $this->assertSame('… bar', $filter->transform('foo bar', new CM_Frontend_Render()));
    }

    public function testTransformCustomReplacement() {
        $filter = new CM_Usertext_Filter_Badwords('---');

        $badwords = new CM_Paging_ContentList_Badwords();
        $badwords->add('foo');

        $this->assertSame('--- bar', $filter->transform('foo bar', new CM_Frontend_Render()));
    }
}
