<?php

class CMTest_Comparator_BetterArrayComparator extends \SebastianBergmann\Comparator\ArrayComparator {
    
    public function __construct() {
        $this->exporter = new CMTest_Exporter();
    }
}
