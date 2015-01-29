<?php

class CM_Usertext_UsertextTest extends CMTest_TestCase {

    public function testProcess() {
        $usertext = new CM_Usertext_Usertext(new CM_Frontend_Render());
        $this->assertSame('foo bar', $usertext->transform('foo bar'));
    }

    public function testProcessEmoticon() {
        $usertext = new CM_Usertext_Usertext(new CM_Frontend_Render());

        $expectedValuePlain = "<img src=\"http://cdn.default.dev/layout/12392/1/img/emoticon/cold_sweat.png\" class=\"emoticon emoticon-cold_sweat\" title=\":cold_sweat:\" />";
        $expectedValueMarkdown = "<p><img src=\"http://cdn.default.dev/layout/12392/1/img/emoticon/cold_sweat.png\" class=\"emoticon emoticon-cold_sweat\" title=\":cold_sweat:\" /></p>";

        $usertext->setMode('oneline');
        $this->assertSame($expectedValuePlain, $usertext->transform(':-\\\\'));

        $usertext->setMode('simple');
        $this->assertSame($expectedValuePlain, $usertext->transform(':-\\\\'));

        $usertext->setMode('markdown');
        $this->assertSame($expectedValueMarkdown, $usertext->transform(':-\\\\'));

        $usertext->setMode('markdownPlain');
        $this->assertContains($expectedValuePlain, $usertext->transform(':-\\\\'));
    }
}
