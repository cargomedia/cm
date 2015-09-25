<?php

class CM_Elasticsearch_ClientTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testConstructor() {
        $elasticsearchClient = self::_getElasticClient();
        $cmClient = new CM_Elasticsearch_Client($elasticsearchClient);

        $this->assertInstanceOf('CM_Elasticsearch_Client', $cmClient);
        $this->assertEquals($elasticsearchClient, CMTest_TH::callProtectedMethod($cmClient, '_getClient'));
    }

    public function testIndexCreateDelete() {
        $indexName = 'index1';
        $cmClient = self::_getCmClient();

        $cmClient->deleteIndex($indexName);
        $this->assertFalse($cmClient->indexExists($indexName));
        $cmClient->createIndex($indexName, $indexName, [], [], false);
        $this->assertTrue($cmClient->indexExists($indexName));
        $cmClient->deleteIndex($indexName);
        $this->assertFalse($cmClient->indexExists($indexName));

        $cmClient->deleteIndex($indexName);
    }

    public function testPutGetDeleteAlias() {
        $cmClient = self::_getCmClient();
        $indexName1 = 'index1';
        $indexName2 = 'index2';
        $aliasName = 'alias4';
        $cmClient->createIndex($indexName1, 'typeName', [], [], false);
        $cmClient->createIndex($indexName2, 'typeName', [], [], false);

        $this->assertSame([], $cmClient->getIndexesByAlias($aliasName));

        $cmClient->putAlias($indexName1, $aliasName);
        $cmClient->putAlias($indexName2, $aliasName);

        $this->assertSame([$indexName1, $indexName2], $cmClient->getIndexesByAlias($aliasName));

        $cmClient->deleteAlias($indexName1, $aliasName);
        $this->assertSame([$indexName2], $cmClient->getIndexesByAlias($aliasName));

        $cmClient->deleteIndex('index1');
        $cmClient->deleteIndex('index2');
    }

    public function testSetGetIndexSettings() {
        $cmClient = self::_getCmClient();
        $indexName = 'index1';
        $cmClient->createIndex($indexName, 'typeName', [], [], false);

        $cmClient->putIndexSettings($indexName, ['refresh_interval' => '10s']);
        $this->assertSame('10s', $cmClient->getIndexSettings($indexName, 'refresh_interval'));

        $cmClient->putIndexSettings($indexName, ['blocks.write' => '1']);
        $this->assertSame('10s', $cmClient->getIndexSettings($indexName, 'refresh_interval'));
        $this->assertSame(['write' => '1'], $cmClient->getIndexSettings($indexName, 'blocks'));

        $this->assertNull($cmClient->getIndexSettings('nonExistentIndexName'));

        $exception = $this->catchException(function () use ($cmClient) {
            $cmClient->getIndexSettings('');
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Invalid elasticsearch index value', $exception->getMessage());

        $cmClient->deleteIndex($indexName);
    }

    public function testBulkAddDelete() {
        $cmClient = self::_getCmClient();
        $indexName = 'index1';
        $typeName = 'typeName';
        $cmClient->createIndex($indexName, $typeName, [], [], false);

        $documentList = [
            new CM_Elasticsearch_Document('1', ['name' => 'foo']),
            new CM_Elasticsearch_Document('2', ['name' => 'bar']),
            new CM_Elasticsearch_Document('3', ['name' => 'baz']),
            new CM_Elasticsearch_Document('4', ['name' => 'quux']),
        ];

        $cmClient->bulkAddDocuments($documentList, $indexName, $typeName);
        $cmClient->refreshIndex($indexName);

        $this->assertSame(4, $cmClient->count($indexName, $typeName));

        $cmClient->bulkDeleteDocuments(['1', '2'], $indexName, $typeName);
        $cmClient->refreshIndex($indexName);

        $this->assertSame(2, $cmClient->count($indexName, $typeName));

        $cmClient->putIndexSettings($indexName, ['blocks.write' => 1]);
        $exception = $this->catchException(function () use ($cmClient, $indexName, $typeName) {
            $cmClient->bulkDeleteDocuments(['11', '222'], $indexName, $typeName);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertContainsAll([
            'Error in one or more bulk request actions',
            'Operator `delete` ClusterBlockException[blocked by: [FORBIDDEN/8/index write (api)];]'],
            $exception->getMessage()
        );
        $exception = $this->catchException(function () use ($cmClient, $indexName, $typeName) {
            $cmClient->bulkAddDocuments([new CM_Elasticsearch_Document('434', ['name' => 'bar'])], $indexName, $typeName);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertContainsAll([
            'Error in one or more bulk request actions',
            'Operator `index` ClusterBlockException[blocked by: [FORBIDDEN/8/index write (api)];]'],
            $exception->getMessage()
        );

        $cmClient->deleteIndex($indexName);
    }

    public function testSearch() {
        $cmClient = self::_getCmClient();
        $indexName = 'index1';
        $typeName = 'typeName';
        $cmClient->createIndex($indexName, $typeName, [], [], false);

        $documentId = '2';
        $singleDocumentList = [new CM_Elasticsearch_Document($documentId, ['name' => 'fooboo'])];
        $cmClient->bulkAddDocuments($singleDocumentList, $indexName, $typeName);
        $cmClient->refreshIndex($indexName);

        $query = new CM_Elasticsearch_Query();
        $query->filterTerm('_id', $documentId);

        $response = $cmClient->search([$indexName], [$typeName], ['query' => $query->getQuery()]);
        $this->assertArrayHasKey('hits', $response);
        $this->assertSame(1, $response['hits']['total']);

        $this->assertNotEmpty($response['hits']['hits']);
        $foundDocument = $response['hits']['hits'][0];

        $this->assertSame($documentId, $foundDocument['_id']);
        $this->assertSame($indexName, $foundDocument['_index']);
        $this->assertSame($typeName, $foundDocument['_type']);

        $cmClient->putIndexSettings($indexName, ['blocks.read' => 1]);
        $exception = $this->catchException(function () use ($cmClient, $indexName, $typeName, $query) {
            $cmClient->search([$indexName], [$typeName], ['query' => $query->getQuery()]);
        });
        $this->assertInstanceOf('\Elasticsearch\Common\Exceptions\ElasticsearchException', $exception);
        $this->assertContains('ClusterBlockException', $exception->getMessage());
        $this->assertContains('"status":403', $exception->getMessage());

        $cmClient->deleteIndex($indexName);
    }

    public function testCount() {
        $cmClient = self::_getCmClient();
        $indexName = 'index1';
        $typeName = 'typeName';
        $cmClient->deleteIndex($indexName);
        $cmClient->createIndex($indexName, $typeName, [], [], false);
        $this->assertSame(0, $cmClient->count($indexName, $typeName));

        $cmClient->bulkAddDocuments([new CM_Elasticsearch_Document('3', ['11' => '22'])], $indexName, $typeName);
        $cmClient->refreshIndex($indexName);
        $this->assertSame(1, $cmClient->count($indexName, $typeName));

        $cmClient->bulkDeleteDocuments([3], $indexName, $typeName);
        $cmClient->refreshIndex($indexName);
        $this->assertSame(0, $cmClient->count($indexName, $typeName));

        $cmClientWithMock = new CM_Elasticsearch_Client(new ElasticClientMock());

        $exception = $this->catchException(function () use ($cmClientWithMock, $indexName, $typeName) {
            $cmClientWithMock->count($indexName, $typeName, new CM_Elasticsearch_Query());
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Count query to `' . $indexName . '`:`' . $typeName . '` returned invalid value', $exception->getMessage());
    }

    /**
     * @return \Elasticsearch\Client
     */
    private static function _getElasticClient() {
        return \Elasticsearch\ClientBuilder::create()->build();
    }

    /**
     * @return CM_Elasticsearch_Client
     */
    private static function _getCmClient() {
        return CM_Service_Manager::getInstance()->getElasticsearch()->getClient();
    }
}

class ElasticClientMock extends \Elasticsearch\Client {

    public function __construct() {
    }

    /**
     * @param array $param
     * @return array
     * @internal param $params
     */
    public function count($param = []) {
        return ['foo' => 'bar', 'bar' => 'baz'];
    }
}
