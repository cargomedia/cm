<?php

use SebastianBergmann\Comparator;

class CMTest_Comparator_Model extends Comparator\Comparator {

    public function __construct() {
        $this->exporter = new CMTest_Exporter();
    }

    public function accepts($expected, $actual) {
        return ($expected instanceof CM_Model_Abstract) && ($actual instanceof CM_Model_Abstract);
    }

    public function assertEquals($expected, $actual, $delta = 0.0, $canonicalize = false, $ignoreCase = false) {
        /** @var CM_Model_Abstract $expected */
        /** @var CM_Model_Abstract $actual */
        if (!$expected->equals($actual)) {
            $message = "Failed asserting that {$this->exporter->export($actual)} matches expected {$this->exporter->export($expected)}.";
            throw new Comparator\ComparisonFailure($expected, $actual, '', '', false, $message);
        }
    }

}
