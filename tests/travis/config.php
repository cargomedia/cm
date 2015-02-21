<?php

return function (CM_Config_Node $config) {
    $config->CM_Memcache_Client->servers = array(
        array('host' => 'localhost', 'port' => 11211),
    );
};
