<?php

class CM_Migration_ScriptTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testLoad() {
        $sm = $this->getServiceManager();
        /** @var PHPUnit_Framework_MockObject_MockObject|CM_Migration_Script $mockScript */
        $script = $this->getMockBuilder('CM_Migration_Script')
            ->setMethods(['up'])
            ->setMockClassName('CM_Migration_Script_123_foo')
            ->setConstructorArgs([$sm])
            ->getMockForAbstractClass();
        $output = $this->getMockBuilder('CM_OutputStream_Interface')
            ->setMethods(['write', 'writeln'])
            ->getMock();

        $script
            ->expects($this->once())
            ->method('up');
        $output
            ->expects($this->once())
            ->method('write')
            ->with($this->equalTo('- execute "CM_Migration_Script_123_foo" update script…'));
        $output
            ->expects($this->exactly(2))
            ->method('writeln')
            ->withConsecutive(
                [$this->equalTo('done')],
                [$this->equalTo('- "CM_Migration_Script_123_foo" already loaded')]
            );

        $time = CMTest_TH::time();
        $script->load($output);
        CMTest_TH::timeForward(10);
        $script->load($output);

        $model = CM_Migration_Model::findByName('CM_Migration_Script_123_foo');
        $this->assertInstanceOf('CM_Migration_Model', $model);
        $this->assertTrue($model->hasExecStamp());
        $this->assertSame($time, $model->getExecStamp()->getTimestamp());
        $this->assertSame('CM_Migration_Script_123_foo', $model->getName());
    }
}
