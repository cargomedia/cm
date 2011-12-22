<?php

class CM_Search extends CM_Class_Abstract {
	const INDEX_LOCATION = 'location';

	/**
	 * @var Elastica_Client
	 */
	private static $_client = null;

	public static function update($index, $entityId) {
		CM_Cache_Redis::sAdd('Search.Updates_' . $index, $entityId);
	}

	/**
	 * @return bool
	 */
	public static function getEnabled() {
		return self::_getConfig()->enabled;
	}

	/**
	 * Elasticsearch request
	 *
	 * @param string $path   Path to call
	 * @param string $method HTTP method (GET, POST, DELETE, PUT)
	 * @param array  $data   Arguments as array
	 * @return array
	 */
	public static function call($path, $method = 'GET', array $data = null) {
		if (!self::getEnabled()) {
			return array();
		}

		if (!self::$_client) {
			self::$_client = new Elastica_Client(array('servers' => self::_getConfig()->servers, 'timeout' => 10,));
		}

		CM_Debug::get()->incStats('search', json_encode($data));

		$response = self::$_client->request($path, $method, $data);

		return $response->getData();
	}
}
