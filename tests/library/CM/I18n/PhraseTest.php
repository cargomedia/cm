<?php

class CM_I18n_PhraseTest extends CMTest_TestCase {

    public function testTranslate() {
        $phrase = '{$hello} world {$time}';
        $variables = ['hello' => 'hi', 'time' => 'now'];
        $i18n_phrase = new CM_I18n_Phrase($phrase, $variables);
        $this->assertInstanceOf('CM_I18n_Phrase', $i18n_phrase);

        /** @var CM_Frontend_Render|\Mocka\AbstractClassTrait $render */
        $render = $this->mockClass('CM_Frontend_Render')->newInstance();
        $getTranslationMethod = $render->mockMethod('getTranslation')->set(function ($key, $params) use ($phrase, $variables) {
            $this->assertSame($phrase, $key);
            $this->assertSame($variables, $params);
        });

        $i18n_phrase->translate($render);
        $this->assertSame(1, $getTranslationMethod->getCallCount());

        $phrase = 'String without params';
        $i18n_phrase = new CM_I18n_Phrase($phrase);
        $this->assertInstanceOf('CM_I18n_Phrase', $i18n_phrase);

        /** @var CM_Frontend_Render|\Mocka\AbstractClassTrait $render */
        $render = $this->mockClass('CM_Frontend_Render')->newInstance();
        $getTranslationMethod = $render->mockMethod('getTranslation')->set(function ($key, $params) use ($phrase) {
            $this->assertSame($phrase, $key);
            $this->assertSame([], $params);
        });

        $i18n_phrase->translate($render);
        $this->assertSame(1, $getTranslationMethod->getCallCount());
    }
}
