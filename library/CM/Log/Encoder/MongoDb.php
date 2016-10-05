<?php

class CM_Log_Encoder_MongoDb implements CM_Log_Encoder_Interface {

    public function encode($value) {
        if ($value instanceof DateTime) {
            return new MongoDate($value->getTimestamp());
        }
        if ($value instanceof CM_Model_Abstract) {
            return '[' . get_class($value) . ':' . $value->getId() . ']';
        }
        if ($value instanceof JsonSerializable) {
            return $this->encode($value->jsonSerialize());
        }
        if (is_array($value)) {
            return array_map([$this, 'encode'], $value);
        }
        if (is_object($value)) {
            return '[' . get_class($value) . ']';
        }
        return $value;
    }
}
