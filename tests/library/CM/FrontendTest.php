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
    public function testTreeCollapseNoCurrent() {
        $frontend = new CM_Frontend(new CM_Render());

        $view = $this->getMockBuilder('CM_View_Abstract')->getMockForAbstractClass();
        /** @var CM_View_Abstract $view */

        $viewResponse1 = new CM_ViewResponse($view);
        $frontend->treeExpand($viewResponse1);

        $frontend->treeCollapse();
        $frontend->treeCollapse();
    }
}
