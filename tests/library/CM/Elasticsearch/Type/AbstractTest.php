<?php

class CM_Elasticsearch_Type_AbstractTest extends CMTest_TestCase {

    /** @var CM_Elasticsearch_Type_AbstractMock */
    private $_type;

    public static function setUpBeforeClass() {
        CM_Db_Db::exec("CREATE TABLE `index_mock` (`id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT, `name` VARCHAR(100))");
    }

    public static function tearDownAfterClass() {
        CM_Db_Db::exec("DROP TABLE `index_mock`");
        parent::tearDownAfterClass();
    }

    public function setUp() {
        $elasticCluster = CMTest_TH::getServiceManager()->getElasticsearch();
        $elasticCluster->setEnabled(true);
        $this->_type = new CM_Elasticsearch_Type_AbstractMock($elasticCluster->getClient());
        $this->_type->createIndex();
        $this->_type->refreshIndex();
    }

    public function tearDown() {
        $this->_type->deleteIndex();
        CMTest_TH::getServiceManager()->getElasticsearch()->setEnabled(false);
        CMTest_TH::clearEnv();
    }

    public function testUpdateItem() {
        $query = new CM_Elasticsearch_Query();
        $query->queryMatchMulti(array('name'), 'foo');
        $source = new CM_PagingSource_Elasticsearch($this->_type, $query);
        $this->assertSame(0, $source->getCount());

        $id1 = $this->_type->createEntry('foo');
        $id2 = $this->_type->createEntry('foo bar');
        $this->assertSame(2, $source->getCount());
        $this->assertEquals(array($id1, $id2), $source->getItems());

        CM_Db_Db::update('index_mock', array('name' => 'bar'), array('id' => $id2));
        $this->_type->updateItem(array('id' => $id2));
        $this->_type->updateIndex();
        $this->assertSame(1, $source->getCount());
        $this->assertEquals(array($id1), $source->getItems());
    }

    public function testUpdateItemWithJob() {
        $this->_setupQueueMock();
        $query = new CM_Elasticsearch_Query();
        $query->queryMatchMulti(array('name'), 'foo');
        $source = new CM_PagingSource_Elasticsearch($this->_type, $query);
        $this->assertSame(0, $source->getCount());

        $id1 = $this->_type->createEntry('foo');
        $id2 = $this->_type->createEntry('foo bar');
        $this->assertSame(2, $source->getCount());
        $this->assertEquals(array($id1, $id2), $source->getItems());

        CM_Db_Db::update('index_mock', array('name' => 'bar'), array('id' => $id2));
        $this->_type->updateItemWithJob(array('id' => $id2));
        $this->assertSame(1, $source->getCount());
        $this->assertEquals(array($id1), $source->getItems());
    }

    public function testCount() {
        $this->assertSame(0, $this->_type->count());
        $this->_type->createEntry('foo');
        $this->_type->createEntry('foo bar');
        $this->assertSame(2, $this->_type->count());
    }
}

class CM_Elasticsearch_Type_AbstractMock extends CM_Elasticsearch_Type_Abstract {

    protected $_mapping = array(
        'name' => array('type' => 'string'),
    );

    protected $_indexParams = array(
        'number_of_shards'   => 1,
        'number_of_replicas' => 0,
    );

    /**
     * @param string $name
     * @return int
     */
    public function createEntry($name) {
        $id = CM_Db_Db::insert('index_mock', array('name' => (string) $name));
        $this->updateDocuments($id);
        $this->refreshIndex();
        return (int) $id;
    }

    protected function _getQuery($ids = null, $limit = null) {
        $query = 'SELECT * FROM index_mock';
        if (is_array($ids)) {
            $query .= ' WHERE `id` IN (' . implode(',', $ids) . ')';
        }
        return $query;
    }

    protected function _getDocument(array $data) {
        return new CM_Elasticsearch_Document($data['id'],
            array(
                'name' => $data['name'],
            )
        );
    }

    public static function getAliasName() {
        return 'index_mock';
    }

    protected static function _getIdForItem($item) {
        return $item['id'];
    }
}
