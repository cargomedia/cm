<?php

class CM_Debug_VariableInspector {

    /**
     * @param mixed      $variable
     * @param array|null $options
     * @return string
     */
    public function getDebugInfo($variable, array $options = null) {
        return CM_Util::varDump($variable, $options);
    }
}
