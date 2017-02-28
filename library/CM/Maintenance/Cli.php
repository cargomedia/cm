<?php

class CM_Maintenance_Cli extends CM_Cli_Runnable_Abstract {

    /** @var CM_Clockwork_Manager */
    protected $_clockworkManager;

    /**
     * @synchronized
     */
    public function start() {
        $this->_clockworkManager = new CM_Clockwork_Manager();
        $storage = new CM_Clockwork_Storage_FileSystem('app-maintenance');
        $storage->setServiceManager(CM_Service_Manager::getInstance());
        $this->_clockworkManager->setStorage($storage);
        $this->_registerCallbacks();
        $this->_clockworkManager->start();
    }

    protected function _registerCallbacks() {
        $this->_registerClockworkCallbacks('1 second', [
            'CM_Jobdistribution_DelayedQueue::queueOutstanding' => function () {
                $delayedQueue = $this->getServiceManager()->getDelayedJobQueue();
                $delayedQueue->queueOutstanding();
            },
            'CM_Elasticsearch_Index_Cli::update'                => function () {
                (new CM_Elasticsearch_Index_Cli())->update();
            }
        ]);
        $this->_registerClockworkCallbacks('1 minute', [
            'CM_Model_User::offlineOld'                 => function () {
                CM_Model_User::offlineOld();
            },
            'CM_ModelAsset_User_Roles::deleteOld'       => function () {
                CM_ModelAsset_User_Roles::deleteOld();
            },
            'CM_Paging_Useragent_Abstract::deleteOlder' => function () {
                CM_Paging_Useragent_Abstract::deleteOlder(100 * 86400);
            },
            'CM_File_UserContent_Temp::deleteOlder'     => function () {
                CM_File_UserContent_Temp::deleteOlder(86400);
            },
            'CM_Paging_Ip_Blocked::deleteOlder'         => function () {
                CM_Paging_Ip_Blocked::deleteOld();
            },
            'CM_Captcha::deleteOlder'                   => function () {
                CM_Captcha::deleteOlder(3600);
            },
            'CM_Session::deleteExpired'                 => function () {
                CM_Session::deleteExpired();
            },
            'CM_MessageStream_Service::synchronize'     => function () {
                CM_Service_Manager::getInstance()->getStreamMessage()->synchronize();
            },
        ]);
        $this->_registerClockworkCallbacks('1 hour', [
            'CM_Elasticsearch_Index_Cli::optimize' => function () {
                (new CM_Elasticsearch_Index_Cli())->optimize();
            }
        ]);

        if ($this->getServiceManager()->has('janus')) {
            $this->_registerClockworkCallbacks('1 minute', [
                'CM_Janus_Service::synchronize'  => function () {
                    $this->getServiceManager()->getJanus('janus')->synchronize();
                },
                'CM_Janus_Service::checkStreams' => function () {
                    $this->getServiceManager()->getJanus('janus')->checkStreams();
                },
            ]);
        }

        $this->_registerClockworkCallbacks('15 minutes', [
            'CM_Action_Abstract::aggregate'                 => function () {
                CM_Action_Abstract::aggregate();
            },
            'CM_Action_Abstract::deleteTransgressionsOlder' => function () {
                CM_Action_Abstract::deleteTransgressionsOlder(3 * 31 * 86400);
            },
            'CM_Paging_Log::cleanup'                        => function () {
                $allLevelsList = array_values(CM_Log_Logger::getLevels());
                foreach (CM_Paging_Log::getClassChildren() as $pagingLogClass) {
                    /** @type CM_Paging_Log $log */
                    $log = new $pagingLogClass($allLevelsList);
                    $log->cleanUp();
                }
                (new CM_Paging_Log($allLevelsList, false))->cleanUp(); //deletes all untyped records
            },
        ]);
        if ($this->getServiceManager()->has('maxmind')) {
            $this->_registerClockworkCallbacks('8 days', [
                'CMService_MaxMind::upgrade' => function () {
                    try {
                        /** @var CMService_MaxMind $maxMind */
                        $maxMind = $this->getServiceManager()->get('maxmind', 'CMService_MaxMind');
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
            ]);
        }
    }

    public static function getPackageName() {
        return 'maintenance';
    }

    /**
     * @param string    $dateTimeString
     * @param Closure[] $callbacks
     */
    protected function _registerClockworkCallbacks($dateTimeString, $callbacks) {
        foreach ($callbacks as $name => $callback) {
            $transactionName = 'cm ' . static::getPackageName() . ' start: ' . $name;
            $this->_clockworkManager->registerCallback($name, $dateTimeString, function () use ($transactionName, $callback) {
                CM_Service_Manager::getInstance()->getNewrelic()->startTransaction($transactionName);
                try {
                    call_user_func_array($callback, func_get_args());
                } catch (CM_Exception $e) {
                    CM_Service_Manager::getInstance()->getNewrelic()->endTransaction();
                    throw $e;
                }
                CM_Service_Manager::getInstance()->getNewrelic()->endTransaction();
            });
        }
    }
}
