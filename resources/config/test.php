<?php

return function (CM_Config_Node $config) {
    $config->CM_Mail->send = false;

    $config->CM_Search->enabled = false;

    $config->CM_Db_Db->db = $config->CM_Db_Db->db . '_test';
    $config->CM_Db_Db->serversReadEnabled = false;
    $config->CM_Db_Db->delayedEnabled = false;

    $config->classConfigCacheEnabled = false;

    $config->CM_Model_Splittest->withoutPersistence = true;

    $config->CM_Model_Splitfeature->withoutPersistence = true;

    $config->CM_Jobdistribution_Job_Abstract->gearmanEnabled = false;

    $config->CMTest_TH->dropDatabase = true;
};
