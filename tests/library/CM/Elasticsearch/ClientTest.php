<?php

class CM_Elasticsearch_ClientTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testConstructor() {
        $elasticClient = self::_getElasticClient();
        $cmClient = new CM_Elasticsearch_Client($elasticClient);

        $this->assertInstanceOf('CM_Elasticsearch_Client', $cmClient);
        $this->assertEquals($elasticClient, CMTest_TH::callProtectedMethod($cmClient, '_getClient'));
    }

    public function testIndexCreateDelete() {
        $indexName = 'index1';
        $cmClient = self::_getCmClient();

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

        $cmClient->deleteIndex($indexName);
    }

    private static function _getElasticClient() {
        return \Elasticsearch\ClientBuilder::create()->build();
    }

    private static function _getCmClient() {
        return new CM_Elasticsearch_Client(self::_getElasticClient());
    }
}
