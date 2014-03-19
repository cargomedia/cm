<?php

return function (CM_Config_Node $config) {
    $config->CM_Redis_Client->server = array('host' => 'localhost', 'port' => 6379);
    $config->CM_Search->servers = array(
        array('host' => 'localhost', 'port' => 9200),
    );
    $config->CM_Memcache_Client->servers = array(
        array('host' => 'localhost', 'port' => 11211),
    );
    $config->CM_Db_Db->db = 'cm';
    $config->CM_Db_Db->username = 'travis';
    $config->CM_Db_Db->password = '';
    $config->CM_Db_Db->server = array('host' => 'localhost', 'port' => 3306);
};
