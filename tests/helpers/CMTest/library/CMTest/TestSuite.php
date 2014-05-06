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
        CM_ServiceManager::getInstance()->getFilesystem('filesystemData')->setup(true);
        CM_ServiceManager::getInstance()->getFilesystem('filesystemUserfiles')->setup(true);
    }
}
