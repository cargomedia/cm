<?php

class CM_Migration_ModelTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testCreate() {
        $model = CM_Migration_Model::create('foo');
        $this->assertSame('foo', $model->getName());
        $this->assertNull($model->getExecutedAt());
        $this->assertFalse($model->hasExecutedAt());

        /** @var CM_Db_Exception $exception */
        $exception = $this->catchException(function () {
            CM_Migration_Model::create('foo');
        });
        $this->assertInstanceOf('CM_Db_Exception', $exception);
        $this->assertSame('Cannot execute SQL statement', $exception->getMessage());
        $this->assertSame([
            'tries'                    => 0,
            'originalExceptionMessage' => "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'foo' for key 'name'",
            'query'                    => 'INSERT INTO `cm_migration` (`name`) VALUES (?)',
        ], $exception->getMetaInfo());
    }

    public function testFindByName() {
        $model = CM_Migration_Model::findByName('foo');
        $this->assertNull($model);

        CM_Migration_Model::create('foo');

        $model = CM_Migration_Model::findByName('foo');
        $this->assertNull($model);

        CMTest_TH::clearCache();

        $model = CM_Migration_Model::findByName('foo');
        $this->assertSame('foo', $model->getName());
        $this->assertNull($model->getExecutedAt());
        $this->assertFalse($model->hasExecutedAt());
    }

    public function testSetExecutedAt() {
        $model = CM_Migration_Model::create('foo');

        $date = new DateTime();
        $model->setExecutedAt($date);
        $this->assertEquals($date, $model->getExecutedAt());
        $this->assertTrue($model->hasExecutedAt());

        $model->setExecutedAt();
        $this->assertNull($model->getExecutedAt());
        $this->assertFalse($model->hasExecutedAt());
    }
}
