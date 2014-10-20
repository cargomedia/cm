<?php

class CM_Process_ForkHandler {

    /** @var int */
    private $_pid;

    /** @var Closure */
    private $_workload;

    /** @var resource */
    private $_ipcStream;

    /**
     * @param int      $pid
     * @param Closure  $workload
     * @param resource $ipcStream
     */
    public function __construct($pid, Closure $workload, $ipcStream) {
        $this->_pid = (int) $pid;
        $this->_workload = $workload;
        $this->_ipcStream = $ipcStream;
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
     * @throws Exception
     */
    public function runAndSendWorkload() {
        $workload = $this->_workload;
        $return = null;
        $exception = null;
        try {
            $return = $workload();
        } catch (Exception $e) {
            CM_Bootloader::getInstance()->getExceptionHandler()->logException($e);
            $exception = $e;
        }

        $result = new CM_Process_WorkloadResult($return, $exception);
        fwrite($this->_ipcStream, serialize($result));
    }

    /**
     * @return CM_Process_WorkloadResult
     */
    public function receiveWorkloadResult() {
        $ipcData = fgets($this->_ipcStream);
        if (false === $ipcData) {
            return new CM_Process_WorkloadResult(null, new CM_Exception('Received no data from IPC stream.'));
        }
        try {
            $workloadResult = unserialize($ipcData);
        } catch (ErrorException $e) {
            return new CM_Process_WorkloadResult(null, new CM_Exception('Received unserializable IPC data.'));
        }
        if (!$workloadResult instanceof CM_Process_WorkloadResult) {
            return new CM_Process_WorkloadResult(null, new CM_Exception('Received unexpected IPC data.'));
        }
        return $workloadResult;
    }
}
