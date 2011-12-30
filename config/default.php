<?php

CM_Config::load('cm.php');

$config = CM_Config::get();

$config->modified = '99999999';
//$config->objects_cdn = 'http://cdn.example.dev/';
//$config->content_cdn = 'http://cdn.example.dev/';
$config->debug = true;
$config->languageCreate = true;
$config->timeZone = 'US/Central';
$config->testIp = '162.23.39.73';

$config->CM_Language = new stdClass();
$config->CM_Language->idDefault = 1;
$config->CM_Language->autoCreate = true;

$config->CM_Stream->enabled = true;

$config->CM_StreamAdapter_Abstract->hostPrefix = false;
$config->CM_StreamAdapter_Abstract->servers = array(
		array('host' => 'www.example.dev', 'port' => 80),
	);


$config->CM_Mysql->db = 'example';
$config->CM_Mysql->user = 'root';
$config->CM_Mysql->pass = 'root';

$config->CM_Search->enabled = false;

$config->CM_Cache_Runtime->enabled = true;
$config->CM_Cache_Apc->enabled = true;
$config->CM_Cache_Memcache->enabled = true;
$config->CM_Cache_Redis->enabled = true;

$config->CM_Mail->siteName = 'Example';
$config->CM_Mail->siteEmailAddress = 'example@example.dev';

$config->CM_Model_User->class = 'CM_Model_User';

$config->CM_Response_RPC->catch = array(
		'CM_Exception_AuthRequired',
	);

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
/*$config->CM_Site_Abstract->types[Cargomedia_Site_Cargomedia::TYPE] = 'Cargomedia_Site_Cargomedia';

*/