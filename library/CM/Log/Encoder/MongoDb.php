<?php

class CM_Log_Encoder_MongoDb {

    /**
     * @param mixed $value
     * @return mixed
     */
    public function encode($value) {
        if ($value instanceof DateTime) {
            return new \MongoDB\BSON\UTCDateTime($value->getTimestamp() * 1000);
        }
        if ($value instanceof CM_Model_Abstract) {
            return [
                'class' => get_class($value),
                'id'    => $value->getId(),
            ];
        }
        if ($value instanceof JsonSerializable) {
            return $this->encode($value->jsonSerialize());
        }
        if (is_array($value)) {
            return array_map([$this, 'encode'], $value);
        }
        if (is_object($value)) {
            return [
                'class' => get_class($value),
            ];
        }
        return $value;
    }
}
