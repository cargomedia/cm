<?php

require_once CM_Util::getModulePath('CM') . 'library/CM/SmartyPlugins/function.img.php';

class smarty_function_imgTest extends CMTest_TestCase {

    public function testRender() {
        $smarty = new Smarty();
        $render = new CM_Frontend_Render();

        $template = $smarty->createTemplate('string:');
        $template->assignGlobal('render', $render);
        $html = '<img src="' . $render->getUrlResource('layout', 'img/foo.png') . '" width="123" />';
        $this->assertSame($html, smarty_function_img(array('path' => 'foo.png', 'width' => 123), $template));
    }

    public function testRenderCrossSite() {
        $smarty = new Smarty();
        $render = new CM_Frontend_Render();

        $siteOther = $this->getMockSite('CM_Site_Abstract', null, ['urlCdn' => 'http://cdn.other.com']);
        $renderOther = new CM_Frontend_Render(new CM_Frontend_Environment($siteOther));

        $template = $smarty->createTemplate('string:');
        $template->assignGlobal('render', $render);
        $this->assertContains($renderOther->getUrlResource('layout', 'img/foo.png'),
            smarty_function_img(array('path' => 'foo.png', 'site' => $siteOther), $template));
    }
}
