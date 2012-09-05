<?php

$config->debug = true;

$config->CM_Render->cdnResource = false;
$config->CM_Render->cdnUserContent = false;

$config->CM_Language = new stdClass();
$config->CM_Language->idDefault = 1;
$config->CM_Language->autoCreate = true;

$config->CM_Stream->enabled = true;

$config->CM_KissTracking_Api->enabled = true;
$config->CM_KissTracking_Api->awsAccessKey = '';
$config->CM_KissTracking_Api->awsSecretKey = '';
$config->CM_KissTracking_Api->awsBucketName = '';
$config->CM_KissTracking_Api->s3FilePrefix = 'cargomedia';

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

$config->CM_Response_View_Abstract->catch = array(
	'CM_Exception_AuthRequired',
	'CM_Exception_Blocked',
	'CM_Exception_ActionLimit',
	'CM_Exception_Nonexistent',
);
