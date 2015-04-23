<?php

return function (CM_Config_Node $config) {
    $config->CM_App->setupScriptClasses = array();
    $config->CM_App->setupScriptClasses[] = 'CM_File_Filesystem_SetupScript';
    $config->CM_App->setupScriptClasses[] = 'CM_Db_SetupScript';
    $config->CM_App->setupScriptClasses[] = 'CM_MongoDb_SetupScript';
    $config->CM_App->setupScriptClasses[] = 'CM_Elasticsearch_SetupScript';
    $config->CM_App->setupScriptClasses[] = 'CM_Http_SetupScript';
    $config->CM_App->setupScriptClasses[] = 'CM_App_SetupScript_Translations';

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
        'CM_Exception_AuthRequired' => [],
        'CM_Exception_NotAllowed'   => [],
    );
    $config->CM_Http_Response_RPC->catchPublicExceptions = true;

    $config->CM_Stream_Video->adapter = 'CM_Stream_Adapter_Video_Wowza';
    $config->CM_Stream_Video->servers = array(
        array('publicHost' => 'localhost', 'publicIp' => '127.0.0.1', 'privateIp' => '127.0.0.1'),
    );

    $config->CM_Stream_Adapter_Video_Wowza->httpPort = '8086';
    $config->CM_Stream_Adapter_Video_Wowza->wowzaPort = '1935';

    $config->CM_Adprovider->enabled = true;
    $config->CM_Adprovider->zones = array();

    $config->CM_AdproviderAdapter_Abstract->class = 'CM_AdproviderAdapter_Openx';
    $config->CM_AdproviderAdapter_Openx->host = 'www.example.dev';

    $config->CM_Jobdistribution_JobWorker->servers = array(array('host' => 'localhost', 'port' => 4730));

    $config->CM_Jobdistribution_Job_Abstract->gearmanEnabled = true;
    $config->CM_Jobdistribution_Job_Abstract->servers = array(array('host' => 'localhost', 'port' => 4730));

    $config->CMService_MaxMind->licenseKey = null;

    $config->CMService_Newrelic->enabled = false;
    $config->CMService_Newrelic->appName = 'CM Application';

    $config->services = array();

    $config->services['databases'] = array(
        'class' => 'CM_Service_Databases',
    );

    $config->services['database-master'] = array(
        'class'     => 'CM_Db_Client',
        'arguments' => array(
            array(
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
            array(
                'db'      => 'cm',
                'server'  => 'mongodb://localhost:27017',
                'options' => array('connect' => true),
            )
        ),
    );

    $config->services['redis'] = array(
        'class'     => 'CM_Redis_Client',
        'arguments' => array(
            array(
                'host' => 'localhost',
                'port' => '6379',
            )
        ),
    );

    $config->services['filesystem-data'] = array(
        'class'  => 'CM_File_Filesystem_Factory',
        'method' => array(
            'name'      => 'createFilesystem',
            'arguments' => array(
                'CM_File_Filesystem_Adapter_Local',
                array(
                    'pathPrefix' => DIR_ROOT . 'data/',
                )
            ),
        )
    );

    $config->services['filesystem-usercontent'] = array(
        'class'  => 'CM_File_Filesystem_Factory',
        'method' => array(
            'name'      => 'createFilesystem',
            'arguments' => array(
                'CM_File_Filesystem_Adapter_Local',
                array(
                    'pathPrefix' => DIR_PUBLIC . 'userfiles/',
                )
            ),
        )
    );

    $config->services['usercontent'] = array(
        'class'     => 'CM_Service_UserContent',
        'arguments' => array(array(
            'default' => array(
                'filesystem' => 'filesystem-usercontent',
                'url'        => 'http://localhost/userfiles',
            ),
        ))
    );

    $config->services['trackings'] = array(
        'class'     => 'CM_Service_Trackings',
        'arguments' => array(array())
    );

    $config->services['tracking-googleanalytics'] = array(
        'class'     => 'CMService_GoogleAnalytics_Client',
        'arguments' => array('my-web-property-id')
    );

    $config->services['tracking-kissmetrics'] = array(
        'class'     => 'CMService_KissMetrics_Client',
        'arguments' => array('my-api-key')
    );

    $config->services['email-verification'] = array(
        'class'     => 'CM_Service_EmailVerification_Standard',
        'arguments' => array()
    );

    $config->services['memcache'] = array(
        'class'     => 'CM_Memcache_Client',
        'arguments' => array(
            array(
                ['host' => 'localhost', 'port' => 11211],
            ),
        ),
    );

    $config->services['stream-message'] = array(
        'class'     => 'CM_MessageStream_Service',
        'arguments' => array(
            'enabled' => true,
            'adapter' => array(
                'class' => 'CM_Stream_Adapter_Message_SocketRedis',
                'arguments'   => array(
                    'servers' => array(
                        ['httpHost' => 'localhost', 'httpPort' => 8085, 'sockjsUrls' => ['http://localhost:8090']],
                    ),
                )
            ),
        ),
    );

    $config->services['elasticsearch'] = array(
        'class'     => 'CM_Elasticsearch_Cluster',
        'arguments' => array(
            array(
                ['host' => 'localhost', 'port' => 9200]
            ),
        ),
    );

    $config->services['options'] = array(
        'class'     => 'CM_Options',
        'arguments' => array(),
    );
};
