<?php

return function (CM_Config_Node $config) {

    $config->installationName = 'CM';

    $config->CM_App->setupScriptClasses = array();
    $config->CM_App->setupScriptClasses[] = 'CM_File_Filesystem_SetupScript';
    $config->CM_App->setupScriptClasses[] = 'CM_Db_SetupScript';
    $config->CM_App->setupScriptClasses[] = 'CM_MongoDb_SetupScript';
    $config->CM_App->setupScriptClasses[] = 'CM_Elasticsearch_SetupScript';
    $config->CM_App->setupScriptClasses[] = 'CM_Class_TypeDumper';
    $config->CM_App->setupScriptClasses[] = 'CM_Http_SetupScript';
    $config->CM_App->setupScriptClasses[] = 'CM_App_SetupScript_Translations';
    $config->CM_App->setupScriptClasses[] = 'CM_App_SetupScript_Currency';

    $config->timeZone = 'UTC';

    $config->CM_Site_Abstract->class = null;

    $config->CM_Cache_Local->storage = 'CM_Cache_Storage_Apc';
    $config->CM_Cache_Local->lifetime = 86400;

    $config->CM_Cache_Shared->storage = 'CM_Cache_Storage_Memcache';
    $config->CM_Cache_Shared->lifetime = 3600;

    $config->CM_Cache_Persistent->storage = 'CM_Cache_Storage_File';
    $config->CM_Cache_Persistent->lifetime = null;

    $config->CM_Paging_Ip_Blocked->maxAge = (7 * 86400);

    $config->classConfigCacheEnabled = true;

    $config->CM_Db_Db->delayedEnabled = true;

    $config->CM_MongoDb_Client->batchSize = null;

    $config->CM_Model_User->class = 'CM_Model_User';

    $config->CM_Params->class = 'CM_Params';

    $config->CM_Usertext_Usertext->class = 'CM_Usertext_Usertext';

    $config->CM_Model_Currency->default = ['code' => '840', 'abbreviation' => 'USD'];

    $config->CM_Http_Response_Page->exceptionsToCatch = array(
        'CM_Exception_Nonexistent'  => ['errorPage' => 'CM_Page_Error_NotFound', 'log' => true, 'level' => CM_Log_Logger::INFO],
        'CM_Exception_InvalidParam' => ['errorPage' => 'CM_Page_Error_NotFound', 'log' => true, 'level' => CM_Log_Logger::INFO],
        'CM_Exception_AuthRequired' => ['errorPage' => 'CM_Page_Error_AuthRequired', 'log' => false],
        'CM_Exception_NotAllowed'   => ['errorPage' => 'CM_Page_Error_NotAllowed', 'log' => false],
    );

    $config->CM_Http_Response_View_Abstract->exceptionsToCatch = array(
        'CM_Exception_Nonexistent'  => ['log' => true, 'level' => CM_Log_Logger::INFO],
        'CM_Exception_InvalidParam' => ['log' => true, 'level' => CM_Log_Logger::INFO],
        'CM_Exception_AuthRequired' => [],
        'CM_Exception_NotAllowed'   => [],
        'CM_Exception_Blocked'      => [],
        'CM_Exception_ActionLimit'  => [],
    );
    $config->CM_Http_Response_View_Abstract->catchPublicExceptions = true;

    $config->CM_Http_Response_RPC->exceptionsToCatch = array(
        'CM_Exception_InvalidParam' => ['log' => true, 'level' => CM_Log_Logger::WARNING],
        'CM_Exception_AuthRequired' => ['log' => true, 'level' => CM_Log_Logger::WARNING],
        'CM_Exception_NotAllowed'   => ['log' => true, 'level' => CM_Log_Logger::WARNING],
    );
    $config->CM_Http_Response_RPC->catchPublicExceptions = true;

    $config->CM_Adprovider->enabled = true;
    $config->CM_Adprovider->zones = array();

    $config->CM_AdproviderAdapter_Abstract->class = CM_AdproviderAdapter_Revive::class;

    $config->CM_Jobdistribution_JobWorker->servers = array(array('host' => 'localhost', 'port' => 4730));

    $config->CM_Jobdistribution_Job_Abstract->gearmanEnabled = true;
    $config->CM_Jobdistribution_Job_Abstract->servers = array(array('host' => 'localhost', 'port' => 4730));

    $config->CMService_MaxMind->licenseKey = null;

    $config->services = array();

    $config->services['databases'] = array(
        'class' => 'CM_Service_Databases',
    );

    $config->services['database-master'] = array(
        'class'     => 'CM_Db_Client',
        'arguments' => array(
            'config' => array(
                'host'     => 'localhost',
                'port'     => 3306,
                'username' => 'root',
                'password' => '',
                'db'       => 'cm',
            ),
        ),
    );

    $config->services['delayedJobQueue'] = [
        'class'     => 'CM_Jobdistribution_DelayedQueue',
        'arguments' => [],
    ];

    $config->services['MongoDb'] = array(
        'class'     => 'CM_MongoDb_Client',
        'arguments' => array(
            'config' => array(
                'db'      => 'cm',
                'server'  => 'mongodb://localhost:27017',
                'options' => array('connect' => true),
            ),
        ),
    );

    $config->services['redis'] = array(
        'class'     => 'CM_Redis_Client',
        'arguments' => array(
            'config' => array(
                'host' => 'localhost',
                'port' => '6379',
            ),
        ),
    );

    $config->services['filesystem-data'] = array(
        'class'  => 'CM_File_Filesystem_Factory',
        'method' => array(
            'name'      => 'createFilesystem',
            'arguments' => array(
                'adapterClassName' => 'CM_File_Filesystem_Adapter_Local',
                'options'          => array(
                    'pathPrefix' => DIR_ROOT . 'data/',
                ),
            ),
        ),
    );

    $config->services['filesystem-usercontent'] = array(
        'class'  => 'CM_File_Filesystem_Factory',
        'method' => array(
            'name'      => 'createFilesystem',
            'arguments' => array(
                'adapterClassName' => 'CM_File_Filesystem_Adapter_Local',
                'options'          => array(
                    'pathPrefix' => DIR_PUBLIC . 'userfiles/',
                ),
            ),
        ),
    );

    $config->services['usercontent'] = array(
        'class'     => 'CM_Service_UserContent',
        'arguments' => array(
            'configList' => array(
                'default' => array(
                    'filesystem' => 'filesystem-usercontent',
                    'url'        => 'http://localhost/userfiles',
                ),
            ),
        ),
    );

    $config->services['trackings'] = array(
        'class'     => 'CM_Service_Trackings',
        'arguments' => array(
            'trackingServiceNameList' => array(),
        ),
    );

    $config->services['tracking-adagnit'] = [
        'class'     => 'CMService_Adagnit_Client',
        'arguments' => [
            'ttl' => 86400,
        ],
    ];

    $config->services['tracking-adwords'] = [
        'class'     => 'CMService_AdWords_Client',
        'arguments' => [],
    ];

    $config->services['tracking-googleanalytics'] = array(
        'class'     => 'CMService_GoogleAnalytics_Client',
        'arguments' => array(
            'code' => 'my-web-property-id',
            'ttl'  => 86400,
        ),
    );

    $config->services['tracking-kissmetrics'] = array(
        'class'     => 'CMService_KissMetrics_Client',
        'arguments' => array(
            'code' => 'my-api-key',
        ),
    );

    $config->services['tracking-inspectlet'] = array(
        'class'     => 'CMService_Inspectlet_Client',
        'arguments' => array(
            'code' => 'my-wid',
        ),
    );

    $config->services['email-verification'] = array(
        'class'     => 'CM_Service_EmailVerification_Standard',
        'arguments' => array(),
    );

    $config->services['memcache'] = array(
        'class'     => 'CM_Memcache_Client',
        'arguments' => array(
            'servers' => array(
                ['host' => 'localhost', 'port' => 11211],
            ),
        ),
    );

    $config->services['stream-message'] = array(
        'class'  => 'CM_MessageStream_Factory',
        'method' => [
            'name'      => 'createService',
            'arguments' => [
                'adapterClass'     => 'CM_MessageStream_Adapter_SocketRedis',
                'adapterArguments' => [
                    'servers' => [
                        ['httpHost' => 'localhost', 'httpPort' => 8085, 'sockjsUrls' => ['http://localhost:8090']],
                    ],
                ],
            ],
        ],
    );

    $config->services['elasticsearch'] = array(
        'class'     => 'CM_Elasticsearch_Cluster',
        'arguments' => array(
            'servers' => array(
                ['host' => 'localhost', 'port' => 9200],
            ),
        ),
    );

    $config->services['options'] = array(
        'class'     => 'CM_Options',
        'arguments' => array(),
    );

    $config->services['newrelic'] = array(
        'class'     => 'CMService_Newrelic',
        'arguments' => array(
            'enabled' => false,
            'appName' => 'CM Application',
        ),
    );

    $config->services['mailer'] = [
        'class'  => 'CM_Mail_MailerFactory',
        'method' => [
            'name'      => 'createLogMailer',
            'arguments' => [
                'logLevel' => CM_Log_Logger::INFO,
            ],
        ],
    ];

    $config->services['logger-handler-newrelic'] = [
        'class'     => 'CMService_NewRelic_Log_Handler',
        'arguments' => [
            'minLevel' => CM_Log_Logger::ERROR,
        ],
    ];

    $config->services['logger-handler-mongodb'] = [
        'class'  => 'CM_Log_Handler_Factory',
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
        'class'  => 'CM_Log_Handler_Factory',
        'method' => [
            'name'      => 'createFileHandler',
            'arguments' => [
                'path'     => 'logs/error.log',
                'minLevel' => CM_Log_Logger::DEBUG,
            ],
        ],
    ];

    $config->services['logger'] = [
        'class'  => 'CM_Log_Factory',
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
};
