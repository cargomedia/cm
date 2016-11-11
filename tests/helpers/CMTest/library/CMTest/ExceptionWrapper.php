<?php

class CMTest_ExceptionWrapper extends PHPUnit_Framework_ExceptionWrapper {

    public function __construct($e) {
        parent::__construct($e);

        if ($e instanceof CM_Exception) {
            $message = $e->getMessage();
            $variableInspector = new CM_Debug_VariableInspector();
            $metaInfo = Functional\map($e->getMetaInfo(), function ($value) use ($variableInspector) {
                return $variableInspector->getDebugInfo($value);
            });
            foreach ($metaInfo as $key => $value) {
                $message .= sprintf("\n - %s: %s", $key, $value);
            }
            $this->message = $message;
        }
    }
}
