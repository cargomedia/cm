<?php

class CM_Jobdistribution_DelayedQueue implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /**
     * @param CM_Service_Manager $serviceManager
     */
    public function __construct(CM_Service_Manager $serviceManager) {
        $this->setServiceManager($serviceManager);
    }

    /**
     * @param CM_Jobdistribution_Job_Abstract $job
     * @param array                           $params
     * @param int                             $executeAt
     */
    public function addJob(CM_Jobdistribution_Job_Abstract $job, array $params, $executeAt) {
        CM_Db_Db::insert('cm_jobdistribution_delayedqueue', [
            'className' => get_class($job),
            'params'    => CM_Params::encode($params, true),
            'executeAt' => (int) $executeAt,
        ]);
    }

    public function queueOutstanding() {
        $executeAtMax = time();
        $result = CM_Db_Db::select('cm_jobdistribution_delayedqueue', '*', '`executeAt` <= ' . $executeAtMax, '`executeAt` ASC');
        while ($row = $result->fetch()) {
            $job = $this->_instantiateJob($row['className']);
            if ($job) {
                $job->queue(CM_Params::decode($row['params'], true));
            }
        }
        CM_Db_Db::delete('cm_jobdistribution_delayedqueue', '`executeAt` <= ' . $executeAtMax);
    }

    /**
     * @param string $className
     * @return CM_Jobdistribution_Job_Abstract|null
     */
    protected function _instantiateJob($className) {
        try {
            return new $className();
        } catch (Exception $e) {
            $logLevel = CM_Log_Context_App::exceptionSeverityToLevel($e);
            $this->getServiceManager()->getLogger()->addMessage('Delayed queue error', $logLevel, new CM_Log_Context_App(null, null, $e));
            return null;
        }
    }
}
