<?php

class CM_InputStream_InvalidValueException extends CM_Exception {

    public function __construct($message = null) {
        if (null === $message) {
            $message = 'Invalid value';
        }
        parent::__construct($message);
    }
}
