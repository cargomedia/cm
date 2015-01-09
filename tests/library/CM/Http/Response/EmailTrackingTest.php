<?php

class CM_Http_Response_EmailTrackingTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testProcess() {
        $user = CMTest_TH::createUser();
        $mail = new CM_Mail_Welcome($user);

        $site = CM_Site_Abstract::factory();
        $headers = array('host' => $site->getHost());
        $render = new CM_Frontend_Render(new CM_Frontend_Environment($site));
        $request = new CM_Http_Request_Get($render->getUrlEmailTracking($mail), $headers);
        $response = new CM_Http_Response_EmailTracking($request);

        $response->process();

        $actionList = new CM_Paging_Action_User($user, CM_Action_Email::getTypeStatic(), CM_Action_Abstract::getVerbByVerbName(CM_Action_Abstract::VIEW));
        $this->assertSame(1, $actionList->getCount());
    }
}
