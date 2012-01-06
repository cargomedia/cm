<?php

CM_Config::load('cm.php');

$config->debug = true;
//$config->urlCdnObjects = 'http://cdn.example.dev/';
//$config->urlCdnContent = 'http://cdn.example.dev/';

$config->CM_Language = new stdClass();
$config->CM_Language->idDefault = 1;
$config->CM_Language->autoCreate = true;

$config->CM_Stream->enabled = true;

$config->CM_StreamAdapter_Abstract->class = 'CM_StreamAdapter_Socketio';
$config->CM_StreamAdapter_Abstract->hostPrefix = false;
$config->CM_StreamAdapter_Abstract->servers = array(
	array('host' => 'www.example.dev', 'port' => 8090),
);

$config->CM_Mysql->db = 'cm';
$config->CM_Mysql->user = 'root';
$config->CM_Mysql->pass = 'root';

$config->CM_Search->enabled = false;

$config->CM_Cache_Runtime->enabled = true;
$config->CM_Cache_Apc->enabled = true;
$config->CM_Cache_Memcache->enabled = true;
$config->CM_Cache_Redis->enabled = true;

$config->CM_Mail->siteName = 'Example';
$config->CM_Mail->siteEmailAddress = 'example@example.dev';

$config->CM_Response_Page->catch = array(
	'CM_Exception_Nonexistent' => '/error/not-found',
	'CM_Exception_InvalidParam' => '/error/not-found',
	'CM_Exception_AuthRequired' => '/account/signup',
);

$config->CM_Response_Component_Abstract->catch = array(
	'CM_Exception_AuthRequired',
	'CM_Exception_Blocked',
	'CM_Exception_ActionLimit',
	'CM_Exception_Nonexistent',
);

$config->CM_Site_Abstract->class = 'CM_Site_CM';
//$config->CM_Site_Abstract->types[CM_Site_CM::TYPE] = 'CM_Site_CM';
