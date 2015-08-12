<?php

class CM_Log_Record {

    /** @var int */
    private $_level;

    /** @var string */
    private $_message;

    /** @var CM_ExceptionHandling_SerializableException|null */
    private $_exception;

    /** @var CM_Log_Context */
    private $_context;

}
