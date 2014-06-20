<?php

class CM_Process {

    const RESPAWN_TIMEOUT = 10;

    /** @var Closure[] */
    private $_terminationCallbackList = array();

    /** @var CM_Process_ForkHandler[] */
    private $_forkHandlerList = array();

    /** @var int */
    private $_forkHandlerCounter = 0;

    private function __construct() {
        $this->_installSignalHandlers();
    }

    /**
     * @param int|null                                                   $pid
     * @param CM_Process_WorkloadResult|CM_Process_WorkloadResult[]|null $workLoadResult
     */
    public function executeTerminationCallback($pid = null, $workLoadResult = null) {
        $pid = ($pid !== null) ? (int) $pid : 0;
        if (isset($this->_terminationCallbackList[$pid])) {
            $this->_terminationCallbackList[$pid]($workLoadResult);
            unset($this->_terminationCallbackList[$pid]);
        }
    }

    /**
     * @param Closure      $workload
     * @param Closure|null $terminationCallback
     * @return int
     * @throws CM_Exception
     */
    public function fork(Closure $workload, Closure $terminationCallback = null) {
        $sequence = ++$this->_forkHandlerCounter;
        return $this->_fork($workload, $sequence, $terminationCallback);
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
        foreach ($this->_forkHandlerList as $forkHandler) {
            posix_kill($forkHandler->getPid(), $signal);
        }
    }

    /**
     * @return CM_Process_WorkloadResult[]
     * @throws CM_Exception
     */
    public function listenForChildren() {
        return $this->_wait(null, null, true);
    }

    /**
     * @param bool|null    $keepAlive
     * @param Closure|null $terminationCallback
     * @return CM_Process_WorkloadResult[]
     * @throws CM_Exception
     */
    public function waitForChildren($keepAlive = null, Closure $terminationCallback = null) {
        return $this->_wait($keepAlive, $terminationCallback);
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

    /**
     * @param Closure      $workload
     * @param int          $sequence
     * @param Closure|null $terminationCallback
     * @throws CM_Exception
     * @return int
     */
    private function _fork(Closure $workload, $sequence, Closure $terminationCallback = null) {
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
            $this->_forkHandlerList[$sequence] = new CM_Process_ForkHandler($pid, $workload, $sockets[1]);
            $this->_terminationCallbackList[$pid] = $terminationCallback;
        } else {
            // child
            fclose($sockets[1]);
            $this->_reset();
            $forkHandler = new CM_Process_ForkHandler($this->getProcessId(), $workload, $sockets[0]);
            $forkHandler->runAndSendWorkload();
            $forkHandler->closeIpcStream();
            exit;
        }
        return $pid;
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

    public function _reset() {
        $this->_terminationCallbackList = array();
        $this->_forkHandlerList = array();
        pcntl_signal(SIGTERM, SIG_DFL);
        pcntl_signal(SIGINT, SIG_DFL);
    }

    /**
     * @param bool|null    $keepAlive
     * @param Closure|null $terminationCallback
     * @param boolean      $nohang
     * @throws CM_Exception
     * @return CM_Process_WorkloadResult[]
     */
    private function _wait($keepAlive = null, Closure $terminationCallback = null, $nohang = null) {
        $keepAlive = (bool) $keepAlive;
        $this->_terminationCallbackList[0] = $terminationCallback;
        $workloadResultList = array();
        $waitOption = $nohang ? WNOHANG : 0;
        if (!empty($this->_forkHandlerList)) {
            do {
                $pid = pcntl_wait($status, $waitOption);
                pcntl_signal_dispatch();
                if (-1 === $pid) {
                    throw new CM_Exception('Waiting on child processes failed');
                } elseif ($pid > 0) {
                    $forkHandlerSequence = $this->_getForkHandlerSequenceByPid($pid);
                    $forkHandler = $this->_forkHandlerList[$forkHandlerSequence];
                    $workloadResultList[$forkHandlerSequence] = $forkHandler->receiveWorkloadResult();
                    $forkHandler->closeIpcStream();
                    unset($this->_forkHandlerList[$forkHandlerSequence]);
                    if ($keepAlive) {
                        $warning = new CM_Exception('Respawning dead child `' . $pid . '`.', null, array('severity' => CM_Exception::WARN));
                        CM_Bootloader::getInstance()->getExceptionHandler()->handleException($warning);
                        usleep(self::RESPAWN_TIMEOUT * 1000000);
                        $callback = isset($this->_terminationCallbackList[$pid]) ? $this->_terminationCallbackList[$pid] : null;
                        $this->_fork($forkHandler->getWorkload(), $forkHandlerSequence, $callback);
                    }
                    $this->executeTerminationCallback($pid, $workloadResultList[$forkHandlerSequence]);
                }
            } while (!empty($this->_forkHandlerList) && $pid > 0);
        }
        $this->executeTerminationCallback(0, $workloadResultList);

        ksort($workloadResultList);
        return $workloadResultList;
    }

    /**
     * @param int $pid
     * @return int
     * @throws CM_Exception
     */
    private function _getForkHandlerSequenceByPid($pid) {
        foreach ($this->_forkHandlerList as $sequence => $forkHandler) {
            if ($pid === $forkHandler->getPid()) {
                return $sequence;
            }
        }
        throw new CM_Exception('Cannot find reference to fork-handler with PID `' . $pid . '`.');
    }
}
