<?php
/**
 * Optimize indexes
 */

define("IS_CRON", true);
define('DIR_ROOT', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR);
require_once DIR_ROOT . 'library/CM/Bootloader.php';
CM_Bootloader::load(array('autoloader', 'constants', 'exceptionHandler', 'errorHandler', 'defaults'));

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
