<?php

require_once CM_Util::getModulePath('CM') . 'library/CM/SmartyPlugins/function.resourceUrl.php';

class smarty_function_resourceUrlTest extends CMTest_TestCase {

    public function testRender() {
        $smarty = new Smarty();
        $render = new CM_Frontend_Render();

        /** @var Smarty_Internal_Template $template */
        $template = $smarty->createTemplate('string:');
        $template->assignGlobal('render', $render);
        $this->assertSame($render->getUrlResource('layout', 'foo'),
            smarty_function_resourceUrl(array('path' => 'foo', 'type' => 'layout'), $template));
        $this->assertSame($render->getUrlStatic('foo'),
            smarty_function_resourceUrl(array('path' => 'foo', 'type' => 'static'), $template));
    }

    public function testRenderCrossSite() {
        $smarty = new Smarty();
        $render = new CM_Frontend_Render();

        $siteOther = $this->getMockSite('CM_Site_Abstract', ['urlCdn' => 'http://cdn.other.com']);
        $renderOther = new CM_Frontend_Render(new CM_Frontend_Environment($siteOther));

        /** @var Smarty_Internal_Template $template */
        $template = $smarty->createTemplate('string:');
        $template->assignGlobal('render', $render);
        $this->assertSame(sprintf('http://cdn.other.com/layout/%s/%s/foo', $siteOther->getId(), CM_App::getInstance()->getDeployVersion()),
            smarty_function_resourceUrl(['path' => 'foo', 'type' => 'layout', 'site' => $siteOther], $template));
        $this->assertSame(sprintf('http://cdn.other.com/static/foo?%s', CM_App::getInstance()->getDeployVersion()),
            smarty_function_resourceUrl(['path' => 'foo', 'type' => 'static', 'site' => $siteOther], $template));

        $this->assertSame(sprintf('http://www.example.com/layout/%s/%s/foo', $siteOther->getId(), CM_App::getInstance()->getDeployVersion()),
            smarty_function_resourceUrl(['path' => 'foo', 'type' => 'layout', 'site' => $siteOther, 'sameOrigin' => true], $template));
        $this->assertSame(sprintf('http://www.example.com/static/foo?%s', CM_App::getInstance()->getDeployVersion()),
            smarty_function_resourceUrl(['path' => 'foo', 'type' => 'static', 'site' => $siteOther, 'sameOrigin' => true], $template));
    }
}
