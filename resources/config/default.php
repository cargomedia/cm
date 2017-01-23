<?php

return function (CM_Config_Node $config) {

    $config->installationName = 'CM';

    $config->CM_App->setupScriptClasses = [];
    $config->CM_App->setupScriptClasses[] = CM_File_Filesystem_SetupScript::class;
    $config->CM_App->setupScriptClasses[] = CM_Db_SetupScript::class;
    $config->CM_App->setupScriptClasses[] = CM_MongoDb_SetupScript::class;
    $config->CM_App->setupScriptClasses[] = CM_Elasticsearch_SetupScript::class;
    $config->CM_App->setupScriptClasses[] = CM_Class_TypeDumper::class;
    $config->CM_App->setupScriptClasses[] = CM_Http_SetupScript::class;
    $config->CM_App->setupScriptClasses[] = CM_App_SetupScript_Translations::class;
    $config->CM_App->setupScriptClasses[] = CM_App_SetupScript_Currency::class;

    $config->timeZone = 'UTC';

    $config->CM_Site_Abstract->class = null;

    $config->CM_Cache_Local->storage = CM_Cache_Storage_Apc::class;
    $config->CM_Cache_Local->lifetime = 86400;

    $config->CM_Cache_Shared->storage = CM_Cache_Storage_Memcache::class;
    $config->CM_Cache_Shared->lifetime = 3600;

    $config->CM_Cache_Persistent->storage = CM_Cache_Storage_File::class;
    $config->CM_Cache_Persistent->lifetime = null;

    $config->CM_Paging_Ip_Blocked->maxAge = (7 * 86400);

    $config->classConfigCacheEnabled = true;

    $config->CM_Db_Db->delayedEnabled = true;

    $config->CM_MongoDb_Client->batchSize = null;

    $config->CM_Model_User->class = CM_Model_User::class;

    $config->CM_Params->class = CM_Params::class;

    $config->CM_Usertext_Usertext->class = CM_Usertext_Usertext::class;

    $config->CM_Model_Currency->default = ['code' => '840', 'abbreviation' => 'USD'];

    $config->CM_Http_Response_Page->exceptionsToCatch = [
        CM_Exception_Nonexistent::class  => ['errorPage' => CM_Page_Error_NotFound::class, 'log' => true, 'level' => CM_Log_Logger::INFO],
        CM_Exception_InvalidParam::class => ['errorPage' => CM_Page_Error_NotFound::class, 'log' => true, 'level' => CM_Log_Logger::INFO],
        CM_Exception_AuthRequired::class => ['errorPage' => CM_Page_Error_AuthRequired::class, 'log' => false],
        CM_Exception_NotAllowed::class   => ['errorPage' => CM_Page_Error_NotAllowed::class, 'log' => false],
    ];

    $config->CM_Http_Response_View_Abstract->exceptionsToCatch = [
        CM_Exception_Nonexistent::class  => ['log' => true, 'level' => CM_Log_Logger::INFO],
        CM_Exception_InvalidParam::class => ['log' => true, 'level' => CM_Log_Logger::INFO],
        CM_Exception_AuthRequired::class => [],
        CM_Exception_NotAllowed::class   => [],
        CM_Exception_Blocked::class      => [],
        CM_Exception_ActionLimit::class  => [],
    ];
    $config->CM_Http_Response_View_Abstract->catchPublicExceptions = true;

    $config->CM_Http_Response_RPC->exceptionsToCatch = [
        CM_Exception_InvalidParam::class => ['log' => true, 'level' => CM_Log_Logger::WARNING],
        CM_Exception_AuthRequired::class => ['log' => true, 'level' => CM_Log_Logger::WARNING],
        CM_Exception_NotAllowed::class   => ['log' => true, 'level' => CM_Log_Logger::WARNING],
    ];
    $config->CM_Http_Response_RPC->catchPublicExceptions = true;

    $config->CM_Adprovider->enabled = true;
    $config->CM_Adprovider->zones = [];

    $config->CM_AdproviderAdapter_Abstract->class = CM_AdproviderAdapter_Revive::class;

    $config->CM_Jobdistribution_JobWorker->servers = [['host' => 'localhost', 'port' => 4730]];

    $config->CMService_MaxMind->licenseKey = null;

    $config->services = [];

    $config->services['databases'] = [
        'class' => CM_Service_Databases::class,
    ];

    $config->services['database-master'] = [
        'class'     => CM_Db_Client::class,
        'arguments' => [
            'config' => [
                'host'     => 'localhost',
                'port'     => 3306,
                'username' => 'root',
                'password' => '',
                'db'       => 'cm',
            ],
        ],
    ];

    $config->services['delayedJobQueue'] = [
        'class'     => CM_Jobdistribution_DelayedQueue::class,
        'arguments' => [],
    ];

    $config->services['MongoDb'] = [
        'class'     => CM_MongoDb_Client::class,
        'arguments' => [
            'config' => [
                'db'      => 'cm',
                'server'  => 'mongodb://localhost:27017',
            ],
        ],
    ];

    $config->services['redis'] = [
        'class'     => CM_Redis_Client::class,
        'arguments' => [
            'config' => [
                'host' => 'localhost',
                'port' => '6379',
            ],
        ],
    ];

    $config->services['filesystem-data'] = [
        'class'  => CM_File_Filesystem_Factory::class,
        'method' => [
            'name'      => 'createFilesystem',
            'arguments' => [
                'adapterClassName' => CM_File_Filesystem_Adapter_Local::class,
                'options'          => [
                    'pathPrefix' => DIR_ROOT . 'data/',
                ],
            ],
        ],
    ];

    $config->services['filesystem-usercontent'] = [
        'class'  => CM_File_Filesystem_Factory::class,
        'method' => [
            'name'      => 'createFilesystem',
            'arguments' => [
                'adapterClassName' => CM_File_Filesystem_Adapter_Local::class,
                'options'          => [
                    'pathPrefix' => DIR_PUBLIC . 'userfiles/',
                ],
            ],
        ],
    ];

    $config->services['usercontent'] = [
        'class'     => CM_Service_UserContent::class,
        'arguments' => [
            'configList' => [
                'default' => [
                    'filesystem' => 'filesystem-usercontent',
                    'url'        => 'http://localhost/userfiles',
                ],
            ],
        ],
    ];

    $config->services['trackings'] = [
        'class'     => CM_Service_Trackings::class,
        'arguments' => [
            'trackingServiceNameList' => [],
        ],
    ];

    $config->services['tracking-adagnit'] = [
        'class'     => CMService_Adagnit_Client::class,
        'arguments' => [
            'ttl' => 86400,
        ],
    ];

    $config->services['tracking-adwords'] = [
        'class'     => CMService_AdWords_Client::class,
        'arguments' => [],
    ];

    $config->services['tracking-googleanalytics'] = [
        'class'     => CMService_GoogleAnalytics_Client::class,
        'arguments' => [
            'code' => 'my-web-property-id',
            'ttl'  => 86400,
        ],
    ];

    $config->services['tracking-googletagmanager'] = [
        'class'     => CMService_GoogleTagManager_Client::class,
        'arguments' => [
            'code' => 'GTM-######',
        ],
    ];

    $config->services['tracking-kissmetrics'] = [
        'class'     => CMService_KissMetrics_Client::class,
        'arguments' => [
            'code' => 'my-api-key',
        ],
    ];

    $config->services['tracking-inspectlet'] = [
        'class'     => CMService_Inspectlet_Client::class,
        'arguments' => [
            'code' => 'my-wid',
        ],
    ];

    $config->services['email-verification'] = [
        'class'     => CM_Service_EmailVerification_Standard::class,
        'arguments' => [],
    ];

    $config->services['memcache'] = [
        'class'     => CM_Memcache_Client::class,
        'arguments' => [
            'servers' => [
                ['host' => 'localhost', 'port' => 11211],
            ],
        ],
    ];

    $config->services['stream-message'] = [
        'class'  => CM_MessageStream_Factory::class,
        'method' => [
            'name'      => 'createService',
            'arguments' => [
                'adapterClass'     => CM_MessageStream_Adapter_SocketRedis::class,
                'adapterArguments' => [
                    'servers' => [
                        ['httpHost' => 'localhost', 'httpPort' => 8085, 'sockjsUrls' => ['http://localhost:8090']],
                    ],
                ],
            ],
        ],
    ];

    $config->services['elasticsearch'] = [
        'class'     => CM_Elasticsearch_Cluster::class,
        'arguments' => [
            'servers' => [
                ['host' => 'localhost', 'port' => 9200],
            ],
        ],
    ];

    $config->services['options'] = [
        'class'     => CM_Options::class,
        'arguments' => [],
    ];

    $config->services['newrelic'] = [
        'class'     => CMService_Newrelic::class,
        'arguments' => [
            'enabled' => false,
            'appName' => 'CM Application',
        ],
    ];

    $config->services['mailer'] = [
        'class'  => CM_Mail_MailerFactory::class,
        'method' => [
            'name'      => 'createLogMailer',
            'arguments' => [
                'logLevel' => CM_Log_Logger::INFO,
            ],
        ],
    ];

    $config->services['logger-handler-newrelic'] = [
        'class'     => CMService_NewRelic_Log_Handler::class,
        'arguments' => [
            'minLevel' => CM_Log_Logger::ERROR,
        ],
    ];

    $config->services['logger-handler-mongodb'] = [
        'class'  => CM_Log_Handler_Factory::class,
        'method' => [
            'name'      => 'createMongoDbHandler',
            'arguments' => [
                'collection'    => 'cm_log',
                'recordTtl'     => null,
                'insertOptions' => null,
                'minLevel'      => CM_Log_Logger::DEBUG,
            ],
        ]
    ];

    $config->services['logger-handler-file-error'] = [
        'class'  => CM_Log_Handler_Factory::class,
        'method' => [
            'name'      => 'createFileHandler',
            'arguments' => [
                'path'     => 'logs/error.log',
                'minLevel' => CM_Log_Logger::DEBUG,
            ],
        ],
    ];

    $config->services['logger'] = [
        'class'  => CM_Log_Factory::class,
        'method' => [
            'name'      => 'createLayeredLogger',
            'arguments' => [
                'handlersLayerConfigList' => [
                    ['logger-handler-mongodb', 'logger-handler-newrelic'],
                    ['logger-handler-file-error']
                ],
            ],
        ]
    ];

    $config->services['maintenance'] = [
        'class'     => CM_Maintenance_ServiceFactory::class,
        'arguments' => [],
        'method'    => [
            'name'      => 'createService',
            'arguments' => [
                'clockworkStorage' => new CM_Clockwork_Storage_MongoDB('maintenance'),
            ]
        ],
    ];

    $config->services[CM_Jobdistribution_Queue::class] = [
        'class'  => CM_Gearman_Factory::class,
        'method' => [
            'name'      => 'createJobQueue',
            'arguments' => [
                'servers'        => [
                    ['host' => 'localhost', 'port' => 4730],
                ],
                'workerJobLimit' => 1000,
            ],
        ],
    ];
};
