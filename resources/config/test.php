<?php

$config->dirData = DIR_TESTS . 'tmp' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR;
$config->dirTmp = DIR_TESTS . 'tmp' . DIRECTORY_SEPARATOR;
$config->dirUserfiles = DIR_TESTS . 'tmp' . DIRECTORY_SEPARATOR . 'userfiles' . DIRECTORY_SEPARATOR;

$config->CM_Mail->send = false;

$config->CM_Mysql->db = $config->CM_Mysql->db . '_test';

$config->CM_Model_DeviceCapabilities->adapter = null;

$config->CM_Model_Splittest->withoutPersistence = true;

$config->CM_Model_Splitfeature->withoutPersistence = true;
