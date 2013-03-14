<?php

class CM_Model_Entity_AbstractTest extends CMTest_TestCase{

	public static function setupBeforeClass() {
		CM_Db_Db::exec("CREATE TABLE IF NOT EXISTS `entityMock` (
				`id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
				`userId` INT UNSIGNED NOT NULL,
				`foo` VARCHAR(32),
				KEY (`userId`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;
		");
	}

	public static function tearDownAfterClass() {
		CM_Db_Db::exec("DROP TABLE `entityMock`");
	}

	public function setup() {
	}

	public function tearDown() {
		CM_Db_Db::truncate('entityMock');
		CMTest_TH::clearEnv();
	}

	public function testGetUserId() {
		$user = CM_Model_User::create();
		CM_Model_Entity_Mock::create(array('userId' => $user->getId(), 'foo' => 'bar1'));
		$entityMock = new CM_Model_Entity_Mock(1);
		$this->assertSame($user->getId(), $entityMock->getUserId());
	}

	public function testGetUser() {
		$user = CM_Model_User::create();
		$user2 = CM_Model_User::create();
		CM_Model_Entity_Mock::create(array('userId' => $user->getId(), 'foo' => 'bar1'));
		$entityMock = new CM_Model_Entity_Mock(1);
		$this->assertEquals($user->getId(), $entityMock->getUser()->getId());
		$this->assertInstanceOf('CM_Model_User', $user);

		$this->assertNotEquals($user2, $entityMock->getUser());
		CM_Mysql::delete(TBL_CM_USER, array('userId' => $user->getId()));
		CMTest_TH::clearCache();
		try {
			$entityMock->getUser();
			$this->fail('User not deleted');
		} catch (CM_Exception_Nonexistent $ex) {
			$this->assertTrue(true);
		}
	}
}

class CM_Model_Entity_Mock extends CM_Model_Entity_Abstract {

	public $onLoadCounter = 0;
	public $onChangeCounter = 0;

	public function getPath() {
		return null;
	}

	public function getFoo() {
		return (string) $this->_get('foo');
	}


	protected function _loadData() {
		return CM_Db_Db::select('entityMock', array('userId', 'foo'), array('id' => $this->getId()))->fetch();
	}

	protected function _onChange() {
	}

	protected function _onDelete() {
		CM_Mysql::delete('entityMock', array('id' => $this->getId()));
	}

	protected function _onLoad() {
	}

	protected static function _create(array $data) {
		return new self(CM_Db_Db::insert('entityMock', array('userId' => $data['userId'], 'foo' => $data['foo'])));
	}

}
