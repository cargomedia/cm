<?php

class CM_Logging_Record {

    /** @var int */
    private $_level;

    /** @var string */
    private $_message;

    /** @var Exception|CM_ExceptionHandling_SerializableException|null */
    private $_exception;

    /** @var CM_Logging_Context */
    private $_context;

}
