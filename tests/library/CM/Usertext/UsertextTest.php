<?php

class CM_Usertext_UsertextTest extends CMTest_TestCase {

    public function testProcess() {
        $usertext = new CM_Usertext_Usertext(new CM_Frontend_Render());
        $this->assertSame('foo bar', $usertext->transform('foo bar'));
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
        $usertext->setMode('escape', ['allowBadwords' => true]);

        $badwordList = new CM_Paging_ContentList_Badwords();
        $badWord = 'testBad';
        $badwordList->add($badWord);
        $sentString = 'Hello i am ' . $badWord . ' !';

        $this->assertSame($sentString, $usertext->transform($sentString));
    }

    public function testTransformWithFilterBefore() {
        $filter1 = $this->mockInterface('CM_Usertext_Filter_Interface')->newInstance();
        $filter2 = $this->mockInterface('CM_Usertext_Filter_Interface')->newInstance();
        $filterBetween = $this->mockInterface('CM_Usertext_Filter_Interface')->newInstance();
        $filter1->mockMethod('transform')->set(function ($input) {
            $this->assertSame('.', $input);
            return $input . '.';
        });
        /** @var CM_Usertext_Filter_Interface $filter1 */
        $filter2->mockMethod('transform')->set(function ($input) {
            $this->assertSame('...', $input);
            return $input . '.';
        });
        /** @var CM_Usertext_Filter_Interface $filter2 */
        $filterBetween->mockMethod('transform')->set(function ($input) {
            $this->assertSame('..', $input);
            return $input . '.';
        });
        /** @var CM_Usertext_Filter_Interface $filterBetween */
        $usertext = new CM_Usertext_Usertext(new CM_Frontend_Render());
        $usertext->addFilter($filter1);
        $usertext->addFilter($filter2);
        $usertext->addFilterAfter(get_class($filter1), $filterBetween);

        $this->assertSame('....', $usertext->transform('.'));
    }
}
