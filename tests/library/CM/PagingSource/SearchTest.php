<?php

class CM_PagingSource_SearchTest extends CMTest_TestCase {

	public static function setUpBeforeClass() {
		CM_Db_Db::exec("CREATE TABLE `indexTest_1` (`id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT, `name` VARCHAR(8))");
		CM_Db_Db::exec("CREATE TABLE `indexTest_2` (`id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT, `price` INT UNSIGNED)");
		$config = CM_Config::get();
		$config->CM_Search->enabled = true;
		CM_Config::set($config);
	}

	public static function tearDownAfterClass() {
		CM_Db_Db::exec("DROP TABLE `indexTest_1");
		CM_Db_Db::exec("DROP TABLE `indexTest_2");
	}

	public function tearDown() {
		CMTest_TH::clearDb();
	}

	public function setUp() {
		$type1 = new CM_Elastica_Type_Mock1();
		$type2 = new CM_Elastica_Type_Mock2();
		$type1->createVersioned();
		$type2->createVersioned();
		$type1->getIndex()->refresh();
		$type2->getIndex()->refresh();
	}

	public function testGet() {
		$type1 = new CM_Elastica_Type_Mock1();
		$source = new CM_PagingSource_Search($type1, new CM_SearchQuery_Mock());
		$this->assertSame(0, $source->getCount());

		$type1->createEntry('foo');
		$this->assertSame(1, $source->getCount());
	}

	public function testMultiGet() {
		$type1 = new CM_Elastica_Type_Mock1();
		$type2 = new CM_Elastica_Type_Mock2();
		$source = new CM_PagingSource_Search(array($type1, $type2), new CM_SearchQuery_Mock());
		$this->assertSame(0, $source->getCount());

		$type1->createEntry('foo');
		$this->assertSame(1, $source->getCount());

		$type2->createEntry(1);
		$this->assertSame(2, $source->getCount());
	}

}

class CM_Elastica_Type_Mock1 extends CM_Elastica_Type_Abstract {

	const INDEX_NAME = 'index_1';

	protected $_mapping = array(
		'id'   => array('type' => 'integer'),
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
	 */
	public function createEntry($name) {
		$id = CM_Db_Db::insert('indexTest_1', array('name' => (string) $name));
		$this->update($id);
		$this->getIndex()->refresh();
	}

	protected function _getQuery($ids = null, $limit = null) {
		return 'SELECT * FROM indexTest_1';
	}

	protected function _getDocument(array $data) {
		$id = self::_getIdSerialized(array('id' => (int) $data['id']));
		$doc = new Elastica_Document($id,
			array(
				'id'   => $data['id'],
				'name' => $data['name'],
			)
		);

		return $doc;
	}
}

class CM_Elastica_Type_Mock2 extends CM_Elastica_Type_Abstract {

	const INDEX_NAME = 'index_2';

	protected $_mapping = array(
		'id'    => array('type' => 'integer'),
		'price' => array('type' => 'integer'),
	);

	protected $_indexParams = array(
		'index' => array(
			'number_of_shards'   => 1,
			'number_of_replicas' => 0
		),
	);

	/**
	 * @param int $price
	 */
	public function createEntry($price) {
		$id = CM_Db_Db::insert('indexTest_2', array('price' => (int) $price));
		$this->update($id);
		$this->getIndex()->refresh();
	}

	protected function _getQuery($ids = null, $limit = null) {
		return 'SELECT * FROM indexTest_2';
	}

	protected function _getDocument(array $data) {
		$id = self::_getIdSerialized(array('id' => (int) $data['id']));
		$doc = new Elastica_Document($id,
			array(
				'id'    => $data['id'],
				'price' => $data['price'],
			)
		);

		return $doc;
	}
}

class CM_SearchQuery_Mock extends CM_SearchQuery_Abstract {

}
