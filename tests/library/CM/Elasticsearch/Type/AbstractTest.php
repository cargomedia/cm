<?php

class CM_Elasticsearch_Type_AbstractTest extends CMTest_TestCase {

    /** @var CM_Elasticsearch_Type_AbstractMock */
    private $_type;

    public static function setUpBeforeClass() {
        CM_Db_Db::exec("CREATE TABLE `index_mock` (`id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT, `name` VARCHAR(100))");
        CM_Config::get()->CM_Elasticsearch_Client->enabled = true;
    }

    public static function tearDownAfterClass() {
        CM_Db_Db::exec("DROP TABLE `index_mock`");
    }

    public function setUp() {
        $this->_type = new CM_Elasticsearch_Type_AbstractMock();
        $this->_type->createVersioned();
        $this->_type->getIndex()->refresh();
    }

    public function tearDown() {
        CMTest_TH::clearDb();
        $this->_type->getIndex()->delete();
    }

    public function testUpdateItem() {
        $query = new CM_Elasticsearch_Query();
        $query->queryMatch(array('name'), 'foo');
        $source = new CM_PagingSource_Elasticsearch($this->_type, $query);
        $this->assertSame(0, $source->getCount());

        $id1 = $this->_type->createEntry('foo');
        $id2 = $this->_type->createEntry('foo bar');
        $this->assertSame(2, $source->getCount());
        $this->assertEquals(array($id1, $id2), $source->getItems());

        CM_Db_Db::update('index_mock', array('name' => 'bar'), array('id' => $id2));
        $this->_type->updateItem(array('id' => $id2));
        $searchCli = new CM_Elasticsearch_Index_Cli();
        $searchCli->update($this->_type);
        $this->assertSame(1, $source->getCount());
        $this->assertEquals(array($id1), $source->getItems());
    }

    public function testUpdateItemWithJob() {
        $query = new CM_Elasticsearch_Query();
        $query->queryMatch(array('name'), 'foo');
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
}

class CM_Elasticsearch_Type_AbstractMock extends CM_Elasticsearch_Type_Abstract {

    const INDEX_NAME = 'index_mock';

    protected $_mapping = array(
        'name' => array('type' => 'string'),
    );

    protected $_indexParams = array(
        'index' => array(
            'number_of_shards'   => 1,
            'number_of_replicas' => 0
        ),
    );

    /**
     * @param string $name
     * @return int
     */
    public function createEntry($name) {
        $id = CM_Db_Db::insert('index_mock', array('name' => (string) $name));
        $this->update($id);
        $this->getIndex()->refresh();
        return (int) $id;
    }

    protected function _getQuery($ids = null, $limit = null) {
        return 'SELECT * FROM index_mock';
    }

    protected function _getDocument(array $data) {
        return new Elastica_Document($data['id'],
            array(
                'name' => $data['name'],
            )
        );
    }

    protected static function _getIdForItem($item) {
        return $item['id'];
    }
}
