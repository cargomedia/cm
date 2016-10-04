<?php

class CM_Log_Encoder_MongoDb implements CM_Log_Encoder_Interface {

    public function encode(array $entry) {
        array_walk_recursive($entry, function (&$value) {
            $encoded = $value;
            if ($value instanceof DateTime) {
                $encoded = new MongoDate($value->getTimestamp());
            } elseif ($value instanceof CM_Model_Abstract) {
                $encoded = '[' . get_class($value) . ':' . $value->getId() . ']';
            } elseif ($value instanceof JsonSerializable) {
                $encoded = $value->jsonSerialize();
                if (is_array($encoded)) {
                    $encoded = $this->encode($encoded);
                }
            } elseif (is_object($value)) {
                $encoded = '[' . get_class($value) . ']';
            }
            $value = $encoded;
        });
        return $entry;
    }
}
