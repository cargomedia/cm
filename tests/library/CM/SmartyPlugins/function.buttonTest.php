<?php

require_once CM_Util::getNamespacePath('CM') . 'library/CM/SmartyPlugins/function.button.php';

class smarty_function_buttonTest extends CMTest_TestCase {

    /**
     * @var Smarty_Internal_Template
     */
    private $_template;

    public function setUp() {
        $smarty = new Smarty();
        $render = new CM_Frontend_Render();

        $formMock = $this->getMockForAbstractClass('CM_Form_Abstract', array(), '', true, true, true, array('getAction'));
        $actionMock = $this->getMockForAbstractClass('CM_FormAction_Abstract', array($formMock), '', true, true, true, array('getName'));
        $actionMock->expects($this->any())->method('getName')->will($this->returnValue('Create'));
        $formMock->expects($this->any())->method('getAction')->will($this->returnValue($actionMock));
        /** @var CM_Form_Abstract $formMock */
        $render->getFrontend()->treeExpand(new CM_ViewResponse($formMock));

        $this->_template = $smarty->createTemplate('string:');
        $this->_template->assignGlobal('render', $render);
    }

    public function testRender() {
        $params = array(
            'action'      => 'Create',
            'label'       => 'Some text <br /> with html tags',
            'theme'       => 'highlight',
            'class'       => 'button-large',
        );

        $this->_assertContains('value="Some text <br /> with html tags"', array_merge($params, array('isHtmlLabel' => true)));
        $this->_assertContains('<span class="label">Some text <br /> with html tags</span>', array_merge($params, array('isHtmlLabel' => true)));
        $this->_assertContains('value="Some text &lt;br /&gt; with html tags"', array_merge($params, array('isHtmlLabel' => false)));
        $this->_assertContains('<span class="label">Some text &lt;br /&gt; with html tags</span>', array_merge($params, array('isHtmlLabel' => false)));
    }

    /**
     * @param string $needle
     * @param array  $params
     */
    private function _assertContains($needle, array $params) {
        $this->assertContains($needle, smarty_function_button($params, $this->_template));
    }
}
