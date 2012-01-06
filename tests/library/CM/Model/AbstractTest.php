<?php
require_once __DIR__ . '/../../../TestCase.php';

class CM_Model_AbstractTest extends TestCase{

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
		TH::clearEnv();
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

	public function testOnload() {
		$modelMock = CM_ModelMock::create(array('foo' => 'bar1'));
		$modelMock = new CM_ModelMock($modelMock->getId());
		$this->assertEquals(0, $modelMock->onLoadCounter);
		$modelMock->_change();
		$modelMock->_get();
		$this->assertEquals(1, $modelMock->onLoadCounter);
	}

	public function testOnChange() {
		$modelMock = CM_ModelMock::create(array('foo' => 'bar1'));
		$modelMock = new CM_ModelMock($modelMock->getId());
		$this->assertEquals(0, $modelMock->onChangeCounter);
		$modelMock->_change();
		$this->assertEquals(1, $modelMock->onChangeCounter);

	}

	public function testSerializable () {
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
}

class CM_ModelMock extends CM_Model_Abstract {

	public $onLoadCounter = 0;
	public $onChangeCounter = 0;

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

	protected function _onDelete() {
		CM_Mysql::delete('modelMock', array('id' => $this->getId()));
	}

	protected function _onLoad() {
		$this->onLoadCounter++;
	}

	protected function _loadAssets() {
		return array(new CM_ModelAsset_ModelMock_ModelThasIsAnAssetMock($this));
	}

	protected static function _create(array $data) {
		return new self(CM_Mysql::insert('modelMock', array('foo' => $data['foo'])));
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

	protected function _onLoad() {
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
				$modelMockId = CM_Mysql::select('modelThasIsAnAssetMock', 'id', array('modelMockId' => $this->_model->getId()));
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
