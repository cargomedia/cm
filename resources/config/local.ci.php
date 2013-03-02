<?php

$config->CM_Cache_Redis->server = array('host' => '172.10.1.100', 'port' => 6379);

$config->CM_Search->servers = array(
	array('host' => '172.10.1.105', 'port' => 9200),
);

$config->CM_Cache_Memcache->servers = array(
	array('host' => '172.10.1.100', 'port' => 11211),
);

$config->CM_Mysql->db = 'skadate_test';
$config->CM_Mysql->user = 'skadate';
$config->CM_Mysql->pass = 'k290DSkjsfiE02sDjj';
$config->CM_Mysql->server = array('host' => '172.10.1.101', 'port' => 3306);
$config->CM_Mysql->servers_read = array(
	array('host' => '127.0.0.1', 'port' => 4040),
);

$config->CM_Db_Db->db = 'skadate_test';
$config->CM_Db_Db->username = 'skadate';
$config->CM_Db_Db->password = 'k290DSkjsfiE02sDjj';
$config->CM_Db_Db->server = array('host' => '172.10.1.101', 'port' => 3306);
$config->CM_Db_Db->servers_read = array(
	array('host' => '127.0.0.1', 'port' => 4040),
);

$config->CM_Jobdistribution_JobWorker->servers = array(array('host' => 'localhost', 'port' => 4730));
$config->CM_Jobdistribution_Job_Abstract->servers = array(array('host' => 'localhost', 'port' => 4730));
