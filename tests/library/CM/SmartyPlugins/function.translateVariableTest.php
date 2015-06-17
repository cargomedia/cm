<?php

require_once CM_Util::getModulePath('CM') . 'library/CM/SmartyPlugins/prefilter.translate.php';

class smarty_function_translateVariableTest extends CMTest_TestCase {

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Passed params will be ignored as you provided CM_I18n_Phrase object
     */
    public function testTranslateObject() {
        /** @var CM_Frontend_Render|\Mocka\AbstractClassTrait $render */
        $render = $this->mockClass('CM_Frontend_Render')->newInstance();
        $getTranslationMethod = $render->mockMethod('getTranslation')->set(function ($key, $params) {
            $this->assertSame('Bar value is {$bar}', $key);
            $this->assertSame(['bar' => 3], $params);
        });
        /** @var CM_Frontend_Render $render */

        $object = new CM_I18n_Phrase('Bar value is {$bar}', ['bar' => 3]);
        $render->parseTemplateContent('{translateVariable key=$foo}', ['foo' => $object]);
        $this->assertSame(1, $getTranslationMethod->getCallCount());

        $render->parseTemplateContent('{translateVariable key=$foo more=one}', ['foo' => $object]);
        $this->assertSame(0, $getTranslationMethod->getCallCount());
    }
}
