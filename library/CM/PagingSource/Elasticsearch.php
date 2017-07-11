<?php

class CM_PagingSource_Elasticsearch extends CM_PagingSource_Abstract {

    /** @var CM_Elasticsearch_Query */
    private $_query;

    /** @var array|null */
    private $_fields;

    /** @var CM_Elasticsearch_Type_Abstract[] */
    private $_types;

    /** @var boolean */
    private $_returnType;

    /**
     * @param CM_Elasticsearch_Type_Abstract|CM_Elasticsearch_Type_Abstract[] $types
     * @param CM_Elasticsearch_Query                                          $query
     * @param array|null                                                      $fields
     * @param bool|null                                                       $returnType
     * @throws CM_Exception_Invalid
     */
    function __construct($types, CM_Elasticsearch_Query $query, array $fields = null, $returnType = null) {
        if (!is_array($types)) {
            $types = [$types];
        }
        array_walk($types, function ($type) {
            if (!$type instanceof CM_Elasticsearch_Type_Abstract) {
                throw new CM_Exception_Invalid("Type is not an instance of CM_Elasticsearch_Type_Abstract");
            }
        });
        if (empty($types)) {
            throw new CM_Exception_Invalid("At least one type needed");
        }
        if (null === $returnType) {
            $returnType = (1 < count($types));
        }
        $this->_returnType = (bool) $returnType;
        $this->_types = $types;
        $this->_query = $query;
        $this->_fields = $fields;
    }

    protected function _cacheKeyBase() {
        $keyParts = [];
        foreach ($this->_types as $type) {
            $keyParts[] = $type->getIndexName() . '_' . $type->getIndexName();
        }
        sort($keyParts);
        $cacheKeyBase = [implode(',', $keyParts), $this->_query->getQuery()];
        if (null !== $this->_query->getMinScore()) {
            $cacheKeyBase[] = $this->_query->getMinScore();
        }
        return $cacheKeyBase;
    }

    /**
     * @param int|null $offset
     * @param int|null $count
     * @return array
     */
    private function _getResult($offset = null, $count = null) {
        $cacheKey = [$this->_query->getSort(), $offset, $count];
        if (($result = $this->_cacheGet($cacheKey)) === false) {
            $data = ['query' => $this->_query->getQuery(), 'sort' => $this->_query->getSort()];
            if (null !== $this->_query->getMinScore()) {
                $data['min_score'] = $this->_query->getMinScore();
            }
            if ($this->_fields) {
                $data['fields'] = $this->_fields;
            }
            if ($offset !== null) {
                $data['from'] = $offset;
            }
            if ($count !== null) {
                $data['size'] = $count;
            }
            $searchResult = CM_Service_Manager::getInstance()->getElasticsearch()->query($this->_types, $data);
            $result = ['items' => [], 'total' => 0];
            if (isset($searchResult['hits'])) {
                foreach ($searchResult['hits']['hits'] as $hit) {
                    if ($this->_fields && array_key_exists('fields', $hit)) {
                        if ($this->_returnType) {
                            $idArray = ['id' => $hit['_id'], 'type' => $hit['_type']];
                        } else {
                            $idArray = ['id' => $hit['_id']];
                        }
                        $fields = $hit['fields'];
                        $fields = Functional\map($fields, function ($field) {
                            if (is_array($field) && 1 == count($field)) {
                                $field = reset($field);
                            }
                            return $field;
                        });
                        $result['items'][] = array_merge($idArray, $fields);
                    } else {
                        if ($this->_returnType) {
                            $item = ['id' => $hit['_id'], 'type' => $hit['_type']];
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
