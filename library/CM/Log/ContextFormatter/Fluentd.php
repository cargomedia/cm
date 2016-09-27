<?php

class CM_Log_ContextFormatter_Fluentd extends CM_Log_ContextFormatter_Cargomedia {

    /**
     * @param array $extra
     * @return array
     */
    protected function _encodeExtra(array $extra) {
        array_walk_recursive($extra, function (&$value) {
            $encoded = $value;
            if ($value instanceof DateTime) {
                $encoded = $value->format('c');
            } elseif (is_object($value)) {
                $encoded = '[';
                $encoded .= get_class($value);
                if ($value instanceof CM_Model_Abstract) {
                    $encoded .= ':' . $value->getId();
                }
                $encoded .= ']';
            }
            $value = $encoded;
        });
        return $extra;
    }
}
