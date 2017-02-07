<?php

use SebastianBergmann\Comparator;

class CMTest_Comparator_Comparable extends Comparator\Comparator {

    public function __construct() {
        $this->exporter = new CMTest_Exporter();
    }

    public function accepts($expected, $actual) {
        return ($expected instanceof CM_Comparable) && ($actual instanceof CM_Comparable);
    }

    public function assertEquals($expected, $actual, $delta = 0.0, $canonicalize = false, $ignoreCase = false) {
        /** @var CM_Comparable $expected */
        /** @var CM_Comparable $actual */
        if (!$expected->equals($actual)) {
            $message = "Failed asserting that {$this->exporter->export($actual)} matches expected {$this->exporter->export($expected)}.";
            throw new Comparator\ComparisonFailure($expected, $actual, '', '', false, $message);
        }
    }

}
