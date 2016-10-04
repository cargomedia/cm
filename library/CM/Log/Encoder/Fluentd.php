<?php

class CM_Log_Encoder_Fluentd implements CM_Log_Encoder_Interface {

    public function encode(array $entry) {
        array_walk_recursive($entry, function (&$value) {
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
        return $entry;
    }
}
