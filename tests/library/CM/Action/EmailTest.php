<?php

class CM_Action_EmailTest extends CMTest_TestCase {

    public function testGetLabel() {
        $actor = CMTest_TH::createUser();
        $typeEmail = CM_Mail_ExampleMailable::getTypeStatic();
        $action = new CM_Action_Email(CM_Action_Abstract::VIEW, $actor, $typeEmail);
        $this->assertSame('Email View Example Mailable', $action->getLabel());
    }

    public function testGetLabel_typeNotConfigured() {
        $actor = CMTest_TH::createUser();
        $typeEmail = mt_rand();
        $action = new CM_Action_Email(CM_Action_Abstract::VIEW, $actor, $typeEmail);
        $this->assertSame('Email View ' . $typeEmail, $action->getLabel());
    }
}
