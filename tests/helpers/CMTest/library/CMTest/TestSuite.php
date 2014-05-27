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
    }
}
