<?php

class CM_Model_MigrationTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testCreate() {
        $model = CM_Model_Migration::create('foo');
        $this->assertSame('foo', $model->getName());
        $this->assertNull($model->getExecStamp());
        $this->assertFalse($model->hasExecStamp());

        /** @var CM_Db_Exception $exception */
        $exception = $this->catchException(function () {
            CM_Model_Migration::create('foo');
        });
        $this->assertInstanceOf('CM_Db_Exception', $exception);
        $this->assertSame('Cannot execute SQL statement', $exception->getMessage());
        $this->assertSame([
            'tries'                    => 0,
            'originalExceptionMessage' => "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'foo' for key 'name'",
            'query'                    => 'INSERT INTO `cm_model_migration` (`name`) VALUES (?)',
        ], $exception->getMetaInfo());
    }

    public function testFindByName() {
        $model = CM_Model_Migration::findByName('foo');
        $this->assertNull($model);

        CM_Model_Migration::create('foo');

        $model = CM_Model_Migration::findByName('foo');
        $this->assertSame('foo', $model->getName());
        $this->assertNull($model->getExecStamp());
        $this->assertFalse($model->hasExecStamp());

        $date = new DateTime();
        $model->setExecStamp($date);

        $model = CM_Model_Migration::findByName('foo');
        $this->assertSame('foo', $model->getName());
        $this->assertEquals($date, $model->getExecStamp());
        $this->assertTrue($model->hasExecStamp());
    }
}
