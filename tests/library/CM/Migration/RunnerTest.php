<?php

class CM_Migration_RunnerTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testLoad() {
        $sm = $this->getServiceManager();
        /** @var PHPUnit_Framework_MockObject_MockObject|CM_Migration_UpgradableInterface $migration */
        $migration = $this->getMockBuilder('CM_Migration_UpgradableInterface')
            ->setMethods(['up'])
            ->setMockClassName('Migration_123_foo')
            ->getMock();
        $migration
            ->expects($this->once())
            ->method('up');

        /** @var PHPUnit_Framework_MockObject_MockObject|CM_Migration_Runner $runner */
        $runner = $this->getMockBuilder('CM_Migration_Runner')
            ->setMethods(['getName'])
            ->setConstructorArgs([$migration, $sm])
            ->getMock();
        $runner
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('123_foo'));

        $runner->load(new CM_OutputStream_Null());

        CMTest_TH::clearCache();

        $model = CM_Migration_Model::findByName('123_foo');
        $this->assertInstanceOf('CM_Migration_Model', $model);
        $this->assertTrue($model->hasExecutedAt());
        $this->assertInstanceOf('DateTime', $model->getExecutedAt());
        $this->assertSame('123_foo', $model->getName());
    }
}
