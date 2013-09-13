<?php

class CM_Paging_ModelAbstractTest extends CMTest_TestCase {

	public static function setUpBeforeClass() {
		CM_Db_Db::exec("CREATE TABLE IF NOT EXISTS `cm_paging_modelabstracttest_modelmock` (
				`id` INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
				`foo` VARCHAR(32)
			) ENGINE=MyISAM AUTO_INCREMENT=".rand(1,1000)." DEFAULT CHARSET=utf8;
		");
	}

	public function testPaging() {
		CM_Config::get()->CM_Model_Abstract->types[CM_Paging_ModelAbstractTest_ModelMock::TYPE] = 'CM_Paging_ModelAbstractTest_ModelMock';
		$model1 = CM_Paging_ModelAbstractTest_ModelMock::create('foo1');
		$model2 = CM_Paging_ModelAbstractTest_ModelMock::create('foo2');
		$source = new CM_PagingSource_Array(array($model1->getId(), $model2->getId(), 999));
		$modelPaging = $this->getMockBuilder('CM_Paging_ModelAbstract')->setMethods(array('_getModelType'))->setConstructorArgs(array($source))
				->getMockForAbstractClass();
		$modelPaging->expects($this->any())->method('_getModelType')->will($this->returnValue(CM_Paging_ModelAbstractTest_ModelMock::TYPE));
		/** @var CM_Paging_ModelAbstract $modelPaging */
		$this->assertCount(3, $modelPaging);
		$this->assertEquals($model1, $modelPaging->getItem(0));
		$this->assertEquals($model2, $modelPaging->getItem(1));
		try {
			$modelPaging->getItem(2);
			$this->fail('Can access nonexistent item.');
		} catch (CM_Exception_Nonexistent $ex) {
			$this->assertTrue(true);
		}
	}
}

class CM_Paging_ModelAbstractTest_ModelMock extends CM_Model_Abstract {

	const TYPE = 1;

	protected function _getSchema() {
		return new CM_Model_Schema_Definition(array('foo' => array()));
	}

	public static function create($foo) {
		$model = new self();
		$model->_set('foo', $foo);
		$model->commit();
		return $model;
	}

	public static function getPersistenceClass() {
		return 'CM_Model_StorageAdapter_Database';
	}
}
