<?php

class CM_Search_Index_Cli extends CM_Cli_Runnable_Abstract {

	/**
	 * @param string|null $indexName
	 * @param boolean|null $test
	 */
	public function create($indexName = null, $test = null) {
		if ($indexName) {
			$indexes = array($this->_getIndex($indexName));
		} else {
			$indexes = $this->_getIndexes();
		}
		foreach ($indexes as $index) {
			$index->createVersioned();
		}
	}

	/**
	 * @param string|null $indexName
	 * @param string|null $test
	 * @param string|null $port
	 * @param string|null $host
	 * @throws CM_Exception_Invalid
	 * @return void
	 */
	public function update($indexName = null, $test = null, $port = null, $host = null) {
		if ($indexName) {
			$indexes = array($this->_getIndex($indexName));
		} else {
			$indexes = $this->_getIndexes();
		}
		foreach ($indexes as $index) {
			$indexName = $index->getIndex()->getName();;
			try {
				$key = 'Search.Updates_' . $indexName;
				$ids = CM_Cache_Redis::sFlush($key);
				$ids = array_filter(array_unique($ids));
				$index->update($ids);
			} catch (Exception $e) {
				echo $indexName . '-updates failed.' . PHP_EOL;
				if (isset($ids)) {
					echo 'Re-adding ' . count($ids) . ' ids to queue.' . PHP_EOL;
					foreach ($ids as $id) {
						CM_Cache_Redis::sAdd('Search.Updates_' . $indexName, $id);
					}
				}
				throw new CM_Exception_Invalid('Update failed');
			}
		}
	}

	public function optimize() {
		$servers = CM_Config::get()->CM_Search->servers;
		$client = new Elastica_Client($servers);
		$client->optimizeAll();
	}



	public static function getPackageName() {
		return 'search-index';
	}

	/**
	 * @return CM_Elastica_Type_Abstract[]
	 */
	private function _getIndexes() {
		$indexTypes = CM_Util::getClassChildren('CM_Elastica_Type_Abstract');
		return array_map(function ($indexType) {
			return new $indexType();
		}, $indexTypes);
	}

	/**
	 * @param string $indexName
	 * @return CM_Elastica_Type_Abstract
	 * @throws CM_Exception_Invalid
	 */
	private function _getIndex($indexName) {
		$indexes = array_filter($this->_getIndexes(), function (CM_Elastica_Type_Abstract $index) use ($indexName) {
			return $index->getIndex()->getName() == $indexName;
		});
		if (!$indexes) {
			throw new CM_Exception_Invalid('No such index: ' . $indexName);
		}
		return current($indexes);
	}
}