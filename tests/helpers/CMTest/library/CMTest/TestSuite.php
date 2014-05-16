<?php

class CMTest_TestSuite {

    /**
     * @param string $dirTestsData
     */
    public function setDirTestData($dirTestsData) {
        define('DIR_TEST_DATA', $dirTestsData);
    }

    public function bootstrap() {
        CMTest_TH::init();
        register_shutdown_function(array($this, 'cleanup'));
    }

    public function cleanup() {
        CMTest_TH::clearEnv();
        $filesystems = CM_ServiceManager::getInstance()->getFilesystems();
        $filesystems->getData()->deleteByPrefix('/');
        $filesystems->getUserfiles()->deleteByPrefix('/');
    }
}
