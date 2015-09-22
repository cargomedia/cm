<?php

class CM_Elasticsearch_Client {

    /** @var Elasticsearch\Client */
    protected $_client;

    /**
     * @param \Elasticsearch\Client $client
     */
    public function __construct(Elasticsearch\Client $client) {
        $this->_client = $client;
    }

    /**
     * @param string[] $idList
     * @param string   $indexName
     * @param string   $typeName
     */
    public function bulkDeleteDocuments(array $idList, $indexName, $typeName) {
        $requestBody = [];
        foreach ($idList as $id) {
            $requestBody[] = ['delete' => ['_id' => (string) $id]];
        }
        $this->_getClient()->bulk([
            'index' => $indexName,
            'type'  => $typeName,
            'body'  => $requestBody,
        ]);
    }

    /**
     * @param CM_Elasticsearch_Document[] $documentList
     * @param string                      $indexName
     * @param string                      $typeName
     */
    public function bulkAddDocuments(array $documentList, $indexName, $typeName) {
        $requestBody = [];

        foreach ($documentList as $document) {
            $createParams = [];
            $documentId = $document->getId();
            if (null !== $documentId) {
                $createParams = ['_id' => $documentId];
            }
            $requestBody[] = ['index' => $createParams];
            $requestBody[] = $document->toArray();
        }
        $this->_getClient()->bulk([
            'index' => $indexName,
            'type'  => $typeName,
            'body'  => $requestBody,
        ]);
    }

    /**
     * @param  string                     $indexName
     * @param  string                     $typeName
     * @param CM_Elasticsearch_Query|null $query
     * @return int
     */
    public function count($indexName, $typeName, CM_Elasticsearch_Query $query = null) {
        $requestParams = [
            'index' => $indexName,
            'type'  => $typeName,
        ];
        if (null !== $query) {
            $requestParams['body']['query'] = $query->getQuery();
        }
        $responseCount = $this->_getClient()->count($requestParams);

        if (isset($responseCount['count'])) {
            return (int) $responseCount['count'];
        } else {
            return 0;
        }
    }

    /**
     * @param string     $indexName
     * @param string     $typeName
     * @param array|null $indexParams
     * @param array|null $mapping
     * @param bool|null  $useSource
     */
    public function createIndex($indexName, $typeName, array $indexParams = null, array $mapping = null, $useSource = null) {
        $indexName = (string) $indexName;

        if (true !== $useSource) {
            $useSource = false;
        }

        $this->deleteIndex($indexName);

        //HACK Different index settings params
        if (!empty($indexParams['index'])) {
            foreach ($indexParams['index'] as $settingKey => $settingValue) {
                $indexParams[$settingKey] = $settingValue;
            }
            unset($indexParams['index']);
        }
        //EOF HACK
        //TODO either fix it on all indices or create adapter method

        $requestParams = [
            'index' => $indexName,
        ];

        $requestBody = [];
        if (!empty($indexParams)) {
            $requestBody['settings'] = $indexParams;
        }
        if (!empty($mapping)) {
            $requestBody['mappings'][$typeName] = [
                '_source'    => [
                    'enabled' => $useSource,
                ],
                'properties' => $mapping,
            ];
        }

        if (!empty($requestBody)) {
            $requestParams['body'] = $requestBody;
        }

        $this->_getIndices()->create($requestParams);
    }

    /**
     * @param string $alias
     * @return string[]
     */
    public function getIndexesByAlias($alias) {
        try {
            $response = $this->_getIndices()->getAlias([
                'name' => (string) $alias,
            ]);
        } catch (\Elasticsearch\Common\Exceptions\Missing404Exception $e) {
            $response = [];
        }
        return array_keys($response);
    }

    /**
     * @param string $indexName
     * @param string $aliasName
     */
    public function deleteAlias($indexName, $aliasName) {
        $this->_getIndices()->deleteAlias([
            'index'  => $indexName,
            'name'   => $aliasName,
            'client' => ['ignore' => 404],
        ]);
    }

    /**
     * @param string[]|string $indexName
     */
    public function deleteIndex($indexName) {
        $paramIndex = self::_prepareIndexNameParam($indexName);
        if ('' !== $paramIndex) {
            $this->_getIndices()->delete([
                'index'  => $paramIndex,
                'client' => ['ignore' => 404],
            ]);
        }
    }

    /**
     * @param string $indexName
     * @return bool
     */
    public function indexExists($indexName) {
        return $this->_getIndices()->exists(['index' => $indexName]);
    }

    /**
     * @param string[]|string $indexName
     * @param string|null     $settingKey
     * @return mixed|null
     * @throws CM_Exception_Invalid
     */
    public function getIndexSettings($indexName, $settingKey = null) {
        $paramIndex = self::_prepareIndexNameParam($indexName);
        if ('' === $paramIndex) {
            throw new CM_Exception_Invalid('Invalid elasticsearch index value');
        }
        $settingsResponse = $this->_getIndices()->getSettings([
            'index'  => $paramIndex,
            'client' => ['ignore' => 404],
        ]);

        if (isset($settingsResponse['error'])) {
            return null;
        }

        if (null !== $settingKey) {
            $settingKey = (string) $settingKey;
            $settingsList = current($settingsResponse); //{"photo.1441893401":{"settings":{"index":{"blocks":{"write":"0"},...
            if (isset($settingsList['settings']['index'][$settingKey])) {
                return $settingsList['settings']['index'][$settingKey];
            } else {
                return null;
            }
        } else {
            return $settingsResponse;
        }
    }

    /**
     * @param string $indexName
     * @param string $aliasName
     */
    public function putAlias($indexName, $aliasName) {
        $this->_getIndices()->putAlias([
            'index' => (string) $indexName,
            'name'  => (string) $aliasName,
        ]);
    }

    /**
     * @param string[]|string $indexName
     * @param array           $settings
     * @throws CM_Exception_Invalid
     */
    public function putIndexSettings($indexName, array $settings) {
        $paramIndex = self::_prepareIndexNameParam($indexName);
        if ('' === $paramIndex) {
            throw new CM_Exception_Invalid('Invalid elasticsearch index value');
        }

        $this->_getIndices()->putSettings([
            'index' => $paramIndex,
            'body'  => [
                'settings' => $settings,
            ]
        ]);
    }

    /**
     * @param string[]|string $indexName
     * @throws CM_Exception_Invalid
     */
    public function refreshIndex($indexName) {
        $paramIndex = self::_prepareIndexNameParam($indexName);
        if ('' === $paramIndex) {
            throw new CM_Exception_Invalid('Invalid elasticsearch index value');
        }
        $this->_getIndices()->refresh([
            'index' => $paramIndex,
        ]);
    }

    /**
     * @return \Elasticsearch\Client
     */
    protected function _getClient() {
        return $this->_client;
    }

    /**
     * @return \Elasticsearch\Namespaces\IndicesNamespace
     */
    protected function _getIndices() {
        return $this->_client->indices();
    }

    /**
     * @param string[]|string $indexName
     * @return string
     */
    protected static function _prepareIndexNameParam($indexName) {
        return is_array($indexName) ? join(',', $indexName) : (string) $indexName;
    }
}