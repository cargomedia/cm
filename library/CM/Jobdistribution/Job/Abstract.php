<?php

abstract class CM_Jobdistribution_Job_Abstract extends CM_Class_Abstract {

    /**
     * @param CM_Params $params
     * @return mixed
     */
    abstract protected function _execute(CM_Params $params);

    /**
     * @param CM_Params $params
     * @return mixed
     * @throws Exception
     */
    private function _executeJob(CM_Params $params) {
        CM_Service_Manager::getInstance()->getNewrelic()->startTransaction('CM Job: ' . $this->_getClassName());
        try {
            $return = $this->_execute($params);
            CM_Service_Manager::getInstance()->getNewrelic()->endTransaction();
            return $return;
        } catch (Exception $ex) {
            CM_Service_Manager::getInstance()->getNewrelic()->endTransaction();
            throw $ex;
        }
    }

    /**
     * @param array|null $params
     * @return mixed
     * @throws CM_Exception
     */
    public function run(array $params = null) {
        if (null === $params) {
            $params = array();
        }
        $resultList = $this->runMultiple(array($params));
        return reset($resultList);
    }

    /**
     * @param array[] $paramsList
     * @return mixed[]
     * @throws CM_Exception
     */
    public function runMultiple(array $paramsList) {
        $this->_verifyParams($paramsList);
        if (!$this->_getGearmanEnabled()) {
            return $this->_runMultipleWithoutGearman($paramsList);
        }

        $resultList = array();
        $gearmanClient = $this->_getGearmanClient();

        $gearmanClient->setCompleteCallback(function (GearmanTask $task) use (&$resultList) {
            $resultList[] = CM_Params::decode($task->data(), true);
        });

        $failureList = array();
        $gearmanClient->setFailCallback(function (GearmanTask $task) use (&$failureList) {
            $failureList[] = $task;
        });

        foreach ($paramsList as $params) {
            $workload = CM_Params::encode($params, true);
            $task = $gearmanClient->addTask($this->_getJobName(), $workload);
            if (false === $task) {
                throw new CM_Exception('Cannot add task `' . $this->_getJobName() . '`.');
            }
        }
        $gearmanClient->runTasks();

        if (count($resultList) != count($paramsList)) {
            throw new CM_Exception('Job `' . $this->_getJobName() . '` failed (' . count($resultList) . '/' . count($paramsList) . ' results).');
        }
        return $resultList;
    }

    /**
     * @param array|null $params
     */
    public function queue(array $params = null) {
        if (null === $params) {
            $params = array();
        }
        $this->_verifyParams($params);
        if (!$this->_getGearmanEnabled()) {
            $this->_runMultipleWithoutGearman(array($params));
            return;
        }

        $workload = CM_Params::encode($params, true);
        $gearmanClient = $this->_getGearmanClient();
        $gearmanClient->doBackground($this->_getJobName(), $workload);
    }

    /**
     * @param GearmanJob $job
     * @return string|null
     * @throws CM_Exception_Nonexistent
     */
    public function __executeGearman(GearmanJob $job) {
        $workload = $job->workload();
        try {
            $params = CM_Params::factory(CM_Params::jsonDecode($workload), true);
        } catch (CM_Exception_Nonexistent $ex) {
            throw new CM_Exception_Nonexistent(
                'Cannot decode workload for Job `' . get_class($this) . '`: Original exception message `' . $ex->getMessage() .
                '`', null, null, CM_Exception::WARN);
        }
        return CM_Params::encode($this->_executeJob($params), true);
    }

    /**
     * @return string
     */
    protected function _getJobName() {
        return get_class($this);
    }

    /**
     * @param array[] $paramsList
     * @return mixed[]
     */
    protected function _runMultipleWithoutGearman(array $paramsList) {
        $resultList = array();
        foreach ($paramsList as $params) {
            $resultList[] = $this->_executeJob(CM_Params::factory($params, true));
        }
        return $resultList;
    }

    /**
     * @return boolean
     */
    private function _getGearmanEnabled() {
        return (boolean) self::_getConfig()->gearmanEnabled;
    }

    /**
     * @return GearmanClient
     * @throws CM_Exception
     */
    protected function _getGearmanClient() {
        if (!extension_loaded('gearman')) {
            throw new CM_Exception('Missing `gearman` extension');
        }
        $config = static::_getConfig();
        $gearmanClient = new GearmanClient();
        foreach ($config->servers as $server) {
            $gearmanClient->addServer($server['host'], $server['port']);
        }
        return $gearmanClient;
    }

    /**
     * @param mixed $value
     * @throws CM_Exception_InvalidParam
     */
    protected static function _verifyParams($value) {
        if (is_array($value)) {
            $value = array_map('self::_verifyParams', $value);
        }
        if (is_object($value) && false === $value instanceof CM_ArrayConvertible) {
            throw new CM_Exception_InvalidParam('Object of class `' . get_class($value) . '` is not an instance of CM_ArrayConvertible');
        }
    }
}
