<?php

class CM_Usertext_UsertextTest extends CMTest_TestCase {

    public function testProcess() {
        $usertext = new CM_Usertext_Usertext(new CM_Frontend_Render());
        $this->assertSame('foo bar', $usertext->transform('foo bar'));
    }

    public function testSetRender() {
        $render = new CM_Frontend_Render();
        $usertext = new CM_Usertext_Usertext(new CM_Frontend_Render());
        $usertext->setRender($render);
        $this->assertSame($render, $usertext->getRender());
    }

    public function testProcessNoRender() {
        $usertext = new CM_Usertext_Usertext();
        $exception = $this->catchException(function() use ($usertext) {
           $usertext->transform('foo bar');
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Render not set', $exception->getMessage());
    }

    public function testProcessEmoticon() {
        $deployVersion = CM_App::getInstance()->getDeployVersion();
        $siteType = CM_Site_Abstract::factory()->getType();
        $usertext = new CM_Usertext_Usertext(new CM_Frontend_Render());

        $expectedValuePlain = "<img src=\"http://cdn.default.dev/layout/" . $siteType . "/" . $deployVersion .
            "/img/emoticon/cold_sweat.png\" class=\"emoticon emoticon-cold_sweat\" title=\":cold_sweat:\" />";
        $expectedValueMarkdown = "<p><img src=\"http://cdn.default.dev/layout/" . $siteType . "/" . $deployVersion .
            "/img/emoticon/cold_sweat.png\" class=\"emoticon emoticon-cold_sweat\" title=\":cold_sweat:\" /></p>";

        $usertext->setMode('escape');
        $this->assertSame('&lt;3', $usertext->transform('<3'));

        $usertext->setMode('oneline');
        $this->assertSame($expectedValuePlain, $usertext->transform(':-\\\\'));

        $usertext->setMode('simple');
        $this->assertSame($expectedValuePlain, $usertext->transform(':-\\\\'));

        $usertext->setMode('markdown');
        $this->assertSame($expectedValueMarkdown, $usertext->transform(':-\\\\'));

        $usertext->setMode('markdownPlain');
        $this->assertContains($expectedValuePlain, $usertext->transform(':-\\\\'));
    }

    public function testAllowBadwords() {
        $usertext = new CM_Usertext_Usertext(new CM_Frontend_Render());
        $usertext->setMode('escape', null, null, null, true);

        $badwordList = new CM_Paging_ContentList_Badwords();
        $badWord = 'testBad';
        $badwordList->add($badWord);
        $sentString = 'Hello i am ' . $badWord . ' !';

        $this->assertSame($sentString, $usertext->transform($sentString));
    }
}
