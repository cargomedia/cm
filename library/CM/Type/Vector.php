<?php

interface CM_Type_Vector extends CM_ArrayConvertible {

    /**
     * @return float
     */
    public function getX();

    /**
     * @return float
     */
    public function getY();

    /**
     * @return int
     */
    public static function getSize();
}
