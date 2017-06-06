<?php

class CM_Serializer_ArrayConvertible implements CM_Serializer_SerializerInterface {

    public function serialize($data) {
        return CM_Params::encode($data, true);
    }

    public function unserialize($data) {
        return CM_Params::decode($data, true);
    }

}
