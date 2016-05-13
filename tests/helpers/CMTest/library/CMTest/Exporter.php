<?php

class CMTest_Exporter extends \SebastianBergmann\Exporter\Exporter {

    public function shortenedExport($value) {
        if ($value instanceof CM_Debug_DebugInfoInterface) {
            return (new CM_Debug_VariableInspector())->getDebugInfo($value);
        }
        return parent::shortenedExport($value);
    }

    public function recursiveExport(&$value, $indentation, $processed = null) {
        if ($value instanceof CM_Debug_DebugInfoInterface) {
            return $this->shortenedExport($value);
        }
        return parent::recursiveExport($value, $indentation, $processed);
    }

}
