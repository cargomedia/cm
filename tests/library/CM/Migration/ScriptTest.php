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
            ->expects($this->exactly(2))
            ->method('up');

        $time = CMTest_TH::time();
        $script->load();

        $model = CM_Migration_Model::findByName('CM_Migration_Script_123_foo');
        $this->assertInstanceOf('CM_Migration_Model', $model);
        $this->assertTrue($model->hasExecStamp());
        $this->assertSame($time, $model->getExecStamp()->getTimestamp());
        $this->assertSame('CM_Migration_Script_123_foo', $model->getName());

        CMTest_TH::timeForward(10);
        $time = CMTest_TH::time();
        $script->load();

        $model = CM_Migration_Model::findByName('CM_Migration_Script_123_foo');
        $this->assertInstanceOf('CM_Migration_Model', $model);
        $this->assertTrue($model->hasExecStamp());
        $this->assertSame($time, $model->getExecStamp()->getTimestamp());
        $this->assertSame('CM_Migration_Script_123_foo', $model->getName());
    }
}
