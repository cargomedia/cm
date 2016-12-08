<?php

require_once dirname(dirname(__DIR__)) . '/bootstrap.php'; // Bootstrap the test explicitly when running in a separate process

class CM_ProcessTest extends CMTest_TestCase {

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testFork() {
        $process = CM_Process::getInstance();
        $forkHandler1 = $process->fork(function () {
        });
        $forkHandler2 = $process->fork(function () {
        });
        $forkHandler3 = $process->fork(function () {
        });
        $this->assertSame(1, $forkHandler2->getIdentifier() - $forkHandler1->getIdentifier());
        $this->assertSame(1, $forkHandler3->getIdentifier() - $forkHandler2->getIdentifier());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testBindOnFork() {
        /** @var CM_Process|\Mocka\AbstractClassTrait $process */
        $process = $this->mockObject('CM_Process');

        $bindMock = $process->mockMethod('bind');
        $bindMock->set(function ($event, callable $callback) {
            $this->assertSame('exit', $event);
            $this->assertSame('killChildren', $callback[1]);
        });
        /** @var CM_Process_ForkHandler|\Mocka\FunctionMock $forkHandlerMock */
        $forkHandlerMock = $process->mockMethod('_getForkHandler');
        $forkHandlerMock->set(function () {
            $mockForkHandler = $this->mockClass('CM_Process_ForkHandler')->newInstanceWithoutConstructor();
            $mockForkHandler->mockMethod('runWorkload');
            return $mockForkHandler;
        });

        $this->assertSame(0, $bindMock->getCallCount());
        $process->mockMethod('_hasForks')->set(true);
        $process->fork(function () {
            return 0;
        });
        $this->assertSame(0, $bindMock->getCallCount());
        $process->mockMethod('_hasForks')->set(false);
        $process->fork(function () {
            return 0;
        });
        $this->assertSame(1, $bindMock->getCallCount());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testForkAndWaitForChildren() {
        $file = CM_File::createTmp();

        // Previous fork, which should be ignored silently by CM_Process::_wait
        if (0 === pcntl_fork()) {
            usleep(200 * 1000);
            exit;
        }

        $process = CM_Process::getInstance();
        $parentOutput = [];
        for ($i = 1; $i <= 4; $i++) {
            $parentOutput[] = "Child $i forked.";
            $process->fork(function () use ($i, $file) {
                $ms = 100 * $i;
                usleep($ms * 1000);
                $file->appendLine("Child $i terminated after $ms ms.");
            });
        }
        $parentOutput[] = "Child $i forked.";
        $process->fork(function () use ($i, $file) {
            if (0 === pcntl_fork()) {
                usleep(200 * 1000);
                exit;
            }
            $ms = 100 * $i;
            usleep($ms * 1000);
            $file->appendLine("Child $i forked own sub-process and terminated after $ms ms.");
        });
        $parentOutput[] = 'Parent waiting for 250 ms...';
        usleep(250000);
        $parentOutput[] = 'Parent listening to children...';
        $workloadResultList = $process->waitForChildren();
        $this->assertSame([1 => true, 2 => true, 3 => true, 4 => true, 5 => true], \Functional\invoke($workloadResultList, 'isSuccess'));
        $parentOutput[] = 'Parent terminated.';
        $childrenOutput = explode(PHP_EOL, $file->read());

        $this->assertSame([
            'Child 1 forked.',
            'Child 2 forked.',
            'Child 3 forked.',
            'Child 4 forked.',
            'Child 5 forked.',
            'Parent waiting for 250 ms...',
            'Parent listening to children...',
            'Parent terminated.'
        ], $parentOutput);

        $this->assertContainsAll([
            'Child 2 terminated after 200 ms.',
            'Child 1 terminated after 100 ms.',
            'Child 3 terminated after 300 ms.',
            'Child 4 terminated after 400 ms.',
            'Child 5 forked own sub-process and terminated after 500 ms.',
        ], $childrenOutput);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testForkAndListenForChildren() {
        $process = CM_Process::getInstance();
        $process->fork(function () {
        });
        $process->fork(function () {
            usleep(1000000);
        });
        usleep(500000);
        $responses = $process->listenForChildren();
        $this->assertCount(1, $responses);
        $this->assertContainsOnlyInstancesOf('CM_Process_WorkloadResult', $responses);
        $this->assertSame([1 => true], \Functional\invoke($responses, 'isSuccess'));
        usleep(1000000);
        $responses = $process->listenForChildren();
        $this->assertCount(1, $responses);
        $this->assertContainsOnlyInstancesOf('CM_Process_WorkloadResult', $responses);
        $this->assertSame([2 => true], \Functional\invoke($responses, 'isSuccess'));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testForkAndWaitForChildrenWithResultsAndExiting() {
        $process = CM_Process::getInstance();
        $process->fork(function () {
            usleep(50 * 1000);
            return 'Child 1 finished';
        });
        $process->fork(function () {
            exit(0);
        });
        $process->fork(function () {
            posix_kill(posix_getpid(), SIGTERM);
        });
        $process->fork(function () {
            posix_kill(posix_getpid(), SIGKILL);
        });

        $workloadResultList = $process->waitForChildren();
        $this->assertCount(4, $workloadResultList);

        $this->assertSame(true, $workloadResultList[1]->isSuccess());
        $this->assertSame(0, $workloadResultList[1]->getReturnCode());

        $this->assertSame(true, $workloadResultList[2]->isSuccess());
        $this->assertSame(0, $workloadResultList[2]->getReturnCode());

        $this->assertSame(false, $workloadResultList[3]->isSuccess());
        $this->assertSame(null, $workloadResultList[3]->getReturnCode());

        $this->assertSame(false, $workloadResultList[4]->isSuccess());
        $this->assertSame(null, $workloadResultList[4]->getReturnCode());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testKillChildren() {
        $loopEcho = function () {
            while (true) {
                usleep(50 * 1000);
                echo "hello\n";
            }
        };
        $process = CM_Process::getInstance();
        $process->fork($loopEcho);
        $process->fork($loopEcho);

        $pidListBefore = $this->_getChildrenPidList();
        $process->killChildren();
        $this->assertCount(2, $pidListBefore);
        $this->assertCount(0, $this->_getChildrenPidList());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testKillChildrenOnExit() {
        $bootloader = CM_Bootloader::getInstance();
        $exceptionHandlerBackup = $bootloader->getExceptionHandler();

        /** @var CM_ExceptionHandling_Handler_Abstract|\Mocka\ClassMock $exceptionHandler */
        $exceptionHandler = $this->mockClass('CM_ExceptionHandling_Handler_Abstract')->newInstanceWithoutConstructor();
        $exceptionHandler->mockMethod('handleException');

        $bootloader->setExceptionHandler($exceptionHandler);

        $loopEcho = function () {
            usleep(50000);
        };

        $process = $this->mockObject('CM_Process');
        $killChildrenMethod = $process->mockMethod('killChildren');
        /** @var CM_Process $process */
        $this->assertSame(0, $killChildrenMethod->getCallCount());

        $process->trigger('exit');
        $this->assertSame(0, $killChildrenMethod->getCallCount());

        // Bind killChildren
        $process->fork($loopEcho);
        $process->trigger('exit');
        $this->assertSame(1, $killChildrenMethod->getCallCount());

        // Unbind killChildren
        $process->waitForChildren();
        $process->trigger('exit');
        $this->assertSame(1, $killChildrenMethod->getCallCount());

        // Rebind killChildren
        $process->fork($loopEcho);
        $process->trigger('exit');
        $this->assertSame(2, $killChildrenMethod->getCallCount());

        $bootloader->setExceptionHandler($exceptionHandlerBackup);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testKillChildrenSigKill() {
        $loopEcho = function () {
            while (true) {
                usleep(50 * 1000);
                echo "hello\n";
            }
        };
        $loopEchoStayinAlive = function () {
            pcntl_signal(SIGTERM, function () {
                echo "Well, you can tell by the way I use my walk\n I'm a woman's man, no time to talk\n";
            }, false);
            while (true) {
                usleep(50 * 1000);
                echo "hello\n";
            }
        };

        $process = CM_Process::getInstance();
        $process->fork($loopEcho);
        $process->fork($loopEchoStayinAlive);
        $pidListBefore = $this->_getChildrenPidList();

        $timeStart = microtime(true);
        $process->killChildren(0.5);

        $this->assertCount(2, $pidListBefore);
        $this->assertCount(0, $this->_getChildrenPidList());
        $this->assertSameTime(0.65, microtime(true) - $timeStart, 0.15);

        $logError = new CM_Paging_Log([CM_Log_Logger::ERROR]);
        $this->assertSame(1, $logError->getCount());
        $this->assertContains('killing with signal `9`', $logError->getItem(0)['message']);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testServicesReset() {
        $mysql = CM_Service_Manager::getInstance()->getDatabases()->getMaster();
        $mysql->setBuffered(false);

        $process = CM_Process::getInstance();
        $process->fork(function () {
            $mysql = CM_Service_Manager::getInstance()->getDatabases()->getMaster();
            $mysql->setBuffered(false);
            for ($i = 0; $i < 1000; $i++) {
                $result2 = $mysql->createStatement('SELECT "bar"')->execute()->fetchColumn();
                $this->assertSame('bar', $result2, 'Separate processes should not share connections to services');
            }
        });

        for ($i = 0; $i < 1000; $i++) {
            $result1 = $mysql->createStatement('SELECT "foo"')->execute()->fetchColumn();
            $this->assertSame('foo', $result1, 'Separate processes should not share connection to services');
        }

        $process->waitForChildren();
    }

    public function testEventHandler() {
        $counter = 0;
        $process = CM_Process::getInstance();
        $process->bind('foo', function () use (&$counter) {
            $counter++;
        });
        $process->trigger('foo');
        $this->assertSame(1, $counter);

        $process->trigger('foo');
        $this->assertSame(2, $counter);

        $process->unbind('foo');
        $process->trigger('foo');
        $this->assertSame(2, $counter);
    }

    /**
     * @return int[]
     * @throws CM_Exception
     */
    private function _getChildrenPidList() {
        $psCommand = 'ps axo pid,ppid,args';
        $psOutput = CM_Util::exec($psCommand);
        if (false === preg_match_all('/^\s*(?<pid>\d+)\s+(?<ppid>\d+)\s+(?<args>.+)$/m', $psOutput, $matches, PREG_SET_ORDER)) {
            throw new CM_Exception('Cannot parse ps output `' . $psOutput . '`.');
        }
        $pid = CM_Process::getInstance()->getProcessId();
        $pidList = array();
        foreach ($matches as $match) {
            if ($match['ppid'] == $pid && false === strpos($match['args'], $psCommand)) {
                $pidList[] = (int) $match['pid'];
            }
        }
        return $pidList;
    }
}
