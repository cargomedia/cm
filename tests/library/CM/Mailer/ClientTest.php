<?php

class CM_Mailer_ClientTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testConstruct() {
        $client = CM_Service_Manager::getInstance()->getMailer();
        $this->assertInstanceOf('CM_Mailer_Client', $client);
    }
}
