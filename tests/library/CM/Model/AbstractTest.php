<?php

class CM_Model_AbstractTest extends CMTest_TestCase {

	public static function setupBeforeClass() {
		CM_Mysql::exec("CREATE TABLE IF NOT EXISTS `modelMock` (
				`id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
				`foo` VARCHAR(32)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;
		");
		CM_Mysql::exec("CREATE TABLE IF NOT EXISTS `modelThasIsAnAssetMock` (
				`id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
				`modelMockId` INT UNSIGNED NOT NULL,
				`bar` VARCHAR(32),
				KEY (`modelMockId`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;
		");
	}

	public static function tearDownAfterClass() {
		CM_Mysql::exec("DROP TABLE `modelMock`");
		CM_Mysql::exec("DROP TABLE `modelThasIsAnAssetMock`");
	}

	public function setup() {
		$modelMock = CM_ModelMock::create(array('foo' => 'bar1'));
	}

	public function tearDown() {
		CM_Mysql::exec("TRUNCATE TABLE `modelMock`");
		CM_Mysql::exec("TRUNCATE TABLE `modelThasIsAnAssetMock`");
		CMTest_TH::clearEnv();
	}

	public function testConstruct() {
		$modelMock = CM_ModelMock::create(array('foo' => 'bar1'));
		$modelMock = new CM_ModelMock($modelMock->getId());
		$this->assertEquals('bar1', $modelMock->getFoo());
	}

	public function testCache() {
		$modelMock = CM_ModelMock::create(array('foo' => 'bar1'));
		$modelMock = new CM_ModelMock($modelMock->getId());
		$this->assertEquals('bar1', $modelMock->getFoo());
		CM_Mysql::update('modelMock', array('foo' => 'bar2'), array('id' => $modelMock->getId()));
		$modelMock = new CM_ModelMock($modelMock->getId());
		$this->assertEquals('bar1', $modelMock->getFoo());
		$modelMock->_change();
		$modelMock = new CM_ModelMock($modelMock->getId());
		$this->assertEquals('bar2', $modelMock->getFoo());
	}

	public function testGet() {
		$modelMock = CM_ModelMock::create(array('foo' => 'bar1'));
		$modelMock = new CM_ModelMock($modelMock->getId());
		try {
			$modelMock->_get('foo');
			$this->assertEquals('bar1', $modelMock->_get('foo'));
		} catch (CM_Exception $ex) {
			$this->fail('Field `foo` does not exist.');
		}
		try {
			$modelMock->_get('bar');
			$this->fail('Field `bar` exists.');
		} catch (CM_Exception $ex) {
			$this->assertTrue(true);
		}
	}

	public function testHas() {
		$modelMock = CM_ModelMock::create(array('foo' => 'bar1'));
		$modelMock = new CM_ModelMock($modelMock->getId());
		$this->assertTrue($modelMock->_has('foo'));
		$this->assertFalse($modelMock->_has('bar'));
	}

	public function testSet() {
		$modelMock = CM_ModelMock::create(array('foo' => 'bar1'));
		$modelMock = new CM_ModelMock($modelMock->getId());
		$this->assertEquals('bar1', $modelMock->getFoo());
		$modelMock->_set('foo', 'bar2');
		$modelMock->_set('bar', 'foo');
		$this->assertEquals('bar2', $modelMock->getFoo());
		$this->assertEquals('foo', $modelMock->_get('bar'));
		$modelMock->_change();
		$modelMock = new CM_ModelMock($modelMock->getId());
		$this->assertEquals('bar1', $modelMock->getFoo());
	}

	public function testDelete() {
		$modelMock = CM_ModelMock::create(array('foo' => 'bar1'));
		$modelMock = new CM_ModelMock($modelMock->getId());
		$modelMock->delete();
		try {
			$modelMock = new CM_ModelMock($modelMock->getId());
			$this->fail('Model was not deleted');
		} catch (CM_Exception_Nonexistent $ex) {
			$this->assertTrue(true);
		}
	}

	public function testOnChange() {
		$modelMock = CM_ModelMock::create(array('foo' => 'bar1'));
		$modelMock = new CM_ModelMock($modelMock->getId());
		$this->assertEquals(0, $modelMock->onChangeCounter);
		$modelMock->_change();
		$this->assertEquals(1, $modelMock->onChangeCounter);
	}

	public function testOnCreate() {
		/** @var CM_ModelMock $modelMock */
		$modelMock = CM_ModelMock::create(array('foo' => 'bar1'));
		$this->assertEquals(1, $modelMock->onCreateCounter);

		$modelMock = new CM_ModelMock($modelMock->getId());
		$this->assertEquals(0, $modelMock->onCreateCounter);
	}

	public function testSerializable() {
		$modelMock = CM_ModelMock::create(array('foo' => 'bar1'));
		$modelMock = new CM_ModelMock($modelMock->getId());
		$this->assertEquals('bar1', $modelMock->getFoo());
		$this->assertEquals('bar1', unserialize(serialize($modelMock))->getFoo());

		$modelMock->_set('foo', 'bar2');
		$this->assertEquals('bar2', unserialize(serialize($modelMock))->getFoo());
	}

	public function testModelAsset() {
		$modelMock = CM_ModelMock::create(array('foo' => 'bar1'));
		$modelThatIsAnAssetMock = CM_ModelThasIsAnAssetMock::create(array('modelMockId' => $modelMock->getId(), 'bar' => $modelMock->getFoo()));
		$modelThatIsAnAssetMock->_change();
		$modelMock->_change();
		$modelMock = new CM_ModelMock($modelMock->getId());
		$this->assertEquals('bar1', $modelMock->getModelAsset()->getBar());
		$modelThatIsAnAssetMock->setBar('bar2');
		$modelMock = new CM_ModelMock($modelMock->getId());
		$this->assertEquals('bar2', $modelMock->getModelAsset()->getBar());
	}

	public function testGetAsset() {
		/** @var CM_ModelMock2 $modelMock */
		$modelMock = new CM_ModelMock2(1);
		$this->assertInstanceOf('CM_ModelAsset_ModelMock_ModelAssetMock', $modelMock->getModelAssetMock());
	}

	public function testLazyAsset() {
		$modelMock = new CM_ModelMock2(1);
		$this->assertFalse(array_key_exists('CM_ModelAsset_ModelMock_ModelAssetMock:foo', $modelMock->_get()));
		$modelMock->getModelAssetMock()->getFoo();
		$this->assertTrue(array_key_exists('CM_ModelAsset_ModelMock_ModelAssetMock:foo', $modelMock->_get()));
		$modelMock->_set('CM_ModelAsset_ModelMock_ModelAssetMock:foo', 'bar');

		$modelMock = new CM_ModelMock2(1);
		$this->assertTrue(array_key_exists('CM_ModelAsset_ModelMock_ModelAssetMock:foo', $modelMock->_get()));
		$this->assertEquals('bar', $modelMock->getModelAssetMock()->getFoo());
		$modelMock->_change();

		$this->assertEquals('foo', $modelMock->getModelAssetMock()->getFoo());
	}

	public function testCachingStrategy() {
		$modelMock = new CM_ModelMock(1);
		try {
			$modelMock->_change();
			$this->assertTrue(true);
		} catch (CM_Exception_NotAllowed $ex) {
			$this->fail("Using CacheLocal instead of Cache.");
		}
		$modelMock = new CM_ModelMock_Local(1);
		try {
			$modelMock->_change();
			$this->fail("Using Cache instead of CacheLocal.");
		} catch (CM_Exception_NotAllowed $ex) {
			$this->assertTrue(true);
		}
	}

	public function testCreateType() {
		$user = CM_Model_Abstract::createType(CM_Model_User::TYPE);
		$this->assertInstanceOf('CM_Model_User', $user);
	}

	public function testTypeConstants() {
		foreach (CM_Model_Abstract::getClassChildren() as $class) {
			$classReflection = new ReflectionClass($class);
			$this->assertArrayHasKey('TYPE', $classReflection->getConstants(), 'No `TYPE` constant defined for `' . $class . '`');
		}
	}
}

class CM_ModelMock extends CM_Model_Abstract {

	public $onChangeCounter = 0;
	public $onCreateCounter = 0;

	public function getFoo() {
		return (string) $this->_get('foo');
	}

	/**
	 * @return CM_ModelThasIsAnAssetMock
	 */
	public function getModelAsset() {
		return $this->_getAsset('CM_ModelAsset_ModelMock_ModelThasIsAnAssetMock')->get();
	}

	protected function _loadData() {
		return CM_Mysql::select('modelMock', array('foo'), array('id' => $this->getId()))->fetchAssoc();
	}

	protected function _onChange() {
		$this->onChangeCounter++;
	}

	protected function _onCreate() {
		$this->onCreateCounter++;
	}

	protected function _onDelete() {
		CM_Mysql::delete('modelMock', array('id' => $this->getId()));
	}

	protected function _loadAssets() {
		return array(new CM_ModelAsset_ModelMock_ModelThasIsAnAssetMock($this));
	}

	protected static function _create(array $data) {
		return new self(CM_Mysql::insert('modelMock', array('foo' => $data['foo'])));
	}
}

class CM_ModelMock_Local extends CM_ModelMock {

	public function __construct($id) {
		$this->_setCacheLocal();
		parent::__construct($id);
	}
}

class CM_ModelThasIsAnAssetMock extends CM_Model_Abstract {

	public function getBar() {
		return (string) $this->_get('bar');
	}

	public function setBar($bar) {
		$bar = (string) $bar;
		CM_Mysql::update('modelThasIsAnAssetMock', array('bar' => $bar), array('id' => $this->getId()));
		$this->_change();
	}

	public function getModelMock($throwNonexistent = true) {
		try {
			return new CM_ModelMock($this->getModelMockId());
		} catch (CM_Exception_Nonexistent $ex) {
			if ($throwNonexistent) {
				throw $ex;
			}
			return null;
		}
	}

	public function getModelMockId() {
		return $this->_get('modelMockId');
	}

	protected function _loadData() {
		return CM_Mysql::select('modelThasIsAnAssetMock', array('bar', 'modelMockId'), array('id' => $this->getId()))->fetchAssoc();
	}

	protected function _onChange() {
		$this->getModelMock()->_change();
	}

	protected function _onDelete() {
		CM_Mysql::delete('modelThasIsAnAssetMock', array('id' => $this->getId()));
	}

	protected static function _create(array $data) {
		return new self(CM_Mysql::insert('modelThasIsAnAssetMock', array('modelMockId' => $data['modelMockId'], 'bar' => $data['bar'])));
	}
}

class CM_ModelAsset_ModelMock_ModelThasIsAnAssetMock extends CM_ModelAsset_Abstract {

	/**
	 * @return CM_ModelThasIsAnAssetMock
	 */
	public function get() {
		if (($modelMock = $this->_cacheGet('modelMock')) === false) {
			try {
				$modelMockId = CM_Mysql::select('modelThasIsAnAssetMock', 'id', array('modelMockId' => $this->_model->getId()))->fetchOne();
				$modelMock = new CM_ModelThasIsAnAssetMock($modelMockId);
				$this->_cacheSet('modelMock', $modelMock);
			} catch (CM_Exception_Nonexistent $ex) {
				$modelMock = null;
			}
		}
		return $modelMock;
	}

	public function _loadAsset() {
		$this->get();
	}

	public function _onModelDelete() {
		if ($this->get()) {
			$this->get()->delete();
		}
	}
}

class CM_ModelMock2 extends CM_Model_Abstract {

	protected function _loadData() {
		return array();
	}

	protected function _loadAssets() {
		return array(new CM_ModelAsset_ModelMock_ModelAssetMock($this));
	}

	public function getData() {
		return $this->_get();
	}

	/**
	 * @return CM_ModelAsset_ModelMock2_ModelAssetMock
	 */
	public function getModelAssetMock() {
		return $this->_getAsset('CM_ModelAsset_Abstract');
	}
}

class CM_ModelAsset_ModelMock_ModelAssetMock extends CM_ModelAsset_Abstract {

	public function _onModelDelete() {
	}

	public function _loadAsset() {
	}

	public function getFoo() {
		if (($foo = $this->_cacheGet('foo')) === false) {
			$foo = 'foo';
			$this->_cacheSet('foo', $foo);
		}
		return $foo;
	}
}
