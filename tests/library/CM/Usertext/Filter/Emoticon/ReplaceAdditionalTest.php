<?php

class CM_Usertext_Filter_Emoticon_ReplaceAdditionalTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testProcess() {
        $text = 'Some text and and some emoticons :-\\\\ :-) :-[';
        $expected = 'Some text and and some emoticons :cold_sweat: :smiley: :confused:';

        $filter = new CM_Usertext_Filter_Emoticon_ReplaceAdditional();
        $actual = $filter->transform($text, new CM_Frontend_Render());

        $this->assertSame($expected, $actual);
    }
}
