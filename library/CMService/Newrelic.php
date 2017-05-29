<?php

class CMService_Newrelic extends CM_Class_Abstract {

    /** @var CMService_Newrelic */
    protected static $_instance;

    /** @var bool */
    private $_enabled;

    /** @var string */
    private $_appName;

    /**
     * @param bool   $enabled
     * @param string $appName
     */
    public function __construct($enabled, $appName) {
        $this->_enabled = (bool) $enabled;
        $this->_appName = (string) $appName;
    }

    public function setConfig() {
        if ($this->getEnabled()) {
            newrelic_set_appname($this->_appName);
        }
    }

    /**
     * @param Exception $exception
     */
    public function setNoticeError(Exception $exception) {
        if ($this->getEnabled()) {
            newrelic_notice_error($exception->getMessage(), $exception);
        }
    }

    /**
     * @param string $name
     */
    public function startTransaction($name) {
        if ($this->getEnabled()) {
            $this->endTransaction();
            newrelic_start_transaction($this->_appName);
            $this->setNameTransaction($name);
        }
    }

    /**
     * @param string  $name
     * @param Closure $closure
     * @return mixed
     * @throws Exception
     */
    public function runAsTransaction($name, Closure $closure) {
        $this->startTransaction($name);
        try {
            $returnValue = $closure();
            $this->endTransaction();
            return $returnValue;
        } catch (Exception $ex) {
            $this->endTransaction();
            throw $ex;
        }
    }

    /**
     * @param string $name
     */
    public function setNameTransaction($name) {
        $name = (string) $name;
        if ($this->getEnabled()) {
            newrelic_name_transaction($name);
        }
    }

    public function endTransaction() {
        if ($this->getEnabled()) {
            newrelic_end_transaction();
        }
    }

    public function ignoreTransaction() {
        if ($this->getEnabled()) {
            newrelic_ignore_transaction();
        }
    }

    /**
     * @param bool|null $flag
     */
    public function setBackgroundJob($flag = null) {
        if (null === $flag) {
            $flag = true;
        }
        if ($this->getEnabled()) {
            newrelic_background_job($flag);;
        }
    }

    /**
     * @param string $name
     * @param int    $milliseconds
     */
    public function setCustomMetric($name, $milliseconds) {
        $name = 'Custom/' . (string) $name;
        $milliseconds = (int) $milliseconds;
        if ($this->getEnabled()) {
            newrelic_custom_metric($name, $milliseconds);
        }
    }

    /**
     * @throws CM_Exception_Invalid
     * @return bool
     */
    public function getEnabled() {
        if ($this->_enabled) {
            if (!extension_loaded('newrelic')) {
                throw new CM_Exception_Invalid('Newrelic Extension is not installed.');
            }
            return true;
        }
        return false;
    }

    /**
     * @deprecated
     * @return CMService_Newrelic
     * @throws Exception
     */
    public static function getInstance() {
        return CM_Service_Manager::getInstance()->getNewrelic();
    }
}
