<?php

class CM_Debug_VariableInspector {

    /**
     * @param mixed      $variable
     * @param array|null $options
     * @return string
     */
    public function getDebugInfo($variable, array $options = null) {
        $options = array_merge([
            'recursive' => false,
            'lengthMax' => null,
        ], (array) $options);
        $recursive = (bool) $options['recursive'];
        $lengthMax = (null === $options['lengthMax']) ? null : (int) $options['lengthMax'];

        if (is_array($variable)) {
            if ($recursive) {
                $elementList = Functional\map($variable, function ($value, $key) use ($options) {
                    return $this->getDebugInfo($key, $options) . ' => ' . $this->getDebugInfo($value, $options);
                });
                return '[' . implode(', ', $elementList) . ']';
            } else {
                return '[]';
            }
        }
        if (is_object($variable)) {
            if ($variable instanceof stdClass) {
                return 'object';
            }
            if ($variable instanceof CM_Debug_DebugInfoInterface) {
                return $variable->getDebugInfo();
            }
            return get_class($variable);
        }
        if (is_string($variable)) {
            if (null !== $lengthMax && strlen($variable) > $lengthMax) {
                $variable = substr($variable, 0, $lengthMax) . '...';
            }
            return "'" . $variable . "'";
        }
        if (is_bool($variable) || is_numeric($variable)) {
            return var_export($variable, true);
        }
        return gettype($variable);
    }
}
