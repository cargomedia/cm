<?php

interface CM_Class_TypeInterface {

    /**
     * @return int
     */
    function getType();

    /**
     * @return int
     */
    static function getTypeStatic();
}
