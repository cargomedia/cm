<?php

class CMTest_SplittedUnitTest {

    public static function suite($suiteClassName) {
        $matches = [];
        $range = getenv('PHPUNIT_TEST_RANGE') ?: '';
        if (preg_match('/(?P<chunk>\d+)\/(?P<parts>\d+)/', $range, $matches)) {
            $chunk = (int) $matches['chunk'] - 1;
            $parts = isset($matches['parts']) ? (int) $matches['parts'] : null;
        } else {
            throw new CM_Exception_Invalid('PHPUNIT_TEST_RANGE environment variable must be set');
        }

        $facade = new File_Iterator_Facade;
        $files = $facade->getFilesAsArray(
            DIR_ROOT . '/tests',
            'Test.php'
        );
        $filesChunks = array_chunk($files, ceil(count($files) / $parts));
        $suite = new PHPUnit_Framework_TestSuite('CMTest_SplittedUnitTest');
        $suite->addTestFiles(isset($filesChunks[$chunk]) ? $filesChunks[$chunk] : []);
        return $suite;
    }
}
