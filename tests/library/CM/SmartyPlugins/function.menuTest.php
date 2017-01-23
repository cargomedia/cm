<?php

require_once CM_Util::getModulePath('CM') . 'library/CM/SmartyPlugins/function.menu.php';

class smarty_function_menuTest extends CMTest_TestCase {

    /**
     * @var Smarty_Internal_Template
     */
    private $_template;

    /** @var array */
    private $_menuData;

    public function setUp() {
        $smarty = new Smarty();
        $render = $this->getDefaultRender();
        $this->_template = $smarty->createTemplate('string:');
        $this->_template->assignGlobal('render', $render);

        $this->_menuData = [
            [
                'label'  => 'Label 1',
                'page'   => 'CM_Page_Example',
                'params' => ['foo' => 1],
            ],
            [
                'label'  => 'Label 2',
                'page'   => 'CM_Page_Example',
                'params' => ['foo' => 2],
            ],
        ];
    }

    public function testRender() {
        $html = new CM_Dom_NodeList(smarty_function_menu([
            'data'       => $this->_menuData,
            'activePage' => new CM_Page_Example(['foo' => 1]),
        ], $this->_template));

        $this->assertContains('menu', $html->getAttribute('class'));
        $this->assertCount(2, $html->find('> li'));
    }
}
