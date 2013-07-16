<?php

$config->CM_Cache_Redis->server = array('host' => '10.10.10.100', 'port' => 6379);

$config->CM_Search->servers = array(
	array('host' => '10.10.10.105', 'port' => 9200),
);

$config->CM_Cache_Memcache->servers = array(
	array('host' => '10.10.10.100', 'port' => 11211),
);

$config->CM_Db_Db->db = 'skadate';
$config->CM_Db_Db->username = 'skadate';
$config->CM_Db_Db->password = 'k290DSkjsfiE02sDjj';
$config->CM_Db_Db->server = array('host' => '10.10.10.101', 'port' => 3306);
$config->CM_Db_Db->servers_read = array(
	array('host' => '127.0.0.1', 'port' => 4040),
);

$config->CM_Jobdistribution_JobWorker->servers = array(array('host' => 'localhost', 'port' => 4730));
$config->CM_Jobdistribution_Job_Abstract->servers = array(array('host' => 'localhost', 'port' => 4730));
