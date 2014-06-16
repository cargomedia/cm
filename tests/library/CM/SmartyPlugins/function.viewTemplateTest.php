<?php

class smarty_function_viewTemplateTest extends CMTest_TestCase {

    public function testRender() {
        /** @var CM_Component_Abstract $component */
        $component = $this->mockObject('CM_Component_Abstract');
        $viewResponse = new CM_Frontend_ViewResponse($component);

        $render = $this->mockObject('CM_Frontend_Render');
        $render->mockMethod('fetchViewTemplate')->set(function ($view, $templateName, $data) use ($component) {
            $this->assertSame($component, $view);
            $this->assertSame('jar', $templateName);
            $this->assertArrayContains(['bar' => 'bar', 'foo' => 'foo'], $data);
        });
        /** @var CM_Frontend_Render $render */
        $render->getGlobalResponse()->treeExpand($viewResponse);
        $render->parseTemplateContent('{viewTemplate file="jar" bar="bar"}', ['foo' => 'foo']);
    }
}
