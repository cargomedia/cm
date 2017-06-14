<?php

return function (CM_Config_Node $config) {
    $config->services['database-master'] =
    $config->services['database-read'] =
    $config->services['database-read-maintenance'] = array(
        'class'     => 'CM_Db_Client',
        'arguments' => array(
            'config' => array(
                'host'     => 'mysql',
                'port'     => 3306,
                'username' => 'root',
                'password' => 'docker',
                'db'       => 'cm_test',
            )
        )
    );

    $config->services['MongoDb'] = [
        'class'     => CM_MongoDb_Client::class,
        'arguments' => [
            'config' => [
                'db'     => 'cm',
                'server' => 'mongodb://mongo:27017',
            ],
        ],
    ];

    $config->services['redis'] = array(
        'class'     => 'CM_Redis_Client',
        'arguments' => array(
            'config' => array(
                'host'     => 'redis',
                'port'     => '6379',
                'database' => 2,
            )
        )
    );

    $config->services['elasticsearch'] = array(
        'class'     => 'CM_Elasticsearch_Cluster',
        'arguments' => array(
            'servers'  => array(
                [
                    'host' => 'elasticsearch',
                    'port' => 9200,
                ]
            ),
            'disabled' => true,
        ),
    );

    $config->services['memcache'] = [
        'class'     => CM_Memcache_Client::class,
        'arguments' => [
            'servers' => [
                [
                    'host' => 'memcached',
                    'port' => 11211,
                ],
            ],
        ],
    ];
};
