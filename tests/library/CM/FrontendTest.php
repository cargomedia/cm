<?php

class CM_FrontendTest extends CMTest_TestCase {

    /**
     * @expectedException CM_Exception_Invalid
     */
    public function testGetTreeRootEmpty() {
        $frontend = new CM_Frontend(new CM_Render());
        $frontend->getTreeRoot();
    }

    /**
     * @expectedException CM_Exception_Invalid
     */
    public function testGetTreeCurrentEmpty() {
        $frontend = new CM_Frontend(new CM_Render());
        $frontend->getTreeCurrent();
    }

    public function testTree() {
        $frontend = new CM_Frontend(new CM_Render());

        $view = $this->getMockBuilder('CM_View_Abstract')->getMockForAbstractClass();
        /** @var CM_View_Abstract $view */

        $viewResponse1 = new CM_ViewResponse($view);
        $frontend->treeExpand($viewResponse1);
        $this->assertSame($viewResponse1, $frontend->getTreeCurrent()->getValue());
        $this->assertSame($viewResponse1, $frontend->getTreeRoot()->getValue());

        $viewResponse2 = new CM_ViewResponse($view);
        $frontend->treeExpand($viewResponse2);
        $this->assertSame($viewResponse2, $frontend->getTreeCurrent()->getValue());
        $this->assertSame($viewResponse1, $frontend->getTreeRoot()->getValue());

        $viewResponse3 = new CM_ViewResponse($view);
        $frontend->treeExpand($viewResponse3);
        $this->assertSame($viewResponse3, $frontend->getTreeCurrent()->getValue());
        $this->assertSame($viewResponse1, $frontend->getTreeRoot()->getValue());

        $frontend->treeCollapse();
        $this->assertSame($viewResponse2, $frontend->getTreeCurrent()->getValue());
        $this->assertSame($viewResponse1, $frontend->getTreeRoot()->getValue());

        $frontend->treeCollapse();
        $this->assertSame($viewResponse1, $frontend->getTreeCurrent()->getValue());
        $this->assertSame($viewResponse1, $frontend->getTreeRoot()->getValue());

        $viewResponse4 = new CM_ViewResponse($view);
        $frontend->treeExpand($viewResponse4);
        $this->assertSame($viewResponse4, $frontend->getTreeCurrent()->getValue());
        $this->assertSame($viewResponse1, $frontend->getTreeRoot()->getValue());
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage No current tree node set
     */
    public function testTreeCollapseCollapsedRoot() {
        $frontend = new CM_Frontend(new CM_Render());
        $view = $this->getMockBuilder('CM_View_Abstract')->getMockForAbstractClass();
        /** @var CM_View_Abstract $view */

        $frontend->treeExpand(new CM_ViewResponse($view));

        $frontend->treeCollapse();
        $frontend->treeCollapse();
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage No current tree node set
     */
    public function testTreeExpandCollapsedRoot() {
        $frontend = new CM_Frontend(new CM_Render());
        $view = $this->getMockBuilder('CM_View_Abstract')->getMockForAbstractClass();
        /** @var CM_View_Abstract $view */

        $frontend->treeExpand(new CM_ViewResponse($view));

        $frontend->treeCollapse();
        $frontend->treeExpand(new CM_ViewResponse($view));
    }

    public function testGetClosest() {
        $layout = $this->getMockBuilder('CM_Layout_Abstract')->getMockForAbstractClass();
        /** @var CM_Layout_Abstract $layout */
        $page = $this->getMockBuilder('CM_Page_Abstract')->getMockForAbstractClass();
        /** @var CM_Page_Abstract $page */
        $component = $this->getMockBuilder('CM_Component_Abstract')->getMockForAbstractClass();
        /** @var CM_Component_Abstract $component */

        $frontend = new CM_Frontend(new CM_Render());

        $viewResponse1 = new CM_ViewResponse($layout);
        $frontend->treeExpand($viewResponse1);

        $viewResponse2 = new CM_ViewResponse($page);
        $frontend->treeExpand($viewResponse2);

        $viewResponse3 = new CM_ViewResponse($component);
        $frontend->treeExpand($viewResponse3);

        $viewResponse4 = new CM_ViewResponse($component);
        $frontend->treeExpand($viewResponse4);

        $this->assertSame($viewResponse4, $frontend->getClosestViewResponse('CM_View_Abstract'));
        $this->assertSame($viewResponse4, $frontend->getClosestViewResponse('CM_Component_Abstract'));
        $this->assertSame($viewResponse2, $frontend->getClosestViewResponse('CM_Page_Abstract'));
        $this->assertSame($viewResponse1, $frontend->getClosestViewResponse('CM_Layout_Abstract'));
        $this->assertSame(null, $frontend->getClosestViewResponse('CM_Form_Abstract'));
    }
}
