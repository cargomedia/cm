<?php

class CM_Frontend_TreeNodeTest extends CMTest_TestCase {

    public function testGetClosest() {
        $layout = $this->getMockBuilder('CM_Layout_Abstract')->getMockForAbstractClass();
        /** @var CM_Layout_Abstract $layout */
        $page = $this->getMockBuilder('CM_Page_Abstract')->getMockForAbstractClass();
        /** @var CM_Page_Abstract $page */
        $component = $this->getMockBuilder('CM_Component_Abstract')->getMockForAbstractClass();
        /** @var CM_Component_Abstract $component */

        $node1 = new CM_Frontend_TreeNode(new CM_ViewResponse($layout));

        $node2 = new CM_Frontend_TreeNode(new CM_ViewResponse($page));
        $node2->setParent($node1);

        $node3 = new CM_Frontend_TreeNode(new CM_ViewResponse($component));
        $node3->setParent($node2);

        $node4 = new CM_Frontend_TreeNode(new CM_ViewResponse($component));
        $node4->setParent($node3);

        $this->assertSame($node4, $node4->getClosest('CM_View_Abstract'));
        $this->assertSame($node4, $node4->getClosest('CM_Component_Abstract'));
        $this->assertSame($node2, $node4->getClosest('CM_Page_Abstract'));
        $this->assertSame($node1, $node4->getClosest('CM_Layout_Abstract'));
        $this->assertSame(null, $node4->getClosest('CM_Form_Abstract'));
    }
}
