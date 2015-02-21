<?php

return function (CM_Config_Node $config) {
    $config->CM_Redis_Client->server = array('host' => 'localhost', 'port' => 6379);
    $config->CM_Elasticsearch_Client->servers = array(
        array('host' => 'localhost', 'port' => 9200),
    );
};
