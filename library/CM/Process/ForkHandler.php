<?php

class CM_Process_ForkHandler {

    /** @var int */
    private $_pid;

    /** @var Closure */
    private $_workload;

    /** @var resource */
    private $_ipcStream;

    /** @var int */
    private $_identifier;

    /**
     * @param int      $pid
     * @param Closure  $workload
     * @param resource $ipcStream
     * @param int|null $identifier
     */
    public function __construct($pid, Closure $workload, $ipcStream, $identifier = null) {
        $this->_pid = (int) $pid;
        $this->_workload = $workload;
        $this->_ipcStream = $ipcStream;
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
     * @return resource
     */
    public function getIpcStream() {
        return $this->_ipcStream;
    }

    public function closeIpcStream() {
        fclose($this->_ipcStream);
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
    public function runAndSendWorkload() {
        $workload = $this->_workload;
        $result = new CM_Process_WorkloadResult();
        try {
            $return = $workload($result);
            $result->setResult($return);
        } catch (Exception $e) {
            CM_Service_Manager::getInstance()->getLogger()->addMessage('Forked workload failed', CM_Log_Logger::exceptionToLevel($e), (new CM_Log_Context())->setException($e));
            $result->setException($e);
        }

        fwrite($this->_ipcStream, serialize($result));
    }

    /**
     * @return CM_Process_WorkloadResult
     */
    public function receiveWorkloadResult() {
        $ipcData = stream_get_contents($this->_ipcStream);
        if (false === $ipcData) {
            return (new CM_Process_WorkloadResult())->setResult(null)->setException(new CM_Exception('Failed to receive IPC data.'));
        }
        if ('' === $ipcData) {
            return (new CM_Process_WorkloadResult())->setResult(null)->setException(new CM_Exception('Received no data from IPC stream.'));
        }
        try {
            $workloadResult = unserialize($ipcData);
        } catch (ErrorException $e) {
            return (new CM_Process_WorkloadResult())->setResult(null)->setException(new CM_Exception('Received unserializable IPC data', null, [
                'data' => $ipcData,
            ]));
        }
        if (!$workloadResult instanceof CM_Process_WorkloadResult) {
            return (new CM_Process_WorkloadResult())->setResult(null)->setException(new CM_Exception('Received unexpected IPC data', null, [
                'data' => $ipcData,
            ]));
        }
        return $workloadResult;
    }
}
