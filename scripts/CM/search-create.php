<?php
/**
 * Create and fill indexes
 */

chdir(realpath(__DIR__));

while ($arg = array_shift($argv)) {
	switch ($arg) {
		case '-t':
			define('IS_TEST', true);
			break;
		case '-i':
			$indexName = array_shift($argv);
			break;
	}
}

define("IS_CRON", true);
require_once dirname(dirname(__DIR__)) . '/library/CM/Bootloader.php';
$bootloader = new CM_Bootloader(dirname(dirname(__DIR__)) . '/', null);
$bootloader->load(array('autoloader', 'constants', 'exceptionHandler', 'errorHandler', 'defaults'));

$indexes = array(new SK_Elastica_Type_Photo(), new SK_Elastica_Type_User(), new SK_Elastica_Type_Video(), new SK_Elastica_Type_Blogpost(),
	new SK_Elastica_Type_Location());

if (isset($indexName)) {
	$indexes = array_filter($indexes, function(CM_Elastica_Type_Abstract $index) use ($indexName) {
		return $index->getIndex()->getName() == $indexName;
	});
	if (empty($indexes)) {
		echo 'ERROR: No such index: ' . $indexName . PHP_EOL;
		exit(1);
	}
}

/** @var CM_Elastica_Type_Abstract $index */
foreach ($indexes as $index) {
	$index->createVersioned();
}
