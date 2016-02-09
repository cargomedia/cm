<?php

class CM_Bootloader_Testing extends CM_Bootloader {

    public function load() {
        parent::load();
        $this->getExceptionHandler()->setPrintSeverityMin(CM_Exception::ERROR);
    }

    public function getDataPrefix() {
        return 'test_';
    }

    /**
     * @return string
     */
    public function getDirTmp() {
        return DIR_ROOT . 'tests/tmp/tmp/';
    }
}
