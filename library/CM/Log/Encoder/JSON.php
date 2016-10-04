<?php

class CM_Log_Encoder_JSON implements CM_Log_Encoder_Interface {

    public function encode(array $entry) {
        return CM_Util::jsonEncode($entry);
    }
}
