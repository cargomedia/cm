<?php

class CMTest_ExceptionWrapper extends PHPUnit_Framework_ExceptionWrapper {

    public function __construct($e) {
        parent::__construct($e);

        if ($e instanceof CM_Exception) {
            $message = $e->getMessage();
            foreach ($e->getMetaInfo() as $key => $value) {
                $message .= sprintf("\n - %s: %s", $key, $value);
            }
            $this->message = $message;
        }
    }
}
