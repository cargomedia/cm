<?php

class CM_Elastica_Type_Location extends CM_Elastica_Type_Abstract {

	const INDEX_NAME = 'location';

	protected $_mapping = array(
		'level' => array('type' => 'integer', 'store' => 'yes'),
		'id' => array('type' => 'integer', 'store' => 'yes'),
		'ids' => array('type' => 'object', 'properties' => array(
			'1' => array('type' => 'integer'),
			'2' => array('type' => 'integer'),
			'3' => array('type' => 'integer'),
			'4' => array('type' => 'integer'),
		)),
		'name' => array('type' => 'string', 'analyzer' => 'lw'),
		'coordinates' => array('type' => 'geo_point'),
	);

	protected $_indexParams = array(
		'index' => array(
			'number_of_shards' => 1,
			'number_of_replicas' => 0
		),
		'analysis' => array(
			'analyzer' => array(
				'lw' => array(
					'type' => 'custom',
					'tokenizer' => 'keyword',
					'filter' => array('lowercase')
				)
			),
        )
	);

	protected function _getQuery($ids = null, $limit = null) {
		$query = 'SELECT * FROM `cm_tmp_location`';
		if (($limit = (int) $limit) > 0) {
			$query .= ' LIMIT ' . $limit;
		}
		return $query;
	}

	protected function _getDocument(array $data) {
		$doc = new Elastica_Document(null, array(
			'level' => (int) $data['level'],
			'id' => (int) $data['id'],
			'name' => $data['name'],
			'ids' => array(
				'1' => $data['1Id'],
				'2' => $data['2Id'],
				'3' => $data['3Id'],
				'4' => $data['4Id'],
			),
		));

		if (isset($data['lat']) && isset($data['lon'])) {
			$doc->addGeoPoint('coordinates', $data['lat'], $data['lon']);
		}

		return $doc;
	}

	/**
	 * @throws CM_Exception_NotImplemented
	 */
	public static function updateItem($item) {
		throw new CM_Exception_NotImplemented();
	}
}
