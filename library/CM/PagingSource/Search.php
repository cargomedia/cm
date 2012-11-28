<?php

class CM_PagingSource_Search extends CM_PagingSource_Abstract {
	private $_indexName, $_typeName, $_query, $_fields;

	/**
	 * @param string                  $indexName
	 * @param string                  $typeName
	 * @param CM_SearchQuery_Abstract $query
	 * @param array                   $fields OPTIONAL
	 */
	function __construct($indexName, $typeName, CM_SearchQuery_Abstract $query, array $fields = null) {
		$this->_indexName = $indexName;
		$this->_typeName = $typeName;
		$this->_query = $query;
		$this->_fields = $fields;
	}

	protected function _cacheKeyBase() {
		return array($this->_indexName, $this->_typeName, $this->_query->getQuery());
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
			$searchResult = CM_Search::getInstance()->query($this->_indexName, $this->_typeName, $data);
			$result = array('items' => array(), 'total' => 0);
			if (isset($searchResult['hits'])) {
				foreach ($searchResult['hits']['hits'] as $hit) {
					if ($this->_fields) {
						$result['items'][] = array_merge($hit['fields'], array('_id' => $hit['_id']));
					} else {
						$result['items'][] = $hit['_id'];
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
