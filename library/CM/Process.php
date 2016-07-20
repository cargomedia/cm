<?php

class CM_Process {

    const RESPAWN_TIMEOUT = 10;

    /** @var CM_EventHandler_EventHandler|null */
    private $_eventHandler;

    /** @var CM_Process_ForkHandler[] */
    private $_forkHandlerList = [];

    /** @var int */
    private $_forkHandlerCounter = 0;

    /**
     * @param string   $event
     * @param callable $callback
     */
    public function bind($event, callable $callback) {
        if (null === $this->_eventHandler) {
            $this->_eventHandler = new CM_EventHandler_EventHandler();

            $handler = function ($signal) {
                $this->trigger('exit', $signal);
                exit(0);
            };
            pcntl_signal(SIGTERM, $handler, false);
            pcntl_signal(SIGINT, $handler, false);
        }
        $this->_eventHandler->bind($event, $callback);
    }

    /**
     * @param string        $event
     * @param callable|null $callback
     */
    public function unbind($event, callable $callback = null) {
        if (null === $this->_eventHandler) {
            return;
        }
        $this->_eventHandler->unbind($event, $callback);
    }

    /**
     * @param string     $event
     * @param mixed|null $param1
     * @param mixed|null $param2
     */
    public function trigger($event, $param1 = null, $param2 = null) {
        if (null === $this->_eventHandler) {
            return;
        }
        $arguments = func_get_args();
        call_user_func_array([$this->_eventHandler, 'trigger'], $arguments);
    }

    /**
     * @param Closure $workload
     * @return CM_Process_ForkHandler
     * @throws CM_Exception
     */
    public function fork(Closure $workload) {
        $identifier = ++$this->_forkHandlerCounter;
        return $this->_fork($workload, $identifier);
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
     * @param int $processId
     * @return bool
     */
    public function isRunning($processId) {
        $processId = (int) $processId;
        return (false !== posix_getsid($processId));
    }

    /**
     * @param float|null $timeoutKill
     */
    public function killChildren($timeoutKill = null) {
        if (null === $timeoutKill) {
            $timeoutKill = 30;
        }
        $timeoutKill = (float) $timeoutKill;
        $signal = SIGTERM;
        $timeStart = microtime(true);
        $timeoutReached = false;
        $timeOutput = $timeStart;

        while (!empty($this->_forkHandlerList)) {
            $timeNow = microtime(true);
            $timePassed = $timeNow - $timeStart;

            if ($timePassed > $timeoutKill) {
                $signal = SIGKILL;
                $timeoutReached = true;
            }
            if ($timeNow > $timeOutput + 2 || $timeoutReached) {
                $message = join(' ', [
                    count($this->_forkHandlerList) . ' children remaining',
                    'after ' . round($timePassed, 1) . ' seconds,',
                    'killing with signal `' . $signal . '`...',
                ]);
                echo $message . PHP_EOL;
                if ($timeoutReached) {
                    $logContext = new CM_Log_Context();
                    $logContext->setExtra([
                        'pid'  => $this->getProcessId(),
                        'argv' => join(' ', $this->getArgv()),
                    ]);
                    CM_Service_Manager::getInstance()->getLogger()->error($message, $logContext);
                }
                $timeOutput = $timeNow;
            }

            foreach ($this->_forkHandlerList as $forkHandler) {
                posix_kill($forkHandler->getPid(), $signal);
            }

            usleep(1000000 * 0.05);

            foreach ($this->_forkHandlerList as $forkHandler) {
                $pid = pcntl_waitpid($forkHandler->getPid(), $status, WNOHANG);
                if ($pid > 0 || !$this->isRunning($pid)) {
                    $forkHandler = $this->_getForkHandlerByPid($forkHandler->getPid());
                    unset($this->_forkHandlerList[$forkHandler->getIdentifier()]);
                    $forkHandler->closeIpcStream();
                    if (!$this->_hasForks()) {
                        $this->unbind('exit', [$this, 'killChildren']);
                    }
                }
            }
        }
    }

    /**
     * @param boolean|null $keepAlive
     * @return CM_Process_WorkloadResult[]
     * @throws CM_Exception
     */
    public function listenForChildren($keepAlive = null) {
        return $this->_wait($keepAlive, true);
    }

    /**
     * @param bool|null $keepAlive
     * @return CM_Process_WorkloadResult[]
     * @throws CM_Exception
     */
    public function waitForChildren($keepAlive = null) {
        return $this->_wait($keepAlive, false);
    }

    /**
     * @return string[]
     */
    public function getArgv() {
        return $_SERVER['argv'];
    }

    public function handleSignals() {
        pcntl_signal_dispatch();
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
     * @return bool
     */
    protected function _hasForks() {
        return count($this->_forkHandlerList) > 0;
    }

    /**
     * @param Closure $workload
     * @param int     $identifier
     * @throws CM_Exception
     * @return CM_Process_ForkHandler
     */
    private function _fork(Closure $workload, $identifier) {
        if (!$this->_hasForks()) {
            $this->bind('exit', [$this, 'killChildren']);
        }
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
            $forkHandler = $this->_getForkHandler($pid, $workload, $sockets[1], $identifier);
            $this->_forkHandlerList[$identifier] = $forkHandler;
            return $forkHandler;
        } else {
            // child
            try {
                fclose($sockets[1]);
                $this->_reset();
                CM_Service_Manager::getInstance()->resetServiceInstances();
                $forkHandler = $this->_getForkHandler($this->getProcessId(), $workload, $sockets[0]);
                $forkHandler->runAndSendWorkload();
                $forkHandler->closeIpcStream();
            } catch (Exception $e) {
                CM_Bootloader::getInstance()->getExceptionHandler()->handleException($e);
            }
            exit;
        }
    }

    /**
     * @param int      $pid
     * @param Closure  $workload
     * @param resource $ipcStream
     * @param int|null $identifier
     * @return CM_Process_ForkHandler
     */
    protected function _getForkHandler($pid, Closure $workload, $ipcStream, $identifier = null) {
        return new CM_Process_ForkHandler($pid, $workload, $ipcStream, $identifier);
    }

    protected function _reset() {
        $this->_eventHandler = null;
        $this->_forkHandlerList = [];
        pcntl_signal(SIGTERM, SIG_DFL);
        pcntl_signal(SIGINT, SIG_DFL);
    }

    /**
     * @param bool|null $keepAlive
     * @param boolean   $nohang
     * @return CM_Process_WorkloadResult[]
     * @throws CM_Exception
     * @throws Exception
     * @internal param callable|null $terminationCallback
     */
    private function _wait($keepAlive = null, $nohang = null) {
        $keepAlive = (bool) $keepAlive;
        $workloadResultList = [];
        $waitOption = $nohang ? WNOHANG : 0;
        if (!empty($this->_forkHandlerList)) {
            do {
                $pid = pcntl_wait($status, $waitOption);
                $this->handleSignals();
                if (-1 === $pid) {
                    throw new CM_Exception('Waiting on child processes failed');
                }
                if ($pid > 0 && ($forkHandler = $this->_findForkHandlerByPid($pid))) {
                    unset($this->_forkHandlerList[$forkHandler->getIdentifier()]);
                    $workloadResultList[$forkHandler->getIdentifier()] = $forkHandler->receiveWorkloadResult();
                    $forkHandler->closeIpcStream();
                    if (!$this->_hasForks()) {
                        $this->unbind('exit', [$this, 'killChildren']);
                    }
                    if ($keepAlive) {
                        $warning = new CM_Exception('Respawning dead child `' . $pid . '`.', CM_Exception::WARN);
                        CM_Bootloader::getInstance()->getExceptionHandler()->handleException($warning);
                        usleep(self::RESPAWN_TIMEOUT * 1000000);
                        $this->_fork($forkHandler->getWorkload(), $forkHandler->getIdentifier());
                    }
                }
            } while (!empty($this->_forkHandlerList) && $pid > 0);
        }
        ksort($workloadResultList);
        return $workloadResultList;
    }

    /**
     * @param int $pid
     * @return CM_Process_ForkHandler|null
     */
    private function _findForkHandlerByPid($pid) {
        $pid = (int) $pid;
        foreach ($this->_forkHandlerList as $forkHandler) {
            if ($pid === $forkHandler->getPid()) {
                return $forkHandler;
            }
        }
        return null;
    }

    /**
     * @param int $pid
     * @return CM_Process_ForkHandler
     * @throws CM_Exception
     */
    private function _getForkHandlerByPid($pid) {
        if ($forkHandler = $this->_findForkHandlerByPid($pid)) {
            return $forkHandler;
        }
        throw new CM_Exception('Cannot find reference to fork-handler with PID `' . $pid . '`.');
    }
}
