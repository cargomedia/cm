<?php

interface CM_Log_Encoder_Interface {

    /**
     * @param array $entry
     * @return array
     */
    public function encode(array $entry);
}
