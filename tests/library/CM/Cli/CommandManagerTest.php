<?php

class CM_Cli_CommandManagerTest extends CMTest_TestCase {

    public function testSingleThread() {
        $commandMock = $this->_getCommandMock(false, false, 1);
        $commandManagerMock = $this->_getCommandManagerMock($commandMock);
        $commandManagerMock->expects($this->never())->method('_findLock');
        $commandManagerMock->expects($this->never())->method('_lockCommand');
        $commandManagerMock->expects($this->never())->method('unlockCommand');
        $this->assertSame(0, $this->_runCommandManagerMock($commandManagerMock));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testCommandThrowingError() {
        $process = CM_Process::getInstance();
        $commandMock = $this->_getCommandMock(false, false, null, function () {
            throw new Exception('foo');
        });
        $commandManagerMock = $this->_getCommandManagerMock($commandMock, 'Exception: foo', $process);
        $commandManagerMock->expects($this->never())->method('_findLock');
        $commandManagerMock->expects($this->never())->method('_lockCommand');
        $commandManagerMock->expects($this->never())->method('unlockCommand');
        $this->assertSame(1, $this->_runCommandManagerMock($commandManagerMock));
    }

    public function testKeepAlive() {
        $commandMock = $this->_getCommandMock(false, true, 1);
        $commandManagerMock = $this->_getCommandManagerMock($commandMock);
        $commandManagerMock->expects($this->never())->method('_findLock');
        $commandManagerMock->expects($this->never())->method('_lockCommand');
        $commandManagerMock->expects($this->never())->method('unlockCommand');
        $this->assertSame(0, $this->_runCommandManagerMock($commandManagerMock));
    }

    public function testFork() {
        $commandMock = $this->_getCommandMock(false, false, 5);
        $commandManagerMock = $this->_getCommandManagerMock($commandMock);
        $commandManagerMock->expects($this->never())->method('_findLock');
        $commandManagerMock->expects($this->never())->method('_lockCommand');
        $commandManagerMock->expects($this->never())->method('unlockCommand');
        $this->assertSame(0, $this->_runCommandManagerMock($commandManagerMock, 5));
    }

    public function testForkAndKeepAlive() {
        $commandMock = $this->_getCommandMock(false, true, 5);
        $commandManagerMock = $this->_getCommandManagerMock($commandMock);
        $commandManagerMock->expects($this->never())->method('_findLock');
        $commandManagerMock->expects($this->never())->method('_lockCommand');
        $commandManagerMock->expects($this->never())->method('unlockCommand');
        $this->assertSame(0, $this->_runCommandManagerMock($commandManagerMock, 5));
    }

    public function testSingleThreadSynchronized() {
        $commandMock = $this->_getCommandMock(true, false, 1);
        $commandManagerMock = $this->_getCommandManagerMock($commandMock);
        $commandManagerMock->expects($this->once())->method('_findLock')->with($commandMock)->will($this->returnValue(null));
        $commandManagerMock->expects($this->once())->method('_lockCommand')->with($commandMock);
        $commandManagerMock->expects($this->once())->method('unlockCommand')->with($commandMock);
        $this->assertSame(0, $this->_runCommandManagerMock($commandManagerMock));
    }

    public function testSingleThreadSynchronizedLocked() {
        $commandMock = $this->_getCommandMock(true, false, 0);
        $commandManagerMock = $this->_getCommandManagerMock($commandMock, "ERROR: Command `package-mock command-mock` still running (process `5432` on machine `my-machine-1`)\n");
        $lock = array('machineId' => 'my-machine-1', 'processId' => '5432');
        $commandManagerMock->expects($this->once())->method('_findLock')->with($commandMock)->will($this->returnValue($lock));
        $commandManagerMock->expects($this->never())->method('_lockCommand');
        $commandManagerMock->expects($this->never())->method('unlockCommand');
        $this->assertSame(1, $this->_runCommandManagerMock($commandManagerMock));
    }

    public function testKeepAliveSynchronized() {
        $commandMock = $this->_getCommandMock(true, true, 1);
        $commandManagerMock = $this->_getCommandManagerMock($commandMock);
        $commandManagerMock->expects($this->once())->method('_findLock')->with($commandMock)->will($this->returnValue(null));
        $commandManagerMock->expects($this->once())->method('_lockCommand')->with($commandMock);
        $commandManagerMock->expects($this->once())->method('unlockCommand')->with($commandMock);
        $this->assertSame(0, $this->_runCommandManagerMock($commandManagerMock));
    }

    public function testKeepAliveSynchronizedLocked() {
        $commandMock = $this->_getCommandMock(true, true, 0);
        $commandManagerMock = $this->_getCommandManagerMock($commandMock, "ERROR: Command `package-mock command-mock` still running (process `5432` on machine `my-machine-1`)\n");
        $lock = array('machineId' => 'my-machine-1', 'processId' => '5432');
        $commandManagerMock->expects($this->once())->method('_findLock')->with($commandMock)->will($this->returnValue($lock));
        $commandManagerMock->expects($this->never())->method('_lockCommand');
        $commandManagerMock->expects($this->never())->method('unlockCommand');
        $this->assertSame(1, $this->_runCommandManagerMock($commandManagerMock));
    }

    public function testForkSynchronized() {
        $commandMock = $this->_getCommandMock(true, false, 5);
        $commandManagerMock = $this->_getCommandManagerMock($commandMock);
        $commandManagerMock->expects($this->once())->method('_findLock')->with($commandMock)->will($this->returnValue(null));
        $commandManagerMock->expects($this->once())->method('_lockCommand')->with($commandMock);
        $commandManagerMock->expects($this->once())->method('unlockCommand')->with($commandMock);
        $this->assertSame(0, $this->_runCommandManagerMock($commandManagerMock, 5));
    }

    public function testForkSynchronizedLocked() {
        $commandMock = $this->_getCommandMock(true, false, 0);
        $commandManagerMock = $this->_getCommandManagerMock($commandMock, "ERROR: Command `package-mock command-mock` still running (process `5432` on machine `my-machine-1`)\n");
        $lock = array('machineId' => 'my-machine-1', 'processId' => '5432');
        $commandManagerMock->expects($this->once())->method('_findLock')->with($commandMock)->will($this->returnValue($lock));
        $commandManagerMock->expects($this->never())->method('_lockCommand');
        $commandManagerMock->expects($this->never())->method('unlockCommand');
        $this->assertSame(1, $this->_runCommandManagerMock($commandManagerMock, 5));
    }

    public function testForkAndKeepAliveSynchronized() {
        $commandMock = $this->_getCommandMock(true, true, 5);
        $commandManagerMock = $this->_getCommandManagerMock($commandMock);
        $commandManagerMock->expects($this->once())->method('_findLock')->with($commandMock)->will($this->returnValue(null));
        $commandManagerMock->expects($this->once())->method('_lockCommand')->with($commandMock);
        $commandManagerMock->expects($this->once())->method('unlockCommand')->with($commandMock);
        $this->assertSame(0, $this->_runCommandManagerMock($commandManagerMock, 5));
    }

    public function testForkAndKeepAliveSynchronizedLocked() {
        $commandMock = $this->_getCommandMock(true, false, 0);
        $commandManagerMock = $this->_getCommandManagerMock($commandMock, "ERROR: Command `package-mock command-mock` still running (process `5432` on machine `my-machine-1`)\n");
        $lock = array('machineId' => 'my-machine-1', 'processId' => '5432');
        $commandManagerMock->expects($this->once())->method('_findLock')->with($commandMock)->will($this->returnValue($lock));
        $commandManagerMock->expects($this->never())->method('_lockCommand');
        $commandManagerMock->expects($this->never())->method('unlockCommand');
        $this->assertSame(1, $this->_runCommandManagerMock($commandManagerMock, 5));
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
        $processMock = $this->mockClass('CM_Process');
        $processMock->mockMethod('fork');

        $command = $this->mockClass('CM_Cli_Command')->newInstanceWithoutConstructor();
        $command->mockMethod('getSynchronized');
        $command->mockMethod('getKeepalive');
        $command->mockMethod('extractParameters');

        $commandManager = $this->mockObject('CM_Cli_CommandManager');
        $commandManager->mockMethod('_getCommand')->set($command);
        $commandManager->mockMethod('_getProcess')
            ->at(0, function () use ($processMock) {
                $processSuccess = $processMock->newInstance();
                $processSuccess->mockMethod('waitForChildren')->set([
                    new CM_Process_Result(0),
                    new CM_Process_Result(0),
                    new CM_Process_Result(0),
                    new CM_Process_Result(0),
                ]);
                return $processSuccess;
            })
            ->at(1, function () use ($processMock) {
                $processFailure = $processMock->newInstance();
                $processFailure->mockMethod('waitForChildren')->set([
                    new CM_Process_Result(0),
                    new CM_Process_Result(1),
                    new CM_Process_Result(0),
                    new CM_Process_Result(0),
                ]);
                return $processFailure;
            });

        /** @var CM_Cli_CommandManager $commandManager */
        $this->assertSame(0, $commandManager->run(new CM_Cli_Arguments(['bin/cm', 'foo', 'bar'])));
        $this->assertSame(1, $commandManager->run(new CM_Cli_Arguments(['bin/cm', 'foo', 'bar'])));
    }

    /**
     * @param CM_Cli_Command  $commandMock
     * @param string|null     $errorMessageExpected
     * @param CM_Process|null $process
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getCommandManagerMock($commandMock, $errorMessageExpected = null, CM_Process $process = null) {
        $keepAliveExpected = $commandMock->getKeepalive();
        if (null === $process) {
            $process = $this->_getProcessMock($keepAliveExpected);
        }
        $mockBuilder = $this->getMockBuilder('CM_Cli_CommandManager');
        $mockBuilder->setMethods(['getCommands', '_getProcess', '_findLock', '_lockCommand', 'unlockCommand', '_outputError',
            '_checkUnusedArguments']);
        $commandManagerMock = $mockBuilder->getMock();
        $commandManagerMock->expects($this->any())->method('getCommands')->will($this->returnValue(array($commandMock)));
        if (null === $errorMessageExpected) {
            $commandManagerMock->expects($this->never())->method('_outputError');
        } else {
            $commandManagerMock
                ->expects($this->any())
                ->method('_outputError')
                ->with($this->callback(function ($errorMessage) use ($errorMessageExpected) {
                    return false !== strpos($errorMessage, $errorMessageExpected);
                }));
        }
        $commandManagerMock->expects($this->any())->method('_getProcess')->will($this->returnValue($process));
        $commandManagerMock->expects($this->any())->method('_checkUnusedArguments');
        return $commandManagerMock;
    }

    /**
     * @param bool          $synchronized
     * @param bool          $keepAlive
     * @param int           $expectedRuns
     * @param callable|null $run
     * @return CM_Cli_Command|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getCommandMock($synchronized, $keepAlive, $expectedRuns, callable $run = null) {
        if (null === $run) {
            $run = function () {
            };
        }
        if (null === $expectedRuns) {
            $expectedRuns = $this->any();
        } else {
            $expectedRuns = $this->exactly($expectedRuns);
        }

        $mockBuilder = $this->getMockBuilder('CM_Cli_Command');
        $mockBuilder->setMethods(['getPackageName', '_getMethodName', 'isAbstract', 'getSynchronized', 'getKeepalive', 'run', 'extractParameters']);
        $mockBuilder->disableOriginalConstructor();
        $commandMock = $mockBuilder->getMock();
        $commandMock->expects($this->any())->method('getPackageName')->will($this->returnValue('package-mock'));
        $commandMock->expects($this->any())->method('_getMethodName')->will($this->returnValue('command-mock'));
        $commandMock->expects($this->any())->method('isAbstract')->will($this->returnValue(false));
        $commandMock->expects($this->any())->method('getSynchronized')->will($this->returnValue($synchronized));
        $commandMock->expects($this->any())->method('getKeepalive')->will($this->returnValue($keepAlive));
        $commandMock->expects($this->any())->method('extractParameters')->will($this->returnValue([]));
        $commandMock->expects($expectedRuns)->method('run')->willReturnCallback($run);
        return $commandMock;
    }

    /**
     * @param bool $keepAliveExpected
     * @return CM_Process|PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getProcessMock($keepAliveExpected) {
        $mockBuilder = $this->getMockBuilder('CM_Process');
        $mockBuilder->setMethods(['fork', 'waitForChildren']);
        $mockBuilder->disableOriginalConstructor();
        $processMock = $mockBuilder->getMock();
        $processMock->expects($this->any())->method('fork')->will($this->returnCallback(function ($workload) {
            $workload();
        }));
        $waitForChildrenMock = function ($keepAlive) {
            return array();
        };
        $processMock->expects($this->any())->method('waitForChildren')->with($keepAliveExpected)->will($this->returnCallback($waitForChildrenMock));
        return $processMock;
    }

    /**
     * @param PHPUnit_Framework_MockObject_MockObject $commandManagerMock
     * @param int|null                                $forks
     * @return int
     */
    protected function _runCommandManagerMock(PHPUnit_Framework_MockObject_MockObject $commandManagerMock, $forks = null) {
        $forksArgument = isset($forks) ? '--forks=' . $forks : null;
        /** @var CM_Cli_CommandManager $commandManagerMock */
        return $commandManagerMock->run(new CM_Cli_Arguments(array('', 'package-mock', 'command-mock', $forksArgument)));
    }
}
