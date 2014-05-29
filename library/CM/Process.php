<?php

class CM_Process {

    const RESPAWN_TIMEOUT = 10;

    /** @var Closure|null */
    private $_terminationCallback = null;

    /** @var CM_Process_ForkHandler[] */
    private $_forkHandlerList = array();

    private function __construct() {
        $this->_installSignalHandlers();
    }

    public function executeTerminationCallback() {
        $terminationCallback = $this->_terminationCallback;
        if (null !== $terminationCallback) {
            $terminationCallback();
            $this->_terminationCallback = null;
        }
    }

    /**
     * @param Closure $workload
     * @throws CM_Exception
     */
    public function fork(Closure $workload) {
        $sockets = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        if (false === $sockets) {
            throw new CM_Exception('Cannot open stream socket pair');
        }

        $pid = pcntl_fork();
        if ($pid === -1) {
            throw new CM_Exception('Could not spawn child process');
        }
        if ($pid) {
            // parent
            fclose($sockets[0]);
            $this->_forkHandlerList[$pid] = new CM_Process_ForkHandler($workload, $sockets[1]);
        } else {
            // child
            fclose($sockets[1]);
            $this->_reset();
            $forkHandler = new CM_Process_ForkHandler($workload, $sockets[0]);
            $forkHandler->runAndSendWorkload();
            $forkHandler->closeIpcStream();
            exit;
        }
    }

    /**
     * @return int
     */
    public function getHostId() {
        return (int) hexdec(CM_Util::exec('hostid'));
    }

    /**
     * @return int
     */
    public function getProcessId() {
        return posix_getpid();
    }

    /**
     * @param int $signal
     */
    public function killChildren($signal) {
        foreach ($this->_forkHandlerList as $processId => $forkHandler) {
            posix_kill($processId, $signal);
        }
    }

    /**
     * @param bool|null    $keepAlive
     * @param Closure|null $terminationCallback
     * @return CM_Process_WorkloadResult[]
     * @throws CM_Exception
     */
    public function waitForChildren($keepAlive = null, Closure $terminationCallback = null) {
        $keepAlive = (bool) $keepAlive;
        $this->_terminationCallback = $terminationCallback;
        $workloadResultList = array();
        do {
            $pid = pcntl_wait($status);
            pcntl_signal_dispatch();
            if (-1 === $pid) {
                throw new CM_Exception('Waiting on child processes failed');
            }
            $forkHandler = $this->_forkHandlerList[$pid];
            $workloadResultList[] = $forkHandler->receiveWorkloadResult();
            $forkHandler->closeIpcStream();
            unset($this->_forkHandlerList[$pid]);
            if ($keepAlive) {
                $warning = new CM_Exception('Respawning dead child `' . $pid . '`.', null, array('severity' => CM_Exception::WARN));
                CM_Bootloader::getInstance()->getExceptionHandler()->handleException($warning);
                usleep(self::RESPAWN_TIMEOUT * 1000000);
                $this->fork($forkHandler->getWorkload());
            }
        } while (!empty($this->_forkHandlerList));
        $this->executeTerminationCallback();
        return $workloadResultList;
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

    /**
     * @param int $processId
     * @return bool
     */
    public static function isRunning($processId) {
        $processId = (int) $processId;
        return (false !== posix_getsid($processId));
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
        $this->_forkHandlerList = array();
        pcntl_signal(SIGTERM, SIG_DFL);
        pcntl_signal(SIGINT, SIG_DFL);
    }
}
