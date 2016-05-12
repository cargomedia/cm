<?php

class CMTest_Exporter extends \SebastianBergmann\Exporter\Exporter {

    public function shortenedExport($value) {
        if ($value instanceof CM_Model_Abstract) {
            $idString = $value->hasId() ? $value->getId() : 'No Id'; 
            return get_class($value) . "({$idString})";
        }
        return parent::shortenedExport($value);
    }

    public function recursiveExport(&$value, $indentation, $processed = null) {
        if ($value instanceof CM_Model_Abstract) {
            return $this->shortenedExport($value);
        }
        return parent::recursiveExport($value, $indentation, $processed);
    }

}
