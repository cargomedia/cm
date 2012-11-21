<?php
/**
 * Optimize indexes
 */

define("IS_CRON", true);
require_once dirname(dirname(__DIR__)) . '/library/CM/Bootloader.php';
$bootloader = new CM_Bootloader(dirname(dirname(__DIR__)) . '/', null);
$bootloader->load(array('autoloader', 'constants', 'exceptionHandler', 'errorHandler', 'defaults'));

$servers = CM_Config::get()->CM_Search->servers;
$server = $servers[array_rand($servers)];
if (!$host) {
	$host = $server['host'];
}
if (!$port) {
	$port = $server['port'];
}
$client = new Elastica_Client(array('host' => $host, 'port' => $port));
$client->optimizeAll();
