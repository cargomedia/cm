<?php

class CM_Cli_Runnable_AbstractTest extends CMTest_TestCase {

    public function testGetServiceManager() {
        /** @var CM_Cli_Runnable_Abstract|\Mocka\AbstractClassTrait $runnable */
        $runnable = $this->mockObject('CM_Cli_Runnable_Abstract');

        $this->assertInstanceOf('CM_Service_Manager', $runnable->getServiceManager());
    }

}
