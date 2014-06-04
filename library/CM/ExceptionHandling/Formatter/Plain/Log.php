<?php

class CM_ExceptionHandling_Formatter_Plain_Log extends CM_ExceptionHandling_Formatter_Plain {

    public function getMetaInfo(CM_ExceptionHandling_SerializableException $exception) {
        return '';
    }
}
