<?php

return function (CM_Config_Node $config) {
    $config->CM_App->setupScriptClasses = array();
    $config->CM_App->setupScriptClasses[] = 'CM_File_Filesystem_SetupScript';
    $config->CM_App->setupScriptClasses[] = 'CM_Db_SetupScript';
    $config->CM_App->setupScriptClasses[] = 'CM_MongoDb_SetupScript';
    $config->CM_App->setupScriptClasses[] = 'CM_App_SetupScript_Core';

    $config->CM_Mail->send = false;

    $config->CM_Elasticsearch_Client->enabled = false;

    $config->CM_Db_Db->serversReadEnabled = false;
    $config->CM_Db_Db->delayedEnabled = false;

    $config->classConfigCacheEnabled = false;

    $config->CM_Jobdistribution_Job_Abstract->gearmanEnabled = false;

    $config->services['database-master'] =
    $config->services['database-read'] =
    $config->services['database-read-maintenance'] = array(
        'class'     => 'CM_Db_Client',
        'arguments' => array(
            array(
                'host'             => 'localhost',
                'port'             => 3306,
                'username'         => 'root',
                'password'         => '',
                'db'               => 'cm_test',
                'reconnectTimeout' => 300
            )
        )
    );

    $config->services['filesystem-data'] = array(
        'class'  => 'CM_File_Filesystem_Factory',
        'method' => array(
            'name'      => 'createFilesystem',
            'arguments' => array(
                'CM_File_Filesystem_Adapter_Local',
                array(
                    'pathPrefix' => DIR_ROOT . 'tests/tmp/data/',
                )
            ),
        ));

    $config->services['filesystem-usercontent'] = array(
        'class'  => 'CM_File_Filesystem_Factory',
        'method' => array(
            'name'      => 'createFilesystem',
            'arguments' => array(
                'CM_File_Filesystem_Adapter_Local',
                array(
                    'pathPrefix' => DIR_ROOT . 'tests/tmp/userfiles/',
                )
            ),
        ));

    $config->CMService_AwsS3Versioning_ClientTest->region = 'eu-west-1';
    $config->CMService_AwsS3Versioning_ClientTest->key = null;
    $config->CMService_AwsS3Versioning_ClientTest->secret = null;
};
