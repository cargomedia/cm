<?php
/**
 * Updates the search index with the new values
 *
 * First fetches and merges all ids from the redis server
 * Then fetches the objects from the database server
 */

$host = null;
$port = null;

while ($arg = array_shift($argv)) {
	switch ($arg) {
		case '-h':
			$host = array_shift($argv);
			break;
		case '-p':
			$port = array_shift($argv);
			break;
		case '-i':
			$indexName = array_shift($argv);
			break;
		case '-t':
			define('IS_TEST', true);
			break;
	}
}

define("IS_CRON", true);
require_once dirname(dirname(__DIR__)) . '/library/CM/Bootloader.php';
$bootloader = new CM_Bootloader(dirname(dirname(__DIR__)) . '/', null);
$bootloader->load(array('autoloader', 'constants', 'exceptionHandler', 'errorHandler', 'defaults'));

$indexes = array('photo' => new SK_Elastica_Type_Photo($host, $port), 'user' => new SK_Elastica_Type_User($host, $port),
	'video' => new SK_Elastica_Type_Video($host, $port), 'blogpost' => new SK_Elastica_Type_Blogpost($host, $port));

if (isset($indexName)) {
	$indexes = array_filter($indexes, function (CM_Elastica_Type_Abstract $index) use ($indexName) {
		return $index->getIndex()->getName() == $indexName;
	});
	if (empty($indexes)) {
		echo 'ERROR: No such index: ' . $indexName . PHP_EOL;
		exit(1);
	}
}

/** @var CM_Elastica_Type_Abstract $index */
foreach ($indexes as $queueName => $index) {
	try {
		$updateIds = getQueue('Updates_' . $queueName);
		$index->update($updateIds);
	} catch (Exception $e) {
		echo $queueName . '-updates failed.' . PHP_EOL;
		if (isset($updateIds)) {
			echo 'Re-adding ' . count($updateIds) . ' ids to queue.' . PHP_EOL;
			addToQueue('Updates_' . $queueName, $updateIds);
		}
		echo '(' . $e->getMessage() . ')' . PHP_EOL;
		exit(1);
	}
}

function getQueue($queueName) {
	$key = 'Search.' . $queueName;
	$ids = CM_Cache_Redis::sFlush($key);
	return array_filter(array_unique($ids));
}

function addToQueue($queueName, array $ids) {
	foreach ($ids as $id) {
		CM_Cache_Redis::sAdd('Search.' . $queueName, $id);
	}
}
