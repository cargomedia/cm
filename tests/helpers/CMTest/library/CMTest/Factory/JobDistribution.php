<?php

class CMTest_Factory_JobDistribution implements CM_Service_ManagerAwareInterface {

    use \Mocka\MockaTrait;
    use CM_Service_ManagerAwareTrait;

    /**
     * @return \Mocka\AbstractClassTrait|CM_Jobdistribution_QueueInterface
     */
    public function createQueue() {
        $executeJob = function (CM_Jobdistribution_Job_Abstract $job) {
            if ($job instanceof CM_Service_ManagerAware) {
                $job->setServiceManager($this->getServiceManager());
            }
            return $job->execute();
        };

        $queueMockClass = $this->mockInterface(CM_Jobdistribution_QueueInterface::class);
        $queueMockClass->mockMethod('queue')->set(function (CM_Jobdistribution_Job_Abstract $job) use ($executeJob) {
            $executeJob($job);
        });
        $queueMockClass->mockMethod('runSync')->set(function (CM_Jobdistribution_Job_Abstract $job) use ($executeJob) {
            return $executeJob($job);
        });
        $queueMockClass->mockMethod('runSyncMultiple')->set(function (array $jobList) use ($executeJob) {
            return \Functional\map($jobList, function (CM_Jobdistribution_Job_Abstract $job) use ($executeJob) {
                return $executeJob($job);
            });
        });
        return $queueMockClass->newInstanceWithoutConstructor();
    }

}
