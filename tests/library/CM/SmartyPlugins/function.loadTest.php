<?php

require_once CM_Util::getModulePath('CM') . 'library/CM/SmartyPlugins/function.load.php';

class smarty_function_loadTest extends CMTest_TestCase {

    /**
     * @var Smarty_Internal_Template
     */
    private $_template;

    public function setUp() {
        $smarty = new Smarty();
        $render = new CM_Frontend_Render();
        $this->_template = $smarty->createTemplate('string:');
        $this->_template->assignGlobal('render', $render);
    }

    public function testRender() {
        $paramsFail = array(
            'file' => 'badFileName.tpl',
        );

        $exception = $this->catchException(function () use ($paramsFail) {
            smarty_function_load($paramsFail, $this->_template);
        });

        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Can\'t find template', $exception->getMessage());
        /** @var CM_Exception_Invalid $exception */
        $this->assertSame(
            [
                'template' => 'badFileName.tpl',
                'modules'  => 'CM',
                'themes'   => 'default',
            ],
            $exception->getMetaInfo()
        );

        $paramsPass = array(
            'file'   => 'badFileName.tpl',
            'needed' => false,
        );

        $this->assertSame('', smarty_function_load($paramsPass, $this->_template));
    }
}
