<?php

require_once CM_Util::getNamespacePath('CM') . 'library/CM/SmartyPlugins/function.link.php';

class smarty_function_linkTest extends CMTest_TestCase {

	/**
	 * @var Smarty_Internal_Template
	 */
	private $_template;

	public function setUp() {
		$smarty = new Smarty();
		$render = new CM_Render($this->_getSite());
		$this->_template = $smarty->createTemplate('string:');
		$this->_template->assignGlobal('render', $render);
	}

	public function testWithLabel() {
		$this->_assertSame('<a href="javascript:;" class="link hasLabel"><span class="label">Label</span></a>', array('label' => 'Label'));
		$this->_assertSame('<a href="javascript:;" class="link testClass hasLabel"><span class="label">Label</span></a>', array('label' => 'Label', 'class' => 'testClass'));
		$this->_assertSame('<a href="javascript:;" class="link hasLabel hasIcon"><span class="icon-delete"></span><span class="label">Label</span></a>', array('label' => 'Label', 'icon' => 'delete'));
		$this->_assertSame('<a href="javascript:;" class="link hasLabel" title="Title"><span class="label">Label</span></a>', array('label' => 'Label', 'title' => 'Title'));
		$this->_assertSame('<a href="javascript:;" class="link testClass hasLabel hasIcon" title="Title"><span class="icon-delete"></span><span class="label">Label</span></a>', array('label' => 'Label', 'class' => 'testClass', 'icon' => 'delete', 'title' => 'Title'));
		$this->getMockForAbstractClass('CM_Page_Abstract', array(), 'CM_Page_Test', false);
		$this->_assertSame('<a href="/test" class="link hasLabel"><span class="label">Label</span></a>', array('page' => 'CM_Page_Test', 'label' => 'Label'));
		$this->_assertSame('<a href="/test" class="link testClass hasLabel"><span class="label">Label</span></a>', array('page' => 'CM_Page_Test', 'label' => 'Label', 'class' => 'testClass'));
		$this->_assertSame('<a href="/test" class="link hasLabel hasIcon"><span class="icon-delete"></span><span class="label">Label</span></a>', array('page' => 'CM_Page_Test', 'label' => 'Label', 'icon' => 'delete'));
		$this->_assertSame('<a href="/test" class="link hasLabel" title="Title"><span class="label">Label</span></a>', array('page' => 'CM_Page_Test', 'label' => 'Label', 'title' => 'Title'));
		$this->_assertSame('<a href="/test" class="link testClass hasLabel hasIcon" title="Title"><span class="icon-delete"></span><span class="label">Label</span></a>', array('page' => 'CM_Page_Test', 'label' => 'Label', 'class' => 'testClass', 'icon' => 'delete', 'title' => 'Title'));
	}

	public function testWithoutLabel() {
		$this->_assertSame('<a href="javascript:;" class="link"></a>', array());
		$this->_assertSame('<a href="javascript:;" class="link testClass"></a>', array('class' => 'testClass'));
		$this->_assertSame('<a href="javascript:;" class="link hasIcon"><span class="icon-delete"></span></a>', array('icon' => 'delete'));
		$this->_assertSame('<a href="javascript:;" class="link" title="Title"></a>', array('title' => 'Title'));
		$this->_assertSame('<a href="javascript:;" class="link testClass hasIcon" title="Title"><span class="icon-delete"></span></a>', array('class' => 'testClass', 'icon' => 'delete', 'title' => 'Title'));
		$this->getMockForAbstractClass('CM_Page_Abstract', array(), 'CM_Page_Test', false);
		$this->_assertSame('<a href="/test" class="link hasLabel"><span class="label">/test</span></a>', array('page' => 'CM_Page_Test'));
		$this->_assertSame('<a href="/test" class="link testClass hasLabel"><span class="label">/test</span></a>', array('page' => 'CM_Page_Test', 'class' => 'testClass'));
		$this->_assertSame('<a href="/test" class="link hasIcon"><span class="icon-delete"></span></a>', array('page' => 'CM_Page_Test', 'icon' => 'delete'));
		$this->_assertSame('<a href="/test" class="link" title="Title"></a>', array('page' => 'CM_Page_Test', 'title' => 'Title'));
		$this->_assertSame('<a href="/test" class="link testClass hasIcon" title="Title"><span class="icon-delete"></span></a>', array('page' => 'CM_Page_Test', 'class' => 'testClass', 'icon' => 'delete', 'title' => 'Title'));
	}

	/**
	 * @param string $expected
	 * @param array  $params
	 */
	private function _assertSame($expected, array $params) {
		$this->assertSame($expected, smarty_function_link($params, $this->_template));
	}
}
