<?php

class CM_PagingSource_ElasticsearchTest extends CMTest_TestCase {

    /** @var  CM_Elasticsearch_Client */
    private $_elasticsearchClient;

    public static function setUpBeforeClass() {
        CM_Db_Db::exec("CREATE TABLE `indexTest_1` (`id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT, `name` VARCHAR(8))");
        CM_Db_Db::exec("CREATE TABLE `indexTest_2` (`id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT, `price` INT UNSIGNED)");
        CM_Db_Db::exec("CREATE TABLE `indexTest_3` (`id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT, `price` INT UNSIGNED)");
    }

    public static function tearDownAfterClass() {
        CM_Db_Db::exec("DROP TABLE `indexTest_1`");
        CM_Db_Db::exec("DROP TABLE `indexTest_2`");
        CM_Db_Db::exec("DROP TABLE `indexTest_3`");
        parent::tearDownAfterClass();
    }

    public function setUp() {
        $elasticCluster = CMTest_TH::getServiceManager()->getElasticsearch();
        $elasticCluster->setEnabled(true);
        $this->_elasticsearchClient = $elasticCluster->getClient();
        $type1 = new CM_Elasticsearch_Type_Mock1($this->_elasticsearchClient);
        $type2 = new CM_Elasticsearch_Type_Mock2($this->_elasticsearchClient);
        $type3 = new CM_Elasticsearch_Type_Mock3($this->_elasticsearchClient);
        $type1->createIndex();
        $type2->createIndex();
        $type3->createIndex();
        $type1->refreshIndex();
        $type2->refreshIndex();
        $type3->refreshIndex();
    }

    public function tearDown() {
        CMTest_TH::getServiceManager()->getElasticsearch()->setEnabled(false);
        CMTest_TH::clearEnv();
    }

    public function testGet() {
        $type1 = new CM_Elasticsearch_Type_Mock1($this->_elasticsearchClient);
        $source = new CM_PagingSource_Elasticsearch($type1, new CM_Elasticsearch_Query());
        $this->assertSame(0, $source->getCount());

        $id = $type1->createEntry('foo');
        $this->assertSame(1, $source->getCount());
        $this->assertSame([(string) $id], $source->getItems());

        $type3 = new CM_Elasticsearch_Type_Mock3($this->_elasticsearchClient);
        $source = new CM_PagingSource_Elasticsearch($type3, new CM_Elasticsearch_Query(), ['price']);
        $this->assertSame(0, $source->getCount());

        $id2 = $type3->createEntry(3);
        $this->assertSame(1, $source->getCount());
        $this->assertSame([['id' => (string) $id2, 'price' => 3]], $source->getItems());
    }

    public function testMultiGet() {
        $type1 = new CM_Elasticsearch_Type_Mock1($this->_elasticsearchClient);
        $type2 = new CM_Elasticsearch_Type_Mock2($this->_elasticsearchClient);
        $source = new CM_PagingSource_Elasticsearch([$type1, $type2], new CM_Elasticsearch_Query());
        $this->assertSame(0, $source->getCount());

        $id1 = $type1->createEntry('foo');
        $this->assertSame(1, $source->getCount());

        $id2 = $type2->createEntry(1);
        $this->assertSame(2, $source->getCount());
        $this->assertContainsAll([
            ['id' => (string) $id1, 'type' => 'index_1'],
            ['id' => (string) $id2, 'type' => 'index_2']
        ], $source->getItems());

        $type3 = new CM_Elasticsearch_Type_Mock3($this->_elasticsearchClient);
        $source = new CM_PagingSource_Elasticsearch([$type1, $type2, $type3], new CM_Elasticsearch_Query(), ['price']);
        $id3 = $type3->createEntry(5);

        $this->assertSame(3, $source->getCount());
        $this->assertContainsAll([
            ['id' => (string) $id1, 'type' => 'index_1'],
            ['id' => (string) $id2, 'type' => 'index_2'],
            ['id' => (string) $id3, 'type' => 'index_3', 'price' => 5]
        ], $source->getItems());
    }

    public function testSelectRandomSubset() {
        $type1 = new CM_Elasticsearch_Type_Mock1($this->_elasticsearchClient);
        $query = new CM_Elasticsearch_Query();
        $source = new CM_PagingSource_Elasticsearch($type1, $query);
        $type1->createEntry('foo');
        $type1->createEntry('bar');
        $type1->createEntry('zoo');
        $query->selectRandomSubset(100, mt_rand());
        $this->assertSame(3, $source->getCount());

        $query->selectRandomSubset(0, mt_rand());
        $this->assertSame(0, $source->getCount());
        $this->assertSame([], $source->getItems());

        foreach ([90, 50, 10, 1] as $percentage) {
            $count = 0;
            $n = 400;
            for ($i = 0; $i < $n; $i++) {
                $query->selectRandomSubset($percentage, mt_rand());
                $count += $source->getCount();
            }
            $countExpected = 3 * $percentage / 100 * $n;
            $countExpectedMin = $countExpected - 5 * sqrt($countExpected);
            $countExpectedMax = $countExpected + 5 * sqrt($countExpected);
            $this->assertGreaterThan($countExpectedMin, $count);
            $this->assertLessThan($countExpectedMax, $count);
        }
    }
}

class CM_Elasticsearch_Type_Mock1 extends CM_Elasticsearch_Type_Abstract {

    protected $_mapping = [
        'name' => ['type' => 'string'],
    ];

    protected $_indexParams = [
        'number_of_shards'   => 1,
        'number_of_replicas' => 0,
    ];

    /**
     * @param string $name
     * @return int
     */
    public function createEntry($name) {
        $id = CM_Db_Db::insert('indexTest_1', ['name' => (string) $name]);
        $this->updateDocuments($id);
        $this->refreshIndex();
        return (int) $id;
    }

    protected function _getQuery($ids = null, $limit = null) {
        return 'SELECT * FROM indexTest_1';
    }

    protected function _getDocument(array $data) {
        return new CM_Elasticsearch_Document($data['id'], ['name' => $data['name']]);
    }

    public static function getAliasName() {
        return 'index_1';
    }
}

class CM_Elasticsearch_Type_Mock2 extends CM_Elasticsearch_Type_Abstract {

    protected $_mapping = [
        'price' => ['type' => 'integer'],
    ];

    protected $_indexParams = [
        'number_of_shards'   => 1,
        'number_of_replicas' => 0,
    ];

    /**
     * @param int $price
     * @return int
     */
    public function createEntry($price) {
        $id = CM_Db_Db::insert('indexTest_2', ['price' => (int) $price]);
        $this->updateDocuments($id);
        $this->refreshIndex();
        return (int) $id;
    }

    protected function _getQuery($ids = null, $limit = null) {
        return 'SELECT * FROM indexTest_2';
    }

    protected function _getDocument(array $data) {
        return new CM_Elasticsearch_Document($data['id'], ['price' => $data['price']]);
    }

    public static function getAliasName() {
        return 'index_2';
    }
}

class CM_Elasticsearch_Type_Mock3 extends CM_Elasticsearch_Type_Abstract {

    protected $_mapping = [
        'price' => ['type' => 'integer', 'store' => 'yes'],
    ];

    protected $_indexParams = [
        'number_of_shards'   => 1,
        'number_of_replicas' => 0,
    ];

    /**
     * @param int $price
     * @return int
     */
    public function createEntry($price) {
        $id = CM_Db_Db::insert('indexTest_3', ['price' => (int) $price]);
        $this->updateDocuments($id);
        $this->refreshIndex();
        return (int) $id;
    }

    protected function _getQuery($ids = null, $limit = null) {
        return 'SELECT * FROM indexTest_3';
    }

    protected function _getDocument(array $data) {
        return new CM_Elasticsearch_Document($data['id'], ['price' => $data['price']]);
    }

    public static function getAliasName() {
        return 'index_3';
    }
}
