<?php

class CM_Action_EmailTest extends CMTest_TestCase {

    public function testGetLabel() {
        $actor = CMTest_TH::createUser();
        $typeEmail = CM_Mail_Welcome::getTypeStatic();
        $action = new CM_Action_Email(CM_Action_Abstract::VIEW, $actor, $typeEmail);
        $this->assertSame('Email View Welcome', $action->getLabel());
    }
}
