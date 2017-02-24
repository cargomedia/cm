<?php

class CM_Cli_CommandManagerTest extends CMTest_TestCase {

    public function testRunRegular() {
        $commandMock = $this->_getCommandMock(false, true);
        $commandManagerMock = $this->_getCommandManagerMock($commandMock);
        $commandManagerMock->expects($this->never())->method('_findLock');
        $commandManagerMock->expects($this->never())->method('_lockCommand');
        $commandManagerMock->expects($this->never())->method('unlockCommand');
        $this->_runCommandManagerMock($commandManagerMock);
    }

    public function testRunSynchronized() {
        $commandMock = $this->_getCommandMock(true, true);
        $commandManagerMock = $this->_getCommandManagerMock($commandMock);
        $commandManagerMock->expects($this->once())->method('_findLock')->with($commandMock)->will($this->returnValue(null));
        $commandManagerMock->expects($this->once())->method('_lockCommand')->with($commandMock);
        $commandManagerMock->expects($this->once())->method('unlockCommand')->with($commandMock);
        $this->_runCommandManagerMock($commandManagerMock);
    }

    public function testRunSynchronizedLocked() {
        $commandMock = $this->_getCommandMock(true, false);
        $commandManagerMock = $this->_getCommandManagerMock($commandMock, "ERROR: Command `package-mock command-mock` still running (process `5432` on machine `my-machine-1`)\n");
        $lock = array('machineId' => 'my-machine-1', 'processId' => '5432');
        $commandManagerMock->expects($this->once())->method('_findLock')->with($commandMock)->will($this->returnValue($lock));
        $commandManagerMock->expects($this->never())->method('_lockCommand');
        $commandManagerMock->expects($this->never())->method('unlockCommand');
        $this->_runCommandManagerMock($commandManagerMock);
    }

    public function testMonitorSynchronizedCommands() {
        $mockBuilder = $this->getMockBuilder('CM_Process');
        $mockBuilder->setMethods(['isRunning']);
        $mockBuilder->disableOriginalConstructor();
        $processMock = $mockBuilder->getMock();
        $processMock->expects($this->any())->method('isRunning')->will($this->returnCallback(function ($processId) {
            return $processId !== 3;
        }));
        $mockBuilder = $this->getMockBuilder('CM_Cli_CommandManager');
        $mockBuilder->setMethods(['_getProcess', '_getMachineId']);
        $commandManagerMock = $mockBuilder->getMock();
        $commandManagerMock->expects($this->any())->method('_getProcess')->will($this->returnValue($processMock));
        $commandManagerMock->expects($this->any())->method('_getMachineId')->will($this->returnValue('my-machine-1'));
        CM_Db_Db::insert('cm_cli_command_manager_process',
            array('commandName' => 'command-mock1', 'machineId' => 'my-machine-1', 'processId' => 1, 'timeoutStamp' => time() + 60));
        CM_Db_Db::insert('cm_cli_command_manager_process',
            array('commandName' => 'command-mock2', 'machineId' => 'my-machine-1', 'processId' => 2, 'timeoutStamp' => time() - 60));
        CM_Db_Db::insert('cm_cli_command_manager_process',
            array('commandName' => 'command-mock3', 'machineId' => 'my-machine-1', 'processId' => 3, 'timeoutStamp' => time() + 60));
        CM_Db_Db::insert('cm_cli_command_manager_process',
            array('commandName' => 'command-mock4', 'machineId' => 'my-machine-2', 'processId' => 4, 'timeoutStamp' => time() + 60));
        CM_Db_Db::insert('cm_cli_command_manager_process',
            array('commandName' => 'command-mock5', 'machineId' => 'my-machine-2', 'processId' => 5, 'timeoutStamp' => time() - 60));
        /** @var CM_Cli_CommandManager $commandManagerMock */
        $commandManagerMock->monitorSynchronizedCommands();
        $timeoutStampExpected = time() + CM_Cli_CommandManager::TIMEOUT;
        $this->assertRow('cm_cli_command_manager_process', array('commandName' => 'command-mock1', 'timeoutStamp' => $timeoutStampExpected));
        $this->assertRow('cm_cli_command_manager_process', array('commandName' => 'command-mock2', 'timeoutStamp' => $timeoutStampExpected));
        $this->assertNotRow('cm_cli_command_manager_process', array('commandName' => 'command-mock3'));
        $this->assertRow('cm_cli_command_manager_process', array('commandName' => 'command-mock4', 'timeoutStamp' => time() + 60));
        $this->assertNotRow('cm_cli_command_manager_process', array('commandName' => 'command-mock5'));
        CM_Db_Db::truncate('cm_cli_command_manager_process');
    }

    public function testRunExitCode() {
        $command = $this->mockClass('CM_Cli_Command')->newInstanceWithoutConstructor();
        $command->mockMethod('getSynchronized');
        $command->mockMethod('extractParameters')->set([]);
        $command->mockMethod('run')
            ->at(0, function () {
            })
            ->at(1, function () {
                throw new Exception('Big Fucking Error');
            });

        $commandManager = $this->mockObject('CM_Cli_CommandManager');
        $commandManager->mockMethod('_getCommand')->set($command);
        $mockOutputError = $commandManager->mockMethod('_outputError')->at(0, function ($message) {
            $this->assertStringStartsWith('Exception: Big Fucking Error', $message);
        });

        /** @var CM_Cli_CommandManager $commandManager */
        $this->assertSame(0, $commandManager->run(new CM_Cli_Arguments(['bin/cm', 'foo', 'bar'])));
        $this->assertSame(1, $commandManager->run(new CM_Cli_Arguments(['bin/cm', 'foo', 'bar'])));
        $this->assertSame(1, $mockOutputError->getCallCount());
    }

    /**
     * @param CM_Cli_Command $commandMock
     * @param string|null    $errorMessageExpected
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getCommandManagerMock($commandMock, $errorMessageExpected = null) {
        $mockBuilder = $this->getMockBuilder('CM_Cli_CommandManager');
        $mockBuilder->setMethods(['getCommands', '_findLock', '_lockCommand', 'unlockCommand', '_outputError',
            '_checkUnusedArguments']);
        $commandManagerMock = $mockBuilder->getMock();
        $commandManagerMock->expects($this->any())->method('getCommands')->will($this->returnValue(array($commandMock)));
        if (null === $errorMessageExpected) {
            $commandManagerMock->expects($this->never())->method('_outputError');
        } else {
            $commandManagerMock->expects($this->once())->method('_outputError')->with($errorMessageExpected);
        }
        $commandManagerMock->expects($this->any())->method('_checkUnusedArguments');
        return $commandManagerMock;
    }

    /**
     * @param boolean $synchronized
     * @param boolean $expectedSuccess
     * @return CM_Cli_Command
     */
    protected function _getCommandMock($synchronized, $expectedSuccess) {
        $mockBuilder = $this->getMockBuilder('CM_Cli_Command');
        $mockBuilder->setMethods(['getPackageName', '_getMethodName', 'isAbstract', 'getSynchronized', 'run', 'extractParameters']);
        $mockBuilder->disableOriginalConstructor();
        $commandMock = $mockBuilder->getMock();
        $commandMock->expects($this->any())->method('getPackageName')->will($this->returnValue('package-mock'));
        $commandMock->expects($this->any())->method('_getMethodName')->will($this->returnValue('command-mock'));
        $commandMock->expects($this->any())->method('isAbstract')->will($this->returnValue(false));
        $commandMock->expects($this->any())->method('getSynchronized')->will($this->returnValue($synchronized));
        $commandMock->expects($this->any())->method('extractParameters')->will($this->returnValue([]));
        if ($expectedSuccess) {
            $commandMock->expects($this->exactly(1))->method('run');
        } else {
            $commandMock->expects($this->never())->method('run');
        }
        return $commandMock;
    }

    /**
     * @param PHPUnit_Framework_MockObject_MockObject $commandManagerMock
     */
    protected function _runCommandManagerMock(PHPUnit_Framework_MockObject_MockObject $commandManagerMock) {
        /** @var CM_Cli_CommandManager $commandManagerMock */
        $commandManagerMock->run(new CM_Cli_Arguments(array('', 'package-mock', 'command-mock')));
    }
}
