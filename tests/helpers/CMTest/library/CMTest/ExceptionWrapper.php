<?php

class CMTest_ExceptionWrapper extends PHPUnit_Framework_ExceptionWrapper {

    public function __construct(CM_Exception $e) {
        parent::__construct($e);
        $formatter = new CM_ExceptionHandling_Formatter_Plain();
        $serializedException = new CM_ExceptionHandling_SerializableException($e);
        $this->message = get_class($e) . ': ' . $e->getMessage() . PHP_EOL . $formatter->getMetaInfo($serializedException);
    }
}
