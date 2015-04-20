<?php

interface CM_Class_TypedInterface {

    /**
     * @return int
     */
    function getType();

    /**
     * @return int
     */
    static function getTypeStatic();
}
