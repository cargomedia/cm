<?php

require_once CM_Util::getNamespacePath('CM') . 'library/CM/SmartyPlugins/function.resourceUrl.php';

class smarty_function_resourceUrlTest extends CMTest_TestCase {

	public function testRender() {
		$smarty = new Smarty();
		$render = new CM_Render();
		$template = $smarty->createTemplate('string:');
		$template->assignGlobal('render', $render);
		$this->assertSame($render->getUrlResource('layout', 'foo'), smarty_function_resourceUrl(array('path' => 'foo', 'type' => 'layout'), $template));
		$this->assertSame($render->getUrlStatic('foo'), smarty_function_resourceUrl(array('path' => 'foo', 'type' => 'static'), $template));
	}
}
