<?php

class smarty_function_viewTemplateTest extends CMTest_TestCase {

    public function testRender() {
        /** @var CM_Page_Abstract $page */
        $page = $this->mockObject('CM_Page_Abstract');
        $pageViewResponse = new CM_Frontend_ViewResponse($page);

        /** @var CM_Component_Abstract $component */
        $component = $this->mockObject('CM_Component_Abstract');
        $componentViewResponse = new CM_Frontend_ViewResponse($component);

        $render = $this->mockObject('CM_Frontend_Render');
        $method = $render->mockMethod('fetchViewTemplate')
            ->at(0, function ($view, $templateName, $data) use ($component) {
                $this->assertSame($component, $view);
                $this->assertSame('jar', $templateName);
                $this->assertArrayContains(['bar' => 'bar', 'foo' => 'foo'], $data);
            })
            ->at(1, function ($view, $templateName, $data) use ($page) {
                $this->assertSame($page, $view);
                $this->assertSame('foo', $templateName);
            });
        /** @var CM_Frontend_Render $render */
        $render->getGlobalResponse()->treeExpand($pageViewResponse);
        $render->getGlobalResponse()->treeExpand($componentViewResponse);

        $render->parseTemplateContent('{viewTemplate name="jar" bar="bar"}', ['foo' => 'foo']);
        $render->parseTemplateContent('{viewTemplate view="CM_Page_Abstract" name="foo"}');

        $this->assertSame(2, $method->getCallCount());
    }
}
