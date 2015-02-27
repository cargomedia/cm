<?php

return function (CM_Config_Node $config) {
    $config->CM_Elasticsearch_Client->servers = array(
        array('host' => 'localhost', 'port' => 9200),
    );
};
