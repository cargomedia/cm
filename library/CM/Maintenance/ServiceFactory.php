<?php

class CM_Maintenance_ServiceFactory implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /** @var CM_Maintenance_ServiceFactory|null */
    private $_serviceFactory = null;

    /**
     * @param CM_Maintenance_ServiceFactory|null $serviceFactory
     */
    public function __construct(CM_Maintenance_ServiceFactory $serviceFactory = null) {
        $this->_serviceFactory = $serviceFactory;
    }

    /**
     * @param CM_Clockwork_Storage_Abstract|null $clockworkStorage
     * @return CM_Maintenance_Service
     */
    public function createService(CM_Clockwork_Storage_Abstract $clockworkStorage = null) {
        $maintenance = new CM_Maintenance_Service($clockworkStorage);
        $this->registerCallbacks($maintenance);
        return $maintenance;
    }

    /**
     * @param CM_Maintenance_Service $maintenance
     */
    public function registerCallbacks(CM_Maintenance_Service $maintenance) {
        if ($this->_serviceFactory) {
            $this->_serviceFactory->setServiceManager($this->getServiceManager());
            $this->_serviceFactory->registerCallbacks($maintenance);
        }
        $this->_registerCallbacks($maintenance);
    }

    /**
     * @param CM_Maintenance_Service $maintenance
     */
    protected function _registerCallbacks(CM_Maintenance_Service $maintenance) {
        $this->_registerClockworkCallbacks('1 second', [
            'CM_Jobdistribution_DelayedQueue::queueOutstanding' => function(DateTime $lastRuntime, CM_Service_Manager $serviceManager) {
                $delayedQueue = $serviceManager->getDelayedJobQueue();
                $delayedQueue->queueOutstanding();
            },
            'CM_Elasticsearch_Index_Cli::update'                => function(DateTime $lastRuntime, CM_Service_Manager $serviceManager) {
                (new CM_Elasticsearch_Index_Cli())->update();
            }
        ], $maintenance);
        $this->_registerClockworkCallbacks('1 minute', [
            'CM_Model_User::offlineOld'                 => function(DateTime $lastRuntime, CM_Service_Manager $serviceManager) {
                CM_Model_User::offlineOld();
            },
            'CM_ModelAsset_User_Roles::deleteOld'       => function(DateTime $lastRuntime, CM_Service_Manager $serviceManager) {
                CM_ModelAsset_User_Roles::deleteOld();
            },
            'CM_Paging_Useragent_Abstract::deleteOlder' => function(DateTime $lastRuntime, CM_Service_Manager $serviceManager) {
                CM_Paging_Useragent_Abstract::deleteOlder(100 * 86400);
            },
            'CM_File_UserContent_Temp::deleteOlder'     => function(DateTime $lastRuntime, CM_Service_Manager $serviceManager) {
                CM_File_UserContent_Temp::deleteOlder(86400);
            },
            'CM_Paging_Ip_Blocked::deleteOlder'         => function(DateTime $lastRuntime, CM_Service_Manager $serviceManager) {
                CM_Paging_Ip_Blocked::deleteOld();
            },
            'CM_Captcha::deleteOlder'                   => function(DateTime $lastRuntime, CM_Service_Manager $serviceManager) {
                CM_Captcha::deleteOlder(3600);
            },
            'CM_Session::deleteExpired'                 => function(DateTime $lastRuntime, CM_Service_Manager $serviceManager) {
                CM_Session::deleteExpired();
            },
            'CM_MessageStream_Service::synchronize'     => function(DateTime $lastRuntime, CM_Service_Manager $serviceManager) {
                CM_Service_Manager::getInstance()->getStreamMessage()->synchronize();
            },
        ], $maintenance);
        $this->_registerClockworkCallbacks('1 hour', [
            'CM_Elasticsearch_Index_Cli::optimize' => function(DateTime $lastRuntime, CM_Service_Manager $serviceManager) {
                (new CM_Elasticsearch_Index_Cli())->optimize();
            }
        ], $maintenance);

        if ($this->getServiceManager()->has('janus')) {
            $this->_registerClockworkCallbacks('1 minute', [
                'CM_Janus_Service::synchronize'  => function(DateTime $lastRuntime, CM_Service_Manager $serviceManager) {
                    $serviceManager->getJanus('janus')->synchronize();
                },
                'CM_Janus_Service::checkStreams' => function(DateTime $lastRuntime, CM_Service_Manager $serviceManager) {
                    $serviceManager->getJanus('janus')->checkStreams();
                },
            ], $maintenance);
        }

        $this->_registerClockworkCallbacks('15 minutes', [
            'CM_Action_Abstract::aggregate'                 => function(DateTime $lastRuntime, CM_Service_Manager $serviceManager) {
                CM_Action_Abstract::aggregate();
            },
            'CM_Action_Abstract::deleteTransgressionsOlder' => function(DateTime $lastRuntime, CM_Service_Manager $serviceManager) {
                CM_Action_Abstract::deleteTransgressionsOlder(3 * 31 * 86400);
            },
            'CM_Paging_Log::cleanup'                        => function(DateTime $lastRuntime, CM_Service_Manager $serviceManager) {
                $allLevelsList = array_values(CM_Log_Logger::getLevels());
                foreach (CM_Paging_Log::getClassChildren() as $pagingLogClass) {
                    /** @type CM_Paging_Log $log */
                    $log = new $pagingLogClass($allLevelsList);
                    $log->cleanUp();
                }
                (new CM_Paging_Log($allLevelsList, false))->cleanUp(); //deletes all untyped records
            },
        ], $maintenance);
        if ($this->getServiceManager()->has('maxmind')) {
            $this->_registerClockworkCallbacks('8 days', [
                'CMService_MaxMind::upgrade' => function(DateTime $lastRuntime, CM_Service_Manager $serviceManager) {
                    try {
                        /** @var CMService_MaxMind $maxMind */
                        $maxMind = $serviceManager->get('maxmind', 'CMService_MaxMind');
                        $maxMind->upgrade();
                    } catch (Exception $exception) {
                        if (!is_a($exception, 'CM_Exception')) {
                            $exception = new CM_Exception($exception->getMessage(), null, [
                                'file'  => $exception->getFile(),
                                'line'  => $exception->getLine(),
                                'trace' => $exception->getTraceAsString(),
                            ]);
                        }
                        $exception->setSeverity(CM_Exception::FATAL);
                        throw $exception;
                    }
                },
            ], $maintenance);
        }
    }

    /**
     * @param string                 $dateTimeString
     * @param array                  $events
     * @param CM_Maintenance_Service $maintenance
     */
    protected function _registerClockworkCallbacks($dateTimeString, array $events, CM_Maintenance_Service $maintenance) {
        foreach ($events as $name => $callback) {
            $maintenance->registerEvent($name, $dateTimeString, $callback);
        }
    }
}
