<?php

class CM_Process_ForkHandler {

    /** @var Closure */
    private $_workload;

    /** @var resource */
    private $_ipcStream;

    /**
     * @param Closure  $workload
     * @param resource $ipcStream
     */
    public function __construct(Closure $workload, $ipcStream) {
        $this->_workload = $workload;
        $this->_ipcStream = $ipcStream;
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
            $exception = $e;
        }

        $result = new CM_Process_WorkloadResult($return, $exception);
        fwrite($this->_ipcStream, serialize($result));

        if (null !== $exception) {
            throw $exception;
        }
    }

    /**
     * @return CM_Process_WorkloadResult
     * @throws CM_Exception
     */
    public function receiveWorkloadResult() {
        $ipcData = fgets($this->_ipcStream);
        if (false === $ipcData) {
            throw new CM_Exception('Received no data from IPC stream.');
        }
        $workloadResult = unserialize($ipcData);
        if (!$workloadResult instanceof CM_Process_WorkloadResult) {
            throw new CM_Exception('Received unexpected data over IPC stream.');
        }
        return $workloadResult;
    }
}
