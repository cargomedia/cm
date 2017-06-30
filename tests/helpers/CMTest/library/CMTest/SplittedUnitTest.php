<?php

class CMTest_SplittedUnitTest {

    public static function suite($suiteClassName) {
        $matches = [];
        $range = getenv('PHPUNIT_TEST_RANGE') ?: '';
        if (preg_match('/(?P<chunk>\d+)\/(?P<parts>\d+)/', $range, $matches)) {
            $chunk = (int) $matches['chunk'] - 1;
            $parts = (int) $matches['parts'];
        } else {
            throw new CM_Exception_Invalid('PHPUNIT_TEST_RANGE environment variable must be set');
        }
        if ($chunk < 0 || $parts == 0 || $chunk >= $parts) {
            throw new CM_Exception_Invalid('invalid PHPUNIT_TEST_RANGE value');
        }

        $facade = new File_Iterator_Facade();
        $files = $facade->getFilesAsArray(
            DIR_ROOT . '/tests',
            'Test.php'
        );
        $filesChunks = array_chunk($files, ceil(count($files) / $parts));
        $suite = new PHPUnit_Framework_TestSuite('SplittedUnitTest');
        $suite->addTestFiles(isset($filesChunks[$chunk]) ? $filesChunks[$chunk] : []);
        return $suite;
    }
}
