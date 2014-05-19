<?php

class CM_Process {

    const RESPAWN_TIMEOUT = 10;

    /** @var Closure|null */
    private $_terminationCallback = null;

    /** @var Closure[] */
    private $_workloadList = array();

    private function __construct() {
        $this->_installSignalHandlers();
    }

    /**
     * @param Closure $workload
     * @throws CM_Exception
     */
    public function fork(Closure $workload) {
        $this->_spawnChild($workload);
    }

    /**
     * @return int
     */
    public function getHostId() {
        return (int) hexdec(exec('hostid'));
    }

    /**
     * @return int
     */
    public function getProcessId() {
        return posix_getpid();
    }

    /**
     * @return Closure|null
     */
    public function executeTerminationCallback() {
        $terminationCallback = $this->_terminationCallback;
        if (null !== $terminationCallback) {
            $terminationCallback();
            $this->_terminationCallback = null;
        }
    }

    /**
     * @param int $processId
     * @return bool
     */
    public function isRunning($processId) {
        $processId = (int) $processId;
        return (false !== posix_getsid($processId));
    }

    /**
     * @param int $signal
     */
    public function killChildren($signal) {
        foreach ($this->_workloadList as $processId => $workload) {
            posix_kill($processId, $signal);
        }
    }

    public function waitForChildren($keepAlive, Closure $terminationCallback = null) {
        $this->_terminationCallback = $terminationCallback;
        do {
            $pid = pcntl_wait($status);
            pcntl_signal_dispatch();
            if (-1 === $pid) {
                throw new CM_Exception('Waiting on child processes failed');
            }
            $workload = $this->_workloadList[$pid];
            unset($this->_workloadList[$pid]);
            if ($keepAlive) {
                $warning = new CM_Exception('Respawning dead child `' . $pid . '`.', null, array('severity' => CM_Exception::WARN));
                CM_Bootloader::getInstance()->getExceptionHandler()->handleException($warning);
                usleep(self::RESPAWN_TIMEOUT * 1000000);
                $this->_spawnChild($workload);
            }
        } while (!empty($this->_childProcessIdList) || $keepAlive);
        $this->executeTerminationCallback();
    }

    /**
     * @throws CM_Exception
     */
    private function _spawnChild(Closure $workload) {
        $pid = pcntl_fork();
        if ($pid === -1) {
            throw new CM_Exception('Could not spawn child process');
        }
        if ($pid) {
            $this->_workloadList[$pid] = $workload;
        } else {
            $this->_reset();
            $workload();
            exit;
        }
    }

    private function _installSignalHandlers() {
        $process = $this;
        $handler = function ($signal) use ($process) {
            $process->killChildren($signal);
            $process->executeTerminationCallback();
            exit(0);
        };
        pcntl_signal(SIGTERM, $handler, false);
        pcntl_signal(SIGINT, $handler, false);
    }

    private function _reset() {
        $this->_terminationCallback = null;
        $this->_workloadList = array();
        pcntl_signal(SIGTERM, SIG_DFL);
        pcntl_signal(SIGINT, SIG_DFL);
    }

    /**
     * @return CM_Process
     */
    public static function getInstance() {
        static $instance;
        if (!$instance) {
            $instance = new self();
        }
        return $instance;
    }
}
