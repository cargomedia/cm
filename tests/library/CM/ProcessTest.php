<?php

require_once dirname(dirname(__DIR__)) . '/bootstrap.php'; // Bootstrap the test explicitly when running in a separate process

class CM_ProcessTest extends CMTest_TestCase {

    /** @var resource */
    protected static $_file;

    public static function setupBeforeClass() {
        parent::setUpBeforeClass();
        self::$_file = tmpfile();
    }

    public static function tearDownAfterClass() {
        fclose(self::$_file);
        parent::tearDownAfterClass();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testFork() {
        $process = CM_Process::getInstance();
        $seq1 = $process->fork(function() {});
        $seq2 = $process->fork(function() {});
        $seq3 = $process->fork(function() {});
        $this->assertSame(1, $seq2 - $seq1);
        $this->assertSame(1, $seq3 - $seq2);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testForkAndWaitForChildren() {
        $file = CM_File::createTmp();

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
        $parentOutput[] = 'Parent waiting for 250 ms...';
        usleep(250000);
        $parentOutput[] = 'Parent listening to children...';
        $process->waitForChildren(null, function () use (&$parentOutput) {
            $parentOutput[] = 'All children terminated.';
        });
        $parentOutput[] = 'Parent terminated.';
        $childrenOutput = explode(PHP_EOL, $file->read());

        $this->assertSame([
            'Child 1 forked.',
            'Child 2 forked.',
            'Child 3 forked.',
            'Child 4 forked.',
            'Parent waiting for 250 ms...',
            'Parent listening to children...',
            'All children terminated.',
            'Parent terminated.'
        ], $parentOutput);

        $this->assertContainsAll([
            'Child 2 terminated after 200 ms.',
            'Child 1 terminated after 100 ms.',
            'Child 3 terminated after 300 ms.',
            'Child 4 terminated after 400 ms.',
        ], $childrenOutput);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testForkAndListenForChildren() {
        $process = CM_Process::getInstance();
        $process->fork(function () {
            return 'foo';
        });
        $process->fork(function () {
            usleep(500000);
            return 'bar';
        });
        usleep(250000);
        $responses = $process->listenForChildren();
        $this->assertCount(1, $responses);
        $this->assertContainsOnlyInstancesOf('CM_Process_WorkloadResult', $responses);
        $this->assertSame(['foo'], \Functional\invoke($responses, 'getResult'));
        usleep(500000);
        $responses = $process->listenForChildren();
        $this->assertCount(1, $responses);
        $this->assertContainsOnlyInstancesOf('CM_Process_WorkloadResult', $responses);
        $this->assertSame(['bar'], \Functional\invoke($responses, 'getResult'));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testForkAndWaitForChildrenWithResults() {
        $bootloader = CM_Bootloader::getInstance();
        $exceptionHandlerBackup = $bootloader->getExceptionHandler();

        /**
         * Increase print-severity to make sure "child 3"'s exception output doesn't disturb phpunit
         */
        $exceptionHandler = new CM_ExceptionHandling_Handler_Cli();
        $exceptionHandler->setPrintSeverityMin(CM_Exception::FATAL);
        $bootloader->setExceptionHandler($exceptionHandler);

        $process = CM_Process::getInstance();
        $process->fork(function () {
            usleep(100 * 1000);
            return 'Child 1 finished';
        });
        $process->fork(function () {
            usleep(50 * 1000);
            return array('msg' => 'Child 2 finished');
        });
        $process->fork(function (CM_Process_WorkloadResult $result) {
            usleep(150 * 1000);
            throw new CM_Exception('Child 3 finished');
        });
        $process->fork(function (CM_Process_WorkloadResult $result) {
            usleep(200 * 1000);
            $result->setException(new CM_Exception('Child 4 finished'));
        });

        $workloadResultList = $process->waitForChildren();
        $this->assertCount(4, $workloadResultList);

        $this->assertSame('Child 1 finished', $workloadResultList[1]->getResult());
        $this->assertSame(null, $workloadResultList[1]->getException());
        $this->assertTrue($workloadResultList[1]->isSuccess());

        $this->assertSame(array('msg' => 'Child 2 finished'), $workloadResultList[2]->getResult());
        $this->assertSame(null, $workloadResultList[2]->getException());
        $this->assertTrue($workloadResultList[2]->isSuccess());

        $this->assertSame(null, $workloadResultList[3]->getResult());
        $this->assertSame('Child 3 finished', $workloadResultList[3]->getException()->getMessage());
        $this->assertFalse($workloadResultList[3]->isSuccess());
        $errorLog = new CM_Paging_Log_Error();
        $this->assertSame(1, $errorLog->getCount());

        $this->assertContains('Child 3 finished', $errorLog->getItem(0)['msg']);
        $this->assertSame(null, $workloadResultList[4]->getResult());
        $this->assertSame('Child 4 finished', $workloadResultList[4]->getException()->getMessage());
        $this->assertFalse($workloadResultList[4]->isSuccess());
        $this->assertSame(1, $errorLog->getCount());

        $bootloader->setExceptionHandler($exceptionHandlerBackup);
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

        $this->assertSame('Child 1 finished', $workloadResultList[1]->getResult());
        $this->assertSame(null, $workloadResultList[1]->getException());

        $this->assertSame(null, $workloadResultList[2]->getResult());
        $this->assertSame('Received no data from IPC stream.', $workloadResultList[2]->getException()->getMessage());

        $this->assertSame(null, $workloadResultList[3]->getResult());
        $this->assertSame('Received no data from IPC stream.', $workloadResultList[3]->getException()->getMessage());

        $this->assertSame(null, $workloadResultList[4]->getResult());
        $this->assertSame('Received no data from IPC stream.', $workloadResultList[4]->getException()->getMessage());
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
        $this->assertSameTime(0.5, microtime(true) - $timeStart, 0.15);

        $logError = new CM_Paging_Log_Error();
        $this->assertSame(1, $logError->getCount());
        $this->assertContains('killing with signal `9`', $logError->getItem(0)['msg']);
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

    /**
     * @param string $message
     */
    public static function writeln($message) {
        fwrite(self::$_file, "$message\n");
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
