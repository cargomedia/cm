<?php

abstract class CM_Jobdistribution_Job_Abstract extends CM_Class_Abstract {

    /**
     * @param CM_Params $params
     * @return mixed
     */
    abstract protected function _execute(CM_Params $params);

    /**
     * @return CM_Jobdistribution_Priority
     */
    public function getPriority() {
        return new CM_Jobdistribution_Priority('normal');
    }

    /**
     * @param CM_Params|null $params
     * @return mixed
     * @throws CM_Exception
     */
    public function run(CM_Params $params = null) {
        $params = $params ?: CM_Params::factory();
        $resultList = $this->runMultiple(array($params));
        return reset($resultList);
    }

    /**
     * @param CM_Params[] $paramsList
     * @return mixed[]
     * @throws CM_Exception
     */
    public function runMultiple(array $paramsList) {
        foreach ($paramsList as $params) {
            $this->_verifyParams($params);
        }
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
            $workload = CM_Util::jsonEncode($params->getParamsEncoded());
            $task = $this->_addTask($workload, $gearmanClient);
            if (false === $task) {
                throw new CM_Exception('Cannot add task', null, ['jobName' => $this->_getJobName()]);
            }
        }
        $gearmanClient->runTasks();

        if (count($resultList) != count($paramsList)) {
            throw new CM_Exception('Job failed. Invalid results', null, [
                'jobName'         => $this->_getJobName(),
                'countResultList' => count($resultList),
                'countParamList'  => count($paramsList),
            ]);
        }
        return $resultList;
    }

    /**
     * @param CM_Params|null $params
     * @throws CM_Exception
     */
    public function queue(CM_Params $params = null) {
        $params = $params ?: CM_Params::factory();
        $this->_verifyParams($params);
        if (!$this->_getGearmanEnabled()) {
            $this->_runMultipleWithoutGearman(array($params));
            return;
        }

        $workload = CM_Util::jsonEncode($params->getParamsEncoded());
        $gearmanClient = $this->_getGearmanClient();
        $priority = $this->getPriority();
        switch ($priority) {
            case CM_Jobdistribution_Priority::HIGH:
                $gearmanClient->doHighBackground($this->_getJobName(), $workload);
                break;
            case CM_Jobdistribution_Priority::NORMAL:
                $gearmanClient->doBackground($this->_getJobName(), $workload);
                break;
            case CM_Jobdistribution_Priority::LOW:
                $gearmanClient->doLowBackground($this->_getJobName(), $workload);
                break;
            default:
                throw new CM_Exception('Invalid priority', null, ['priority' => (string) $priority]);
        }
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
            throw new CM_Exception_Nonexistent('Cannot decode workload for Job', CM_Exception::WARN, [
                'job'                      => get_class($this),
                'originalExceptionMessage' => $ex->getMessage(),
            ]);
        }
        return CM_Params::encode($this->_executeJob($params), true);
    }

    /**
     * @param string        $workload
     * @param GearmanClient $gearmanClient
     * @return GearmanTask
     */
    protected function _addTask($workload, $gearmanClient) {
        return $gearmanClient->addTaskHigh($this->_getJobName(), $workload);
    }

    /**
     * @return string
     */
    protected function _getJobName() {
        return get_class($this);
    }

    /**
     * @param CM_Params[] $paramsList
     * @return mixed[]
     */
    protected function _runMultipleWithoutGearman(array $paramsList) {
        $resultList = array();
        foreach ($paramsList as $params) {
            $resultList[] = $this->_executeJob($params);
        }
        return $resultList;
    }

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
     * @param CM_Params $params
     */
    protected static function _verifyParams(CM_Params $params) {
        if ($params->isFullyEncoded()) {
            return;
        }
        foreach ($params->getParamsDecoded() as $value) {
            self::_verifyParam($value);
        }
    }

    /**
     * @param mixed $value
     * @throws CM_Exception_InvalidParam
     */
    protected static function _verifyParam($value) {
        if (is_array($value)) {
            $value = array_map('self::_verifyParam', $value);
        }
        if (is_object($value) && false === $value instanceof CM_ArrayConvertible) {
            throw new CM_Exception_InvalidParam('Object is not an instance of CM_ArrayConvertible', null, ['className' => get_class($value)]);
        }
    }
}
