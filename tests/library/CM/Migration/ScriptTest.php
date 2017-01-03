<?php

class CM_Migration_ScriptTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testLoad() {
        $sm = $this->getServiceManager();
        /** @var PHPUnit_Framework_MockObject_MockObject|CM_Migration_Script $script */
        $script = $this->getMockBuilder('CM_Migration_Script')
            ->setMethods(['up'])
            ->setMockClassName('CM_Migration_Script_123_foo')
            ->setConstructorArgs([$sm])
            ->getMockForAbstractClass();

        $script
            ->expects($this->once())
            ->method('up');

        $script->load();

        $model = CM_Migration_Model::findByName('123_foo');
        $this->assertInstanceOf('CM_Migration_Model', $model);
        $this->assertTrue($model->hasExecutedAt());
        $this->assertInstanceOf('DateTime', $model->getExecutedAt());
        $this->assertSame('123_foo', $model->getName());
    }
}
