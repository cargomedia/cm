<?php

class CM_Process_ForkHandler {

    /** @var int */
    private $_pid;

    /** @var Closure */
    private $_workload;

    /** @var int */
    private $_identifier;

    /**
     * @param int      $pid
     * @param Closure  $workload
     * @param int|null $identifier
     */
    public function __construct($pid, Closure $workload, $identifier = null) {
        $this->_pid = (int) $pid;
        $this->_workload = $workload;
        if (null !== $identifier) {
            $identifier = (int) $identifier;
        }
        $this->_identifier = $identifier;
    }

    /**
     * @return int
     */
    public function getPid() {
        return $this->_pid;
    }

    /**
     * @return Closure
     */
    public function getWorkload() {
        return $this->_workload;
    }

    /**
     * @throws CM_Exception
     * @return int
     */
    public function getIdentifier() {
        if (null === $this->_identifier) {
            throw new CM_Exception('Fork-handler has no identifier');
        }
        return $this->_identifier;
    }

    /**
     * @throws Exception
     */
    public function runWorkload() {
        $workload = $this->_workload;
        try {
            call_user_func($workload);
        } catch (Exception $e) {
            CM_Bootloader::getInstance()->getExceptionHandler()->handleException($e);
        }
    }
}
