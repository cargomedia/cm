<?php

interface CM_ArrayConvertible {

    /**
     * Object representation as array
     *
     * @return array
     */
    public function toArray();

    /**
     * Return object from array-representation
     *
     * @param array $array
     * @return object
     * @throws CM_ArrayConvertible_MalformedArrayException
     */
    public static function fromArray(array $array);
}
