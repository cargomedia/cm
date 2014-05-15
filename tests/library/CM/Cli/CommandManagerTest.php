<?php

class CM_Cli_CommandManagerTest extends CMTest_TestCase {

    public function testSingleThread() {
        $commandMock = $this->getMock('CM_Cli_Command',
            array('getPackageName', '_getMethodName', 'isAbstract', 'getSynchronized', 'getKeepalive', 'run'),
            array(), '', false);
        $commandMock->expects($this->any())->method('getPackageName')->will($this->returnValue('package-mock'));
        $commandMock->expects($this->any())->method('_getMethodName')->will($this->returnValue('command-mock'));
        $commandMock->expects($this->any())->method('isAbstract')->will($this->returnValue(false));
        $commandMock->expects($this->any())->method('getSynchronized')->will($this->returnValue(false));
        $commandMock->expects($this->any())->method('getKeepalive')->will($this->returnValue(false));
        $commandMock->expects($this->once())->method('run');

        $processMock = $this->getMock('CM_Process', array('fork'), array(), '', false);
        $processMock->expects($this->never())->method('fork');

        $commandManagerMock = $this->getMock('CM_Cli_CommandManager', array('getCommands', '_getProcess'));
        $commandManagerMock->expects($this->any())->method('getCommands')->will($this->returnValue(array($commandMock)));
        $commandManagerMock::staticExpects($this->any())->method('_getProcess')->will($this->returnValue($processMock));

        /** @var CM_Cli_CommandManager $commandManagerMock */
        $commandManagerMock->run(new CM_Cli_Arguments(array('', 'package-mock', 'command-mock')));
    }

    public function testFork() {
        $commandMock = $this->getMock('CM_Cli_Command',
            array('getPackageName', '_getMethodName', 'isAbstract', 'getSynchronized', 'getKeepalive', 'run'),
            array(), '', false);
        $commandMock->expects($this->any())->method('getPackageName')->will($this->returnValue('package-mock'));
        $commandMock->expects($this->any())->method('_getMethodName')->will($this->returnValue('command-mock'));
        $commandMock->expects($this->any())->method('isAbstract')->will($this->returnValue(false));
        $commandMock->expects($this->any())->method('getSynchronized')->will($this->returnValue(false));
        $commandMock->expects($this->any())->method('getKeepalive')->will($this->returnValue(false));
        $commandMock->expects($this->once())->method('run');

        $processMock = $this->getMock('CM_Process', array('fork'), array(), '', false);
        $processMock->expects($this->once())->method('fork')->with(5, false, null);

        $commandManagerMock = $this->getMock('CM_Cli_CommandManager', array('getCommands', '_getProcess'));
        $commandManagerMock->expects($this->any())->method('getCommands')->will($this->returnValue(array($commandMock)));
        $commandManagerMock::staticExpects($this->any())->method('_getProcess')->will($this->returnValue($processMock));

        /** @var CM_Cli_CommandManager $commandManagerMock */
        $commandManagerMock->run(new CM_Cli_Arguments(array('', 'package-mock', 'command-mock', '--forks=5')));
    }

    public function testForkAndKeepAlive() {
        $commandMock = $this->getMock('CM_Cli_Command',
            array('getPackageName', '_getMethodName', 'isAbstract', 'getSynchronized', 'getKeepalive', 'run'),
            array(), '', false);
        $commandMock->expects($this->any())->method('getPackageName')->will($this->returnValue('package-mock'));
        $commandMock->expects($this->any())->method('_getMethodName')->will($this->returnValue('command-mock'));
        $commandMock->expects($this->any())->method('isAbstract')->will($this->returnValue(false));
        $commandMock->expects($this->any())->method('getSynchronized')->will($this->returnValue(false));
        $commandMock->expects($this->any())->method('getKeepalive')->will($this->returnValue(true));
        $commandMock->expects($this->once())->method('run');

        $processMock = $this->getMock('CM_Process', array('fork'), array(), '', false);
        $processMock->expects($this->once())->method('fork')->with(5, true, null);

        $commandManagerMock = $this->getMock('CM_Cli_CommandManager', array('getCommands', '_getProcess'));
        $commandManagerMock->expects($this->any())->method('getCommands')->will($this->returnValue(array($commandMock)));
        $commandManagerMock::staticExpects($this->any())->method('_getProcess')->will($this->returnValue($processMock));

        /** @var CM_Cli_CommandManager $commandManagerMock */
        $commandManagerMock->run(new CM_Cli_Arguments(array('', 'package-mock', 'command-mock', '--forks=5')));
    }

    public function testKeepAlive() {
        $commandMock = $this->getMock('CM_Cli_Command',
            array('getPackageName', '_getMethodName', 'isAbstract', 'getSynchronized', 'getKeepalive', 'run'),
            array(), '', false);
        $commandMock->expects($this->any())->method('getPackageName')->will($this->returnValue('package-mock'));
        $commandMock->expects($this->any())->method('_getMethodName')->will($this->returnValue('command-mock'));
        $commandMock->expects($this->any())->method('isAbstract')->will($this->returnValue(false));
        $commandMock->expects($this->any())->method('getSynchronized')->will($this->returnValue(false));
        $commandMock->expects($this->any())->method('getKeepalive')->will($this->returnValue(true));
        $commandMock->expects($this->once())->method('run');

        $processMock = $this->getMock('CM_Process', array('fork'), array(), '', false);
        $processMock->expects($this->once())->method('fork')->with(1, true, null);

        $commandManagerMock = $this->getMock('CM_Cli_CommandManager', array('getCommands', '_getProcess'));
        $commandManagerMock->expects($this->any())->method('getCommands')->will($this->returnValue(array($commandMock)));
        $commandManagerMock::staticExpects($this->any())->method('_getProcess')->will($this->returnValue($processMock));

        /** @var CM_Cli_CommandManager $commandManagerMock */
        $commandManagerMock->run(new CM_Cli_Arguments(array('', 'package-mock', 'command-mock')));
    }
}
