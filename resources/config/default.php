<?php

return function (CM_Config_Node $config) {
    $config->CM_App->setupScriptClasses = array();
    $config->CM_App->setupScriptClasses[] = 'CM_File_Filesystem_SetupScript';
    $config->CM_App->setupScriptClasses[] = 'CM_Db_SetupScript';
    $config->CM_App->setupScriptClasses[] = 'CM_MongoDb_SetupScript';
    $config->CM_App->setupScriptClasses[] = 'CM_Elasticsearch_SetupScript';
    $config->CM_App->setupScriptClasses[] = 'CM_Http_SetupScript';
    $config->CM_App->setupScriptClasses[] = 'CM_App_SetupScript_Translations';
    $config->CM_App->setupScriptClasses[] = 'CM_App_SetupScript_Currency';

    $config->timeZone = 'UTC';

    $config->CM_Mail->send = true;
    $config->CM_Mail->mailDeliveryAgent = null;

    $config->CM_Site_Abstract->class = null;

    $config->CM_Cache_Local->storage = 'CM_Cache_Storage_Apc';
    $config->CM_Cache_Local->lifetime = 86400;

    $config->CM_Cache_Shared->storage = 'CM_Cache_Storage_Memcache';
    $config->CM_Cache_Shared->lifetime = 3600;

    $config->CM_Paging_Ip_Blocked->maxAge = (7 * 86400);

    $config->classConfigCacheEnabled = true;

    $config->CM_Db_Db->delayedEnabled = true;

    $config->CM_MongoDb_Client->batchSize = null;

    $config->CM_Model_User->class = 'CM_Model_User';

    $config->CM_Params->class = 'CM_Params';

    $config->CM_Usertext_Usertext->class = 'CM_Usertext_Usertext';

    $config->CM_Model_Currency->default = ['code' => '840', 'abbreviation' => 'USD'];

    $config->CM_Http_Response_Page->exceptionsToCatch = array(
        'CM_Exception_Nonexistent'  => ['errorPage' => 'CM_Page_Error_NotFound', 'log' => 'CM_Paging_Log_NotFound'],
        'CM_Exception_InvalidParam' => ['errorPage' => 'CM_Page_Error_NotFound', 'log' => 'CM_Paging_Log_NotFound'],
        'CM_Exception_AuthRequired' => ['errorPage' => 'CM_Page_Error_AuthRequired', 'log' => null],
        'CM_Exception_NotAllowed'   => ['errorPage' => 'CM_Page_Error_NotAllowed', 'log' => null],
    );

    $config->CM_Http_Response_View_Abstract->exceptionsToCatch = array(
        'CM_Exception_Nonexistent'  => ['log' => 'CM_Paging_Log_NotFound'],
        'CM_Exception_InvalidParam' => ['log' => 'CM_Paging_Log_NotFound'],
        'CM_Exception_AuthRequired' => [],
        'CM_Exception_NotAllowed'   => [],
        'CM_Exception_Blocked'      => [],
        'CM_Exception_ActionLimit'  => [],
    );
    $config->CM_Http_Response_View_Abstract->catchPublicExceptions = true;

    $config->CM_Http_Response_RPC->exceptionsToCatch = array(
        'CM_Exception_InvalidParam' => [],
        'CM_Exception_AuthRequired' => [],
        'CM_Exception_NotAllowed'   => [],
    );
    $config->CM_Http_Response_RPC->catchPublicExceptions = true;

    $config->CM_Adprovider->enabled = true;
    $config->CM_Adprovider->zones = array();

    $config->CM_AdproviderAdapter_Abstract->class = 'CM_AdproviderAdapter_Openx';
    $config->CM_AdproviderAdapter_Openx->host = 'www.example.dev';

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
            )
        )
    );

    $config->services['MongoDb'] = array(
        'class'     => 'CM_MongoDb_Client',
        'arguments' => array(
            'config' => array(
                'db'      => 'cm',
                'server'  => 'mongodb://localhost:27017',
                'options' => array('connect' => true),
            )
        )
    );

    $config->services['redis'] = array(
        'class'     => 'CM_Redis_Client',
        'arguments' => array(
            'config' => array(
                'host' => 'localhost',
                'port' => '6379',
            )
        )
    );

    $config->services['filesystem-data'] = array(
        'class'  => 'CM_File_Filesystem_Factory',
        'method' => array(
            'name'      => 'createFilesystem',
            'arguments' => array(
                'adapterClassName' => 'CM_File_Filesystem_Adapter_Local',
                'options'          => array(
                    'pathPrefix' => DIR_ROOT . 'data/',
                )
            )
        )
    );

    $config->services['filesystem-usercontent'] = array(
        'class'  => 'CM_File_Filesystem_Factory',
        'method' => array(
            'name'      => 'createFilesystem',
            'arguments' => array(
                'adapterClassName' => 'CM_File_Filesystem_Adapter_Local',
                'options'          => array(
                    'pathPrefix' => DIR_PUBLIC . 'userfiles/',
                )
            )
        )
    );

    $config->services['usercontent'] = array(
        'class'     => 'CM_Service_UserContent',
        'arguments' => array(
            'configList' => array(
                'default' => array(
                    'filesystem' => 'filesystem-usercontent',
                    'url'        => 'http://localhost/userfiles',
                ),
            )
        )
    );

    $config->services['trackings'] = array(
        'class'     => 'CM_Service_Trackings',
        'arguments' => array(
            'trackingServiceNameList' => array()
        )
    );

    $config->services['tracking-adagnit'] = [
        'class'     => 'CMService_Adagnit_Client',
        'arguments' => [
            'ttl' => 86400,
        ],
    ];

    $config->services['tracking-googleanalytics'] = array(
        'class'     => 'CMService_GoogleAnalytics_Client',
        'arguments' => array(
            'code' => 'my-web-property-id',
            'ttl'  => 86400,
        )
    );

    $config->services['tracking-kissmetrics'] = array(
        'class'     => 'CMService_KissMetrics_Client',
        'arguments' => array(
            'code' => 'my-api-key',
        )
    );

    $config->services['tracking-inspectlet'] = array(
        'class'     => 'CMService_Inspectlet_Client',
        'arguments' => array(
            'code' => 'my-wid',
        )
    );

    $config->services['email-verification'] = array(
        'class'     => 'CM_Service_EmailVerification_Standard',
        'arguments' => array()
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
        ]
    );

    $config->services['elasticsearch'] = array(
        'class'     => 'CM_Elasticsearch_Cluster',
        'arguments' => array(
            'servers' => array(
                ['host' => 'localhost', 'port' => 9200]
            ),
        ),
    );

    $config->services['options'] = array(
        'class'     => 'CM_Options',
        'arguments' => array(),
    );

    $config->services['stream-video'] = array(
        'class'  => 'CM_VideoStream_Factory',
        'method' => array(
            'name'      => 'createService',
            'arguments' => array(
                'adapterClass'     => 'CM_VideoStream_Adapter_Wowza',
                'adapterArguments' => array(
                    'servers' => array(
                        ['publicHost' => 'localhost', 'publicIp' => '127.0.0.1', 'privateIp' => '127.0.0.1'],
                    ),
                    'config'  => array(
                        'httpPort'  => '8086',
                        'wowzaPort' => '1935'
                    ),
                )
            )
        )
    );

    $config->services['newrelic'] = array(
        'class'     => 'CMService_Newrelic',
        'arguments' => array(
            'enabled' => false,
            'appName' => 'CM Application',
        )
    );
};
