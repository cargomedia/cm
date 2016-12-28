<?php

class CM_Provision_Script_MigrationTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testLoad() {
        $sm = $this->getServiceManager();
        /** @var PHPUnit_Framework_MockObject_MockObject|CM_Provision_Script_Migration $mockScript */
        $script = $this->getMockBuilder('CM_Provision_Script_Migration')
            ->setMethods(['up'])
            ->setMockClassName('CM_Provision_Script_Migration_123_foo')
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
            ->with($this->equalTo('- execute "CM_Provision_Script_Migration_123_foo" update script…'));
        $output
            ->expects($this->exactly(2))
            ->method('writeln')
            ->withConsecutive(
                [$this->equalTo('done')],
                [$this->equalTo('- "CM_Provision_Script_Migration_123_foo" already loaded')]
            );

        $time = CMTest_TH::time();
        $script->load($output);
        CMTest_TH::timeForward(10);
        $script->load($output);

        $model = CM_Model_Migration::findByName('CM_Provision_Script_Migration_123_foo');
        $this->assertInstanceOf('CM_Model_Migration', $model);
        $this->assertTrue($model->hasExecStamp());
        $this->assertSame($time, $model->getExecStamp()->getTimestamp());
        $this->assertSame('CM_Provision_Script_Migration_123_foo', $model->getName());
    }
}
