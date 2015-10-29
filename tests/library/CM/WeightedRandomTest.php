<?php

class CM_WeightedRandomTest extends CMTest_TestCase {

    /**
     * @expectedException CM_Exception
     */
    public function testConstructDifferentLengths() {
        new CM_WeightedRandom(['1', '2', '3'], [0, 10]);
    }

    /**
     * @expectedException CM_Exception
     */
    public function testConstructEmpty() {
        new CM_WeightedRandom([], []);
    }

    public function testLookup() {
        $weightedRandom = new CM_WeightedRandom(
            ['1', '2', '3'],
            [0, 10, 100]
        );

        for ($i = 0; $i < 10; $i++) {
            $this->assertContains($weightedRandom->lookup(), ['2', '3']);
        }
    }

    public function testLookupAssociativeArray() {
        $weightedRandom = new CM_WeightedRandom(
            [0 => '1', 1 => '2', 5 => '3'],
            [0 => 0, 1 => 10, 10 => 100]
        );
        $this->assertContains($weightedRandom->lookup(), ['2', '3']);
    }

}
