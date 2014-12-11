<?php

class CMTest_TestSuite {

    /**
     * @param string $dirTestsData
     */
    public function setDirTestData($dirTestsData) {
        define('DIR_TEST_DATA', $dirTestsData);
    }

    public function bootstrap() {
        $this->generateInternalConfig();
        CMTest_TH::init();
    }

    public function generateInternalConfig() {
        $generator = new CM_Config_Generator();
        $internalConfig = $generator->getConfigClassTypes()->extendWithConfig($generator->getConfigActionVerbs());
        $originalConfig = CM_Config::get();
        $mergedConfig = $internalConfig->extendWithConfig($originalConfig);
        $config = $mergedConfig->export();
        CM_Config::set($config);
    }
}
