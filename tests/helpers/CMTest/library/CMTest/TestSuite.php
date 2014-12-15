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
        $config = new CM_Config_Node();
        $config->extendWithConfig($generator->getConfigClassTypes());
        $config->extendWithConfig($generator->getConfigActionVerbs());
        $config->extendWithConfig(CM_Config::get());
        CM_Config::set($config->export());
    }
}
