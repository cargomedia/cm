<?php

interface CM_Log_Encoder_Interface {

    /**
     * @param mixed $value
     * @return mixed
     */
    public function encode($value);
}
