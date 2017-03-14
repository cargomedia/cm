<?php

class CM_Usertext_UsertextTest extends CMTest_TestCase {

    public function testProcess() {
        $usertext = new CM_Usertext_Usertext();
        $this->assertSame('foo bar', $usertext->transform('foo bar', new CM_Frontend_Render()));
    }

    public function testProcessEmoticon() {
        $deployVersion = CM_App::getInstance()->getDeployVersion();
        $siteId = (new CM_Site_SiteFactory())->getDefaultSite()->getId();
        $usertext = new CM_Usertext_Usertext();

        $expectedValuePlain = "<img src=\"http://cdn.default.dev/layout/" . $siteId . "/" . $deployVersion .
            "/img/emoticon/cold_sweat.png\" class=\"emoticon emoticon-cold_sweat\" title=\":cold_sweat:\" />";
        $expectedValueMarkdown = "<p><img src=\"http://cdn.default.dev/layout/" . $siteId . "/" . $deployVersion .
            "/img/emoticon/cold_sweat.png\" class=\"emoticon emoticon-cold_sweat\" title=\":cold_sweat:\" /></p>";

        $usertext->setMode('escape');
        $render = new CM_Frontend_Render();
        $this->assertSame('&lt;3', $usertext->transform('<3', $render));

        $usertext->setMode('oneline');
        $this->assertSame($expectedValuePlain, $usertext->transform(':-\\\\', $render));

        $usertext->setMode('simple');
        $this->assertSame($expectedValuePlain, $usertext->transform(':-\\\\', $render));

        $usertext->setMode('markdown');
        $this->assertSame($expectedValueMarkdown, $usertext->transform(':-\\\\', $render));

        $usertext->setMode('markdownPlain');
        $this->assertContains($expectedValuePlain, $usertext->transform(':-\\\\', $render));
    }

    public function testAllowBadwords() {
        $usertext = new CM_Usertext_Usertext();
        $usertext->setMode('escape', ['allowBadwords' => true]);

        $badwordList = new CM_Paging_ContentList_Badwords();
        $badWord = 'testBad';
        $badwordList->add($badWord);
        $sentString = 'Hello i am ' . $badWord . ' !';

        $this->assertSame($sentString, $usertext->transform($sentString, new CM_Frontend_Render()));
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
        $usertext = new CM_Usertext_Usertext();
        $usertext->addFilter($filter1);
        $usertext->addFilter($filter2);
        $usertext->addFilterAfter(get_class($filter1), $filterBetween);

        $this->assertSame('....', $usertext->transform('.', new CM_Frontend_Render()));
    }

    public function testAddFilterAfterNoFilterFound() {
        $usertext = new CM_Usertext_Usertext();
        $exception = $this->catchException(function () use ($usertext) {
            $usertext->addFilterAfter('Filter Not Set', new CM_Usertext_Filter_Emoticon());
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        /** @var CM_Exception_Invalid $exception */
        $this->assertSame('Filter not found', $exception->getMessage());
        $this->assertSame(['filter' => 'Filter Not Set'], $exception->getMetaInfo());
    }
}
