<?php

class CM_Http_Response_EmailTrackingTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testProcess() {
        $user = $this->getMockUser();
        $mail = new CM_Mail_ExampleMailable($user);

        $site = (new CM_Site_SiteFactory())->getDefaultSite();
        $render = new CM_Frontend_Render(new CM_Frontend_Environment($site));
        $request = new CM_Http_Request_Get($render->getUrlEmailTracking($mail), ['host' => $site->getHost()]);
        $response = CM_Http_Response_EmailTracking::createFromRequest($request, $site, $this->getServiceManager());

        $response->process();

        $actionList = new CM_Paging_Action_User($user, CM_Action_Email::getTypeStatic(), CM_Action_Abstract::getVerbByVerbName(CM_Action_Abstract::VIEW));
        $this->assertSame(1, $actionList->getCount());
    }

    public function testProcessNonexistentUser() {
        $user = $this->getMockUser();
        $mail = new CM_Mail_ExampleMailable($user);

        $site = (new CM_Site_SiteFactory())->getDefaultSite();
        $render = new CM_Frontend_Render(new CM_Frontend_Environment($site));
        $request = new CM_Http_Request_Get($render->getUrlEmailTracking($mail), ['host' => $site->getHost()]);
        $response = CM_Http_Response_EmailTracking::createFromRequest($request, $site, $this->getServiceManager());

        $user->delete();
        try {
            $response->process();
            $this->fail('Expected exception not thrown');
        } catch (CM_Exception_Nonexistent $e) {
            $this->assertSame(CM_Exception::WARN, $e->getSeverity());
        }
    }

    public function testProcessMissingParameter() {
        $site = (new CM_Site_SiteFactory())->getDefaultSite();
        $request = new CM_Http_Request_Get('/emailtracking', ['host' => $site->getHost()]);
        $response = CM_Http_Response_EmailTracking::createFromRequest($request, $site, $this->getServiceManager());

        try {
            $response->process();
            $this->fail('Expected exception not thrown');
        } catch (CM_Exception_InvalidParam $e) {
            $this->assertSame(CM_Exception::WARN, $e->getSeverity());
        }
    }

    /**
     * @param string|null           $email
     * @param CM_Site_Abstract|null $site
     * @return CM_Model_User|\Mocka\AbstractClassTrait
     */
    public function getMockUser($email = null, CM_Site_Abstract $site = null) {
        $email = null === $email ? 'foo@example.com' : $email;
        $site = null === $site ? $this->getMockSite() : $site;
        $mockBuilder = $this->getMockBuilder('CM_Model_User');
        $mockBuilder->setMethods(['getEmail', 'getSite']);
        $mockBuilder->setConstructorArgs([CMTest_TH::createUser()->getId()]);
        $userMock = $mockBuilder->getMock();
        $userMock->expects($this->any())->method('getEmail')->will($this->returnValue($email));
        $userMock->expects($this->any())->method('getSite')->will($this->returnValue($site));
        return $userMock;
    }
}
