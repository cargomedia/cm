<?php

require_once CM_Util::getModulePath('CM') . 'library/CM/SmartyPlugins/function.resourceFileContent.php';

class smarty_function_resourceFileContentTest extends CMTest_TestCase {

    public function testRender() {
        $smarty = new Smarty();
        $render = new CM_Frontend_Render();

        $template = $smarty->createTemplate('string:');
        $template->assignGlobal('render', $render);
        $contentExpected = $render->getLayoutFile('resource/img/favicon.svg')->read();
        $contentActual = smarty_function_resourceFileContent(array('path' => 'img/favicon.svg'), $template);
        $this->assertGreaterThan(0, strlen($contentActual));
        $this->assertSame($contentExpected, $contentActual);
    }

}
