<?php

return function (CM_Config_Node $config) {
    $config->CM_App->setupScriptClasses = array();
    $config->CM_App->setupScriptClasses[] = 'CM_File_Filesystem_SetupScript';
    $config->CM_App->setupScriptClasses[] = 'CM_Db_SetupScript';
    $config->CM_App->setupScriptClasses[] = 'CM_MongoDb_SetupScript';
    $config->CM_App->setupScriptClasses[] = 'CM_Http_SetupScript';

    $config->CM_Db_Db->serversReadEnabled = false;
    $config->CM_Db_Db->delayedEnabled = false;

    $config->classConfigCacheEnabled = false;

    $config->services['database-master'] =
    $config->services['database-read'] =
    $config->services['database-read-maintenance'] = array(
        'class'     => 'CM_Db_Client',
        'arguments' => array(
            'config' => array(
                'host'     => 'localhost',
                'port'     => 3306,
                'username' => 'root',
                'password' => '',
                'db'       => 'cm_test',
            )
        )
    );

    $config->services['redis'] = array(
        'class'     => 'CM_Redis_Client',
        'arguments' => array(
            'config' => array(
                'host'     => 'localhost',
                'port'     => '6379',
                'database' => 2,
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
                    'pathPrefix' => DIR_ROOT . 'tests/tmp/data/',
                )
            )
        ));

    $config->services['filesystem-usercontent'] = array(
        'class'  => 'CM_File_Filesystem_Factory',
        'method' => array(
            'name'      => 'createFilesystem',
            'arguments' => array(
                'adapterClassName' => 'CM_File_Filesystem_Adapter_Local',
                'options'          => array(
                    'pathPrefix' => DIR_ROOT . 'tests/tmp/userfiles/',
                )
            )
        ));

    $config->services['elasticsearch'] = array(
        'class'     => 'CM_Elasticsearch_Cluster',
        'arguments' => array(
            'servers'  => array(
                ['host' => 'localhost', 'port' => 9200]
            ),
            'disabled' => true,
        ),
    );

    $config->services[CM_Jobdistribution_QueueInterface::class] = [
        'class'  => CMTest_Factory_JobDistribution::class,
        'method' => ['name' => 'createQueue'],
    ];

    $config->CMService_AwsS3Versioning_ClientTest->version = '2006-03-01';
    $config->CMService_AwsS3Versioning_ClientTest->region = 'eu-west-1';
    $config->CMService_AwsS3Versioning_ClientTest->key = null;
    $config->CMService_AwsS3Versioning_ClientTest->secret = null;
};
