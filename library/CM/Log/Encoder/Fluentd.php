<?php

class CM_Log_Encoder_Fluentd implements CM_Log_Encoder_Interface {

    public function encode($value) {
        if ($value instanceof DateTime) {
            return $value->format('c');
        }

        if (is_object($value)) {
            $encoded = '[';
            $encoded .= get_class($value);
            if ($value instanceof CM_Model_Abstract) {
                $encoded .= ':' . $value->getId();
            }
            $encoded .= ']';
            return $encoded;
        }
        
        if (is_array($value)) {
            return array_map([$this, 'encode'], $value);
        }
        
        return $value;
    }
}
