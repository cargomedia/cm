<?php

class CM_Bootloader_Testing extends CM_Bootloader {

    public function load() {
        parent::load();
        $this->getExceptionHandler()->setPrintSeverityMin(CM_Exception::ERROR);
    }

    public function getDataPrefix() {
        return 'test_';
    }
}
