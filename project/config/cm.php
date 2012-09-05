<?php

$config->debug = false;
$config->dirData = null;
$config->dirTmp = null;
$config->dirUserfiles = null;
$config->timeZone = 'US/Central';
$config->testIp = '162.23.39.73';

$config->CM_Render = new stdClass();
$config->CM_Render->cdnResource = true;
$config->CM_Render->cdnUserContent = true;

$config->CM_Mail = new stdClass();
$config->CM_Mail->siteName = 'CM';
$config->CM_Mail->siteEmailAddress = 'noreply@example.com';

$config->CM_Site_Abstract = new stdClass();
$config->CM_Site_Abstract->class = 'CM_Site_CM';

$config->CM_Tracking_Abstract = new stdClass();
$config->CM_Tracking_Abstract->enabled = false;
$config->CM_Tracking_Abstract->code = '';

$config->CM_Splittesting_Abstract = new stdClass();
$config->CM_Splittesting_Abstract->enabled = false;

$config->CM_Search = new stdClass();
$config->CM_Search->enabled = true;
$config->CM_Search->servers = array(
	array('host' => 'localhost', 'port' => 9200),
);

$config->CM_Cache_Runtime = new stdClass;
$config->CM_Cache_Runtime->enabled = true;

$config->CM_Cache_Apc = new stdClass;
$config->CM_Cache_Apc->enabled = true;
$config->CM_Cache_Apc->lifetime = 86400;

$config->CM_Cache_Memcache = new stdClass();
$config->CM_Cache_Memcache->enabled = true;
$config->CM_Cache_Memcache->lifetime = 3600;
$config->CM_Cache_Memcache->servers = array(
	array('host' => 'localhost', 'port' => 11211),
);

$config->CM_Cache_Redis = new stdClass;
$config->CM_Cache_Redis->enabled = true;
$config->CM_Cache_Redis->server = array('host' => 'localhost', 'port' => 6379);

$config->CM_Language = new stdClass();
$config->CM_Language->idDefault = 1;
$config->CM_Language->autoCreate = false;

$config->CM_Stream = new stdClass();
$config->CM_Stream->enabled = true;

$config->CM_StreamAdapter_Abstract = new stdClass();
$config->CM_StreamAdapter_Abstract->class = 'CM_StreamAdapter_Apache';
$config->CM_StreamAdapter_Abstract->hostPrefix = false;
$config->CM_StreamAdapter_Abstract->servers = array(
	array('host' => 'localhost', 'port' => 80),
);

$config->CM_Mysql = new stdClass();
$config->CM_Mysql->db = 'cm';
$config->CM_Mysql->user = 'root';
$config->CM_Mysql->pass = 'root';
$config->CM_Mysql->server = array('host' => 'localhost', 'port' => 3306);
$config->CM_Mysql->servers_read = array();

$config->CM_Action_Abstract = new stdClass();

$config->CM_Model_Abstract = new stdClass();

$config->CM_Model_ActionLimit_Abstract = new stdClass();

$config->CM_Model_Entity_Abstract = new stdClass();

$config->CM_Mail = new stdClass();

$config->CM_Paging_Log_Abstract = new stdClass();

$config->CM_Paging_ContentList_Abstract = new stdClass();

$config->CM_Model_User = new stdClass();
$config->CM_Model_User->class = 'CM_Model_User';

$config->CM_Params = new stdClass();
$config->CM_Params->class = 'CM_Params';

$config->CM_Response_Page = new stdClass();
$config->CM_Response_Page->catch = array(
	'CM_Exception_Nonexistent' => '/error/not-found',
	'CM_Exception_InvalidParam' => '/error/not-found',
	'CM_Exception_AuthRequired' => '/error/auth-required',
	'CM_Exception_NotAllowed' => '/error/not-allowed',
);

$config->CM_Response_View_Abstract = new stdClass();
$config->CM_Response_View_Abstract->catch = array(
	'CM_Exception_AuthRequired',
	'CM_Exception_Blocked',
	'CM_Exception_ActionLimit',
	'CM_Exception_Nonexistent',
);

$config->CM_Response_RPC = new stdClass();
$config->CM_Response_RPC->catch = array(
	'CM_Exception_AuthRequired',
	'CM_Exception_NotAllowed',
);

$config->CM_Model_DeviceCapabilities = new stdClass();
$config->CM_Model_DeviceCapabilities->adapter = 'CM_DeviceCapabilitiesAdapter_Wurfl';

$config->CM_Model_StreamChannel_Abstract = new stdClass();

$config->CM_Wowza = new stdClass();

$config->CM_Site_CM = new stdClass();
$config->CM_Site_CM->url = 'http://www.example.dev';
$config->CM_Site_CM->urlCdn = 'http://cdn.example.dev';


$config->CM_KissTracking_Api = new stdClass();
$config->CM_KissTracking_Api->csvFile = 'kissmetrics.csv';