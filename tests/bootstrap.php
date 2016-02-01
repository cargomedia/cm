<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';

// phpunit fail to serialize the Bootloader if it's has some unserializable object, so let's wrap it in a Closure to remove it from $_GLOBALS
call_user_func(function(){
    $bootloader = new CM_Bootloader_Testing(dirname(__DIR__) . '/');
    $bootloader->load();
});

$suite = new CMTest_TestSuite();
$suite->setDirTestData(__DIR__ . '/data/');
$suite->bootstrap();
