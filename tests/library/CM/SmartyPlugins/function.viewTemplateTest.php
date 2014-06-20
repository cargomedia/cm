<?php

class smarty_function_viewTemplateTest extends CMTest_TestCase {

    public function testRender() {
        /** @var CM_Component_Abstract $component */
        $component = $this->mockObject('CM_Component_Abstract');
        $componentViewResponse = new CM_Frontend_ViewResponse($component);

        /** @var CM_Form_Abstract $form */
        $form = $this->mockObject('CM_Form_Abstract');
        $formViewResponse = new CM_Frontend_ViewResponse($form);

        $render = $this->mockObject('CM_Frontend_Render');
        $method = $render->mockMethod('fetchViewTemplate')
            ->at(0, function ($view, $templateName, $data) use ($form) {
                $this->assertSame($form, $view);
                $this->assertSame('jar', $templateName);
                $this->assertArrayContains(['bar' => 'bar', 'foo' => 'foo'], $data);
            })
            ->at(1, function ($view, $templateName, $data) use ($component) {
                $this->assertSame($component, $view);
                $this->assertSame('foo', $templateName);
            });
        /** @var CM_Frontend_Render $render */
        $render->getGlobalResponse()->treeExpand($componentViewResponse);
        $render->getGlobalResponse()->treeExpand($formViewResponse);

        $render->parseTemplateContent('{viewTemplate file="jar" bar="bar"}', ['foo' => 'foo']);
        $render->parseTemplateContent('{viewTemplate view="CM_Component_Abstract" file="foo"}');

        $this->assertSame(2, $method->getCallCount());
    }
}
