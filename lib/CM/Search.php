<?php

class CM_Search {
	const INDEX_USER = 'user';
	const INDEX_LOCATION = 'location';

	/**
	 * @var Elastica_Client
	 */
	private static $_client = null;

	public static function update($index, $entityId) {
		CM_Cache_Redis::sAdd('Search.Updates_' . $index, $entityId);
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
		if (!Config::get()->search->enabled) {
			return array();
		}

		if (!self::$_client) {
			self::$_client = new Elastica_Client(array('servers' => Config::get()->search->servers, 'timeout' => 10,));
		}

		CM_Debug::get()->incStats('search', json_encode($data));

		$response = self::$_client->request($path, $method, $data);

		return $response->getData();
	}
}
