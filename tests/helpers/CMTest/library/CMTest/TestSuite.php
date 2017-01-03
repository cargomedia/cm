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
        $comparatorFactory = SebastianBergmann\Comparator\Factory::getInstance();
        $this->registerComparators($comparatorFactory);
    }

    /**
     * @param SebastianBergmann\Comparator\Factory $comparatorFactory
     */
    public function registerComparators(SebastianBergmann\Comparator\Factory $comparatorFactory) {
        $comparatorFactory->register(new CMTest_Comparator_Comparable());
        $comparatorFactory->register(new CMTest_Comparator_BetterArrayComparator());
    }

    public function generateInternalConfig() {
        if (isset(CM_Config::get()->CM_Class_Abstract->typesMaxValue)) {
            return;
        }
        $generator = new CM_Config_Generator();
        $config = new CM_Config_Node();
        $config->extendWithConfig($generator->getConfigClassTypes());
        $config->extendWithConfig($generator->getConfigActionVerbs());
        $config->extendWithConfig(CM_Config::get());
        CM_Config::set($config->export());
    }
}
