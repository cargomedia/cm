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
$config->CM_Mail->send = true;
$config->CM_Mail->siteName = 'CM';
$config->CM_Mail->siteEmailAddress = 'noreply@example.com';

$config->CM_Site_Abstract = new stdClass();
$config->CM_Site_Abstract->class = 'CM_Site_CM';

$config->CM_Tracking_Abstract = new stdClass();
$config->CM_Tracking_Abstract->class = 'CM_Tracking';
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

$config->CM_Stream = new stdClass();
$config->CM_Stream->enabled = true;

$config->CM_StreamAdapter_Abstract = new stdClass();
$config->CM_StreamAdapter_Abstract->class = 'CM_StreamAdapter_SockJS';
$config->CM_StreamAdapter_Abstract->hostPrefix = true;
$config->CM_StreamAdapter_Abstract->servers = array(
	array('host' => 'localhost', 'port' => 8090),
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
$config->CM_Mail->siteName = 'Example';
$config->CM_Mail->siteEmailAddress = 'example@example.dev';

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
$config->CM_Wowza->httpPort = '8086';
$config->CM_Wowza->wowzaPort = '1935';
$config->CM_Wowza->servers = array(
	array('publicHost' => 'localhost', 'publicIp' => '127.0.0.1', 'privateIp' => '127.0.0.1'),
);

$config->CM_Site_CM = new stdClass();
$config->CM_Site_CM->url = 'http://www.example.dev';
$config->CM_Site_CM->urlCdn = 'http://cdn.example.dev';

$config->CM_Amazon_Abstract = new stdClass();
$config->CM_Amazon_Abstract->accessKey = '';
$config->CM_Amazon_Abstract->secretKey = '';

$config->CM_KissTracking = new stdClass();
$config->CM_KissTracking->enabled = false;
$config->CM_KissTracking->awsBucketName = '';
$config->CM_KissTracking->awsFilePrefix = '';

$config->CM_Adprovider->enabled = true;
$config->CM_Adprovider->zones = array();

$config->CM_AdproviderAdapter_Abstract->class = 'CM_AdproviderAdapter_Openx';
$config->CM_AdproviderAdapter_Openx->host = 'www.example.dev';

$config->CM_JobManager = new stdClass();
$config->CM_JobManager->workerCount = 5;

$config->CM_JobWorker = new stdClass();
$config->CM_JobWorker->servers = array(array('host' => 'localhost', 'port' => 4730));

$config->CM_Job_Abstract = new stdClass();
$config->CM_Job_Abstract->gearmanEnabled = true;
$config->CM_Job_Abstract->servers = array(array('host' => 'localhost', 'port' => 4730));

$config->CM_Model_Splittest->forceAllVariations = false;