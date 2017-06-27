<?php

require_once CM_Util::getModulePath('CM') . 'library/CM/SmartyPlugins/function.img.php';

class smarty_function_imgTest extends CMTest_TestCase {

    public function testRender() {
        $smarty = new Smarty();
        $render = new CM_Frontend_Render();

        $template = $smarty->createTemplate('string:');
        $template->assignGlobal('render', $render);
        $html = '<img src="' . $render->getUrlResource('layout', 'img/foo.png') . '" width="123" />';
        $this->assertSame($html, smarty_function_img(['path' => 'foo.png', 'width' => 123], $template));
    }

    public function testRenderCrossSite() {
        $smarty = new Smarty();
        $render = new CM_Frontend_Render();

        $siteOther = $this->getMockSite('CM_Site_Abstract', ['urlCdn' => 'http://cdn.other.com']);
        $renderOther = new CM_Frontend_Render(new CM_Frontend_Environment($siteOther));

        $template = $smarty->createTemplate('string:');
        $template->assignGlobal('render', $render);
        $this->assertContains($renderOther->getUrlResource('layout', 'img/foo.png'),
            smarty_function_img(['path' => 'foo.png', 'site' => $siteOther], $template));
    }

    public function testAbsolutePath() {
        $smarty = new Smarty();
        $render = new CM_Frontend_Render();

        $template = $smarty->createTemplate('string:');
        $template->assignGlobal('render', $render);
        $html = '<img src="https://example.com/img/foo.png" height="123" />';
        $this->assertSame($html, smarty_function_img(['path' => 'https://example.com/img/foo.png', 'height' => 123], $template));
    }

    public function testBackgroundImage() {
        $smarty = new Smarty();
        $render = new CM_Frontend_Render();

        $template = $smarty->createTemplate('string:');
        $template->assignGlobal('render', $render);
        $html = '<img src="https://example.com/img/foo.png" style="background-image: url(https://example.com/img/foo.gif)" class="background-cover" width="456" height="123" />';
        $this->assertSame($html, smarty_function_img([
            'path'             => 'https://example.com/img/foo.png',
            'width'            => 456,
            'height'           => 123,
            'background-image' => 'https://example.com/img/foo.gif'
        ], $template));
    }

    public function testBackgroundImageData() {
        $smarty = new Smarty();
        $render = new CM_Frontend_Render();

        $template = $smarty->createTemplate('string:');
        $template->assignGlobal('render', $render);
        $html = '<img src="https://example.com/img/foo.png" style="background-image: url(data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7)" class="background-cover" width="456" height="123" />';
        $this->assertSame($html, smarty_function_img([
            'path'             => 'https://example.com/img/foo.png',
            'width'            => 456,
            'height'           => 123,
            'background-image' => 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'
        ], $template));
    }
}
