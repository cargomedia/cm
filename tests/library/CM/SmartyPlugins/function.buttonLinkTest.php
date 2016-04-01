<?php

require_once CM_Util::getModulePath('CM') . 'library/CM/SmartyPlugins/function.button_link.php';

class smarty_function_button_linkTest extends CMTest_TestCase {

    /** @var CM_Frontend_Render */
    private $_render;

    /** @var Smarty_Internal_Template */
    private $_template;

    public function setUp() {
        $smarty = new Smarty();
        $this->_render = new CM_Frontend_Render();
        $this->_template = $smarty->createTemplate('string:');
        $this->_template->assignGlobal('render', $this->_render);
    }

    public function testRender() {
        $params = array(
            'action' => 'Create',
            'label'  => 'Some text <br /> with html tags',
            'theme'  => 'highlight',
            'class'  => 'button-large',
        );

        $this->_assertContains('value="Some text &lt;br /&gt; with html tags"', array_merge($params, array('isHtmlLabel' => true)));
        $this->_assertContains('<span class="label">Some text <br /> with html tags</span>', array_merge($params, array('isHtmlLabel' => true)));
        $this->_assertContains('value="Some text &lt;br /&gt; with html tags"', array_merge($params, array('isHtmlLabel' => false)));
        $this->_assertContains('<span class="label">Some text &lt;br /&gt; with html tags</span>', array_merge($params, array('isHtmlLabel' => false)));
    }

    public function testRenderHref() {
        $this->assertRegExp(
            '#<a.*href="http://example.com".*>.*Click me.*</a>#',
            smarty_function_button_link(['label' => 'Click me', 'href' => 'http://example.com'], $this->_template)
        );
    }

    public function testRenderPage() {
        $urlPage = $this->_render->getUrlPage('CM_Page_Example', ['foo' => '12']);
        $this->assertRegExp(
            '#<a.*href="' . preg_quote($urlPage) . '".*>.*Click me.*</a>#',
            smarty_function_button_link(['label' => 'Click me', 'page' => 'CM_Page_Example', 'foo' => '12'], $this->_template)
        );
    }

    /**
     * @param string $needle
     * @param array  $params
     */
    private function _assertContains($needle, array $params) {
        $this->assertContains($needle, smarty_function_button_link($params, $this->_template));
    }
}
