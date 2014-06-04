<?php

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

    public function runBare() {
        require_once dirname(dirname(__DIR__)) . '/bootstrap.php'; // Bootstrap the test explicitly when running in a separate process
        parent::runBare();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testForkAndWaitForChildren() {
        $process = CM_Process::getInstance();
        for ($i = 1; $i <= 4; $i++) {
            CM_ProcessTest::writeln("Child $i forked.");
            $process->fork(function () use ($i) {
                $ms = 100 * $i;
                usleep($ms * 1000);
                CM_ProcessTest::writeln("Child $i terminated after $ms ms.");
                ob_clean(); // Remove any test output buffered by phpUnit, which uses STDOUT itself to return test results from isolated processes
            });
        }
        CM_ProcessTest::writeln('Parent waiting for 250 ms...');
        usleep(250000);
        CM_ProcessTest::writeln('Parent listening to children...');
        $process->waitForChildren(null, function () {
            CM_ProcessTest::writeln('All children terminated.');
        });
        CM_ProcessTest::writeln('Parent terminated.');

        $this->expectOutputString('Child 1 forked.
Child 2 forked.
Child 3 forked.
Child 4 forked.
Parent waiting for 250 ms...
Parent listening to children...
All children terminated.
Parent terminated.
');

        $outputFileExpected = 'Child 1 forked.
Child 2 forked.
Child 3 forked.
Child 4 forked.
Parent waiting for 250 ms...
Child 1 terminated after 100 ms.
Child 2 terminated after 200 ms.
Parent listening to children...
Child 3 terminated after 300 ms.
Child 4 terminated after 400 ms.
All children terminated.
Parent terminated.
';
        rewind(self::$_file);
        $outputFileActual = fread(self::$_file, 8192);
        $this->assertEquals($outputFileExpected, $outputFileActual);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testForkAndWaitForChildrenWithResults() {
        $process = CM_Process::getInstance();
        $process->fork(function () {
            usleep(100 * 1000);
            return 'Child 1 finished';
        });
        $process->fork(function () {
            usleep(50 * 1000);
            return array('msg' => 'Child 2 finished');
        });
        $process->fork(function () {
            usleep(150 * 1000);
            $this->setExpectedException('Exception');
            throw new Exception('Child 3 finished');
        });

        $workloadResultList = $process->waitForChildren();
        $this->assertCount(3, $workloadResultList);
        $this->assertSame('Child 1 finished', $workloadResultList[0]->getResult());
        $this->assertSame(null, $workloadResultList[0]->getException());
        $this->assertSame(array('msg' => 'Child 2 finished'), $workloadResultList[1]->getResult());
        $this->assertSame(null, $workloadResultList[1]->getException());
        $this->assertSame(null, $workloadResultList[2]->getResult());
        $this->assertSame('Child 3 finished', $workloadResultList[2]->getException()->getMessage());
    }

    /**
     * @param string $message
     */
    public static function writeln($message) {
        print "$message\n";
        fwrite(self::$_file, "$message\n");
    }
}
