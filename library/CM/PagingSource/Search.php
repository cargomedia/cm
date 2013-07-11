<?php

class CM_PagingSource_Search extends CM_PagingSource_Abstract {

	/** @var CM_SearchQuery_Abstract */
	private $_query;

	/** @var array|null */
	private $_fields;

	/** @var CM_Elastica_Type_Abstract[] */
	private $_types;

	/** @var boolean */
	private $_multipleTypes;

	/**
	 * @param CM_Elastica_Type_Abstract|CM_Elastica_Type_Abstract[] $types
	 * @param CM_SearchQuery_Abstract                               $query
	 * @param array|null                                            $fields
	 * @throws CM_Exception_Invalid
	 */
	function __construct($types, CM_SearchQuery_Abstract $query, array $fields = null) {
		if (!is_array($types)) {
			$types = array($types);
		}
		array_walk($types, function ($type) {
			if (!$type instanceof CM_Elastica_Type_Abstract) {
				throw new CM_Exception_Invalid("Type is not an instance of CM_Elastica_Type_Abstract");
			}
		});
		if (empty($types)) {
			throw new CM_Exception_Invalid("At least one type needed");
		}
		$this->_multipleTypes = (1 < count($types));
		$this->_types = $types;
		$this->_query = $query;
		$this->_fields = $fields;
	}

	protected function _cacheKeyBase() {
		$keyParts = array();
		foreach ($this->_types as $type) {
			$keyParts[] = $type->getIndex()->getName() . '_' . $type->getType()->getName();
		}
		sort($keyParts);
		return array(implode(',', $keyParts), $this->_query->getQuery());
	}

	private function _getResult($offset = null, $count = null) {
		$cacheKey = array($this->_query->getSort(), $offset, $count);
		if (($result = $this->_cacheGet($cacheKey)) === false) {
			$data = array('query' => $this->_query->getQuery(), 'sort' => $this->_query->getSort());
			if ($this->_fields) {
				$data['fields'] = $this->_fields;
			}
			if ($offset !== null) {
				$data['from'] = $offset;
			}
			if ($count !== null) {
				$data['size'] = $count;
			}
			$searchResult = CM_Search::getInstance()->query($this->_types, $data);
			$result = array('items' => array(), 'total' => 0);
			if (isset($searchResult['hits'])) {
				foreach ($searchResult['hits']['hits'] as $hit) {
					if ($this->_fields && array_key_exists('fields', $hit)) {
						if ($this->_multipleTypes) {
							$idArray = array('_id' => $hit['_id'], '_type' => $hit['_type']);
						} else {
							$idArray = array('_id' => $hit['_id']);
						}
						$result['items'][] = array_merge($hit['fields'], $idArray);
					} else {
						if ($this->_multipleTypes) {
							$item = array('_id' => $hit['_id'], '_type' => $hit['_type']);
						} else {
							$item = $hit['_id'];
						}
						$result['items'][] = $item;
					}
				}
				$result['total'] = $searchResult['hits']['total'];
			}
			$this->_cacheSet($cacheKey, $result);
		}
		return $result;
	}

	public function getCount($offset = null, $count = null) {
		$result = $this->_getResult($offset, $count);
		return (int) $result['total'];
	}

	public function getItems($offset = null, $count = null) {
		$result = $this->_getResult($offset, $count);
		return $result['items'];
	}

	public function getStalenessChance() {
		return 0.1;
	}
}
