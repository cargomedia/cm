<?php

return function (CM_Config_Node $config) {
    $config->CM_Mail->send = false;

    $config->CM_Elasticsearch_Client->enabled = false;

    $config->CM_Db_Db->db = $config->CM_Db_Db->db . '_test';
    $config->CM_Db_Db->serversReadEnabled = false;
    $config->CM_Db_Db->delayedEnabled = false;

    $config->classConfigCacheEnabled = false;

    $config->CM_Model_Splittest->withoutPersistence = true;

    $config->CM_Model_Splitfeature->withoutPersistence = true;

    $config->CM_Jobdistribution_Job_Abstract->gearmanEnabled = false;

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

    $config->services['filesystem-userfiles'] = array(
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

    $config->services['filesystem-userfiles-tmp'] = array(
        'class'  => 'CM_File_Filesystem_Factory',
        'method' => array(
            'name'      => 'createFilesystem',
            'arguments' => array(
                'CM_File_Filesystem_Adapter_Local',
                array(
                    'pathPrefix' => DIR_ROOT . 'tests/tmp/userfiles/tmp/',
                )
            ),
        ));

    $config->CMService_AwsS3Versioning_ClientTest->region = 'eu-west-1';
    $config->CMService_AwsS3Versioning_ClientTest->key = null;
    $config->CMService_AwsS3Versioning_ClientTest->secret = null;
};
