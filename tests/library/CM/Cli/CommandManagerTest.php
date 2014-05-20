<?php

class CM_Cli_CommandManagerTest extends CMTest_TestCase {

    public function testSingleThread() {
        $commandMock = $this->_getCommandMock(false, false, 1);
        $commandManagerMock = $this->_getCommandManagerMock($commandMock);
        $commandManagerMock->expects($this->never())->method('_findLock');
        $commandManagerMock->expects($this->never())->method('_lockCommand');
        $commandManagerMock->expects($this->never())->method('unlockCommand');
        $this->_runCommandManagerMock($commandManagerMock);
    }

    public function testKeepAlive() {
        $commandMock = $this->_getCommandMock(false, true, 1);
        $commandManagerMock = $this->_getCommandManagerMock($commandMock);
        $commandManagerMock->expects($this->never())->method('_findLock');
        $commandManagerMock->expects($this->never())->method('_lockCommand');
        $commandManagerMock->expects($this->never())->method('unlockCommand');
        $this->_runCommandManagerMock($commandManagerMock);
    }

    public function testFork() {
        $commandMock = $this->_getCommandMock(false, false, 5);
        $commandManagerMock = $this->_getCommandManagerMock($commandMock);
        $commandManagerMock->expects($this->never())->method('_findLock');
        $commandManagerMock->expects($this->never())->method('_lockCommand');
        $commandManagerMock->expects($this->never())->method('unlockCommand');
        $this->_runCommandManagerMock($commandManagerMock, 5);
    }

    public function testForkAndKeepAlive() {
        $commandMock = $this->_getCommandMock(false, true, 5);
        $commandManagerMock = $this->_getCommandManagerMock($commandMock);
        $commandManagerMock->expects($this->never())->method('_findLock');
        $commandManagerMock->expects($this->never())->method('_lockCommand');
        $commandManagerMock->expects($this->never())->method('unlockCommand');
        $this->_runCommandManagerMock($commandManagerMock, 5);
    }

    public function testSingleThreadSynchronized() {
        $commandMock = $this->_getCommandMock(true, false, 1);
        $commandManagerMock = $this->_getCommandManagerMock($commandMock);
        $commandManagerMock->expects($this->once())->method('_findLock')->with($commandMock)->will($this->returnValue(null));
        $commandManagerMock->expects($this->once())->method('_lockCommand')->with($commandMock);
        $commandManagerMock->expects($this->once())->method('unlockCommand')->with($commandMock);
        $this->_runCommandManagerMock($commandManagerMock);
    }

    public function testSingleThreadSynchronizedLocked() {
        $commandMock = $this->_getCommandMock(true, false, 0);
        $commandManagerMock = $this->_getCommandManagerMock($commandMock, "ERROR: Command `package-mock command-mock` still running (process `5432` on host `007f0101`)\n");
        $lock = array('hostId' => '007f0101', 'processId' => '5432');
        $commandManagerMock->expects($this->once())->method('_findLock')->with($commandMock)->will($this->returnValue($lock));
        $commandManagerMock->expects($this->never())->method('_lockCommand');
        $commandManagerMock->expects($this->never())->method('unlockCommand');
        $this->_runCommandManagerMock($commandManagerMock);
    }

    public function testKeepAliveSynchronized() {
        $commandMock = $this->_getCommandMock(true, true, 1);
        $commandManagerMock = $this->_getCommandManagerMock($commandMock);
        $commandManagerMock->expects($this->once())->method('_findLock')->with($commandMock)->will($this->returnValue(null));
        $commandManagerMock->expects($this->once())->method('_lockCommand')->with($commandMock);
        $commandManagerMock->expects($this->once())->method('unlockCommand')->with($commandMock);
        $this->_runCommandManagerMock($commandManagerMock);
    }

    public function testKeepAliveSynchronizedLocked() {
        $commandMock = $this->_getCommandMock(true, true, 0);
        $commandManagerMock = $this->_getCommandManagerMock($commandMock, "ERROR: Command `package-mock command-mock` still running (process `5432` on host `007f0101`)\n");
        $lock = array('hostId' => '007f0101', 'processId' => '5432');
        $commandManagerMock->expects($this->once())->method('_findLock')->with($commandMock)->will($this->returnValue($lock));
        $commandManagerMock->expects($this->never())->method('_lockCommand');
        $commandManagerMock->expects($this->never())->method('unlockCommand');
        $this->_runCommandManagerMock($commandManagerMock);
    }

    public function testForkSynchronized() {
        $commandMock = $this->_getCommandMock(true, false, 5);
        $commandManagerMock = $this->_getCommandManagerMock($commandMock);
        $commandManagerMock->expects($this->once())->method('_findLock')->with($commandMock)->will($this->returnValue(null));
        $commandManagerMock->expects($this->once())->method('_lockCommand')->with($commandMock);
        $commandManagerMock->expects($this->once())->method('unlockCommand')->with($commandMock);
        $this->_runCommandManagerMock($commandManagerMock, 5);
    }

    public function testForkSynchronizedLocked() {
        $commandMock = $this->_getCommandMock(true, false, 0);
        $commandManagerMock = $this->_getCommandManagerMock($commandMock, "ERROR: Command `package-mock command-mock` still running (process `5432` on host `007f0101`)\n");
        $lock = array('hostId' => '007f0101', 'processId' => '5432');
        $commandManagerMock->expects($this->once())->method('_findLock')->with($commandMock)->will($this->returnValue($lock));
        $commandManagerMock->expects($this->never())->method('_lockCommand');
        $commandManagerMock->expects($this->never())->method('unlockCommand');
        $this->_runCommandManagerMock($commandManagerMock, 5);
    }

    public function testForkAndKeepAliveSynchronized() {
        $commandMock = $this->_getCommandMock(true, true, 5);
        $commandManagerMock = $this->_getCommandManagerMock($commandMock);
        $commandManagerMock->expects($this->once())->method('_findLock')->with($commandMock)->will($this->returnValue(null));
        $commandManagerMock->expects($this->once())->method('_lockCommand')->with($commandMock);
        $commandManagerMock->expects($this->once())->method('unlockCommand')->with($commandMock);
        $this->_runCommandManagerMock($commandManagerMock, 5);
    }

    public function testForkAndKeepAliveSynchronizedLocked() {
        $commandMock = $this->_getCommandMock(true, false, 0);
        $commandManagerMock = $this->_getCommandManagerMock($commandMock, "ERROR: Command `package-mock command-mock` still running (process `5432` on host `007f0101`)\n");
        $lock = array('hostId' => '007f0101', 'processId' => '5432');
        $commandManagerMock->expects($this->once())->method('_findLock')->with($commandMock)->will($this->returnValue($lock));
        $commandManagerMock->expects($this->never())->method('_lockCommand');
        $commandManagerMock->expects($this->never())->method('unlockCommand');
        $this->_runCommandManagerMock($commandManagerMock, 5);
    }

    /**
     * @param CM_Cli_Command $commandMock
     * @param string|null    $errorMessageExpected
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function _getCommandManagerMock($commandMock, $errorMessageExpected = null) {
        $keepAliveExpected = $commandMock->getKeepalive();
        $processMock = $this->_getProcessMock($keepAliveExpected);
        $commandManagerMock = $this->getMock('CM_Cli_CommandManager',
            array('getCommands', '_getProcess', '_findLock', '_lockCommand', 'unlockCommand', '_outputError'));
        $commandManagerMock->expects($this->any())->method('getCommands')->will($this->returnValue(array($commandMock)));
        if (null === $errorMessageExpected) {
            $commandManagerMock->expects($this->never())->method('_outputError');
        } else {
            $commandManagerMock->expects($this->once())->method('_outputError')->with($errorMessageExpected);
        }
        $commandManagerMock->expects($this->any())->method('_getProcess')->will($this->returnValue($processMock));
        return $commandManagerMock;
    }

    /**
     * @param bool $synchronized
     * @param bool $keepAlive
     * @param int  $expectedRuns
     * @return CM_Cli_Command
     */
    protected function _getCommandMock($synchronized, $keepAlive, $expectedRuns) {
        $commandMock = $this->getMock('CM_Cli_Command',
            array('getPackageName', '_getMethodName', 'isAbstract', 'getSynchronized', 'getKeepalive', 'run'),
            array(), '', false);
        $commandMock->expects($this->any())->method('getPackageName')->will($this->returnValue('package-mock'));
        $commandMock->expects($this->any())->method('_getMethodName')->will($this->returnValue('command-mock'));
        $commandMock->expects($this->any())->method('isAbstract')->will($this->returnValue(false));
        $commandMock->expects($this->any())->method('getSynchronized')->will($this->returnValue($synchronized));
        $commandMock->expects($this->any())->method('getKeepalive')->will($this->returnValue($keepAlive));
        $commandMock->expects($this->exactly($expectedRuns))->method('run');
        return $commandMock;
    }

    /**
     * @param bool $keepAliveExpected
     * @return CM_Process
     */
    protected function _getProcessMock($keepAliveExpected) {
        $processMock = $this->getMock('CM_Process', array('fork', 'waitForChildren'), array(), '', false);
        $processMock->expects($this->any())->method('fork')->will($this->returnCallback(function ($workload) {
            $workload();
        }));
        $waitForChildrenMock = function ($keepAlive, $terminationCallback) {
            if (isset($terminationCallback)) {
                $terminationCallback();
            }
        };
        $processMock->expects($this->any())->method('waitForChildren')->with($keepAliveExpected, $this->anything())->will($this->returnCallback($waitForChildrenMock));
        return $processMock;
    }

    /**
     * @param PHPUnit_Framework_MockObject_MockObject $commandManagerMock
     * @param int|null                                $forks
     */
    protected function _runCommandManagerMock(PHPUnit_Framework_MockObject_MockObject $commandManagerMock, $forks = null) {
        $forksArgument = isset($forks) ? '--forks=' . $forks : null;
        /** @var CM_Cli_CommandManager $commandManagerMock */
        $commandManagerMock->run(new CM_Cli_Arguments(array('', 'package-mock', 'command-mock', $forksArgument)));
    }
}
