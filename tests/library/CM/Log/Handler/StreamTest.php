<?php

class CM_Log_Handler_StreamTest extends CMTest_TestCase {

    public function testFormatting() {
        $record = new CM_Log_Record(CM_Log_Logger::INFO, 'foo', new CM_Log_Context());
        $handler = new CM_Log_Handler_Stream('foo', 'php://temp');
        $formattedMessage = $this->forceInvokeMethod($handler, '_formatRecord', [$record]);
        $this->assertRegExp('/^\[[0-9]{2}-[0-9]{2}-[0-9]{4} [0-9]{2}:[0-9]{2}:[0-9]{2} - INFO\] foo$/', $formattedMessage);

        $handler = new CM_Log_Handler_Stream('foo', 'php://temp', CM_Log_Logger::INFO, '{message} | {datetime} | {levelname}', 'H:i:s');
        $formattedMessage = $this->forceInvokeMethod($handler, '_formatRecord', [$record]);
        $this->assertRegExp('/^foo \| [0-9]{2}:[0-9]{2}:[0-9]{2} \| INFO$/', $formattedMessage);
    }

    public function testWriteRecord() {
        $tmpFilename = tempnam(sys_get_temp_dir(), __METHOD__ . '_');
        $tmpFile = 'file://' + $tmpFilename;

        $record = new CM_Log_Record(CM_Log_Logger::INFO, 'foo', new CM_Log_Context());
        $handler = new CM_Log_Handler_Stream('foo', $tmpFile);
        $this->forceInvokeMethod($handler, '_writeRecord', [$record]);

        $this->assertRegExp('/^\[[0-9 \:\-]+INFO\] foo\n$/', file_get_contents($tmpFile));
        unlink($tmpFilename);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testWriteRecordWithLockfile() {
        $tmpFilename = tempnam(sys_get_temp_dir(), __METHOD__ . '_');
        $tmpFile = 'file://' + $tmpFilename;
        $stream = fopen($tmpFile, 'w');

        flock($stream, LOCK_EX);

        $process = CM_Process::getInstance();
        $process->fork(function () use ($tmpFile) {
            $record = new CM_Log_Record(CM_Log_Logger::INFO, 'foo', new CM_Log_Context());
            $handler = new CM_Log_Handler_Stream('foo', $tmpFile, null, null, null, true);
            $this->forceInvokeMethod($handler, '_writeRecord', [$record]);
        });

        usleep(50 * 1000);
        $results = $process->listenForChildren();
        $this->assertEmpty($results);

        flock($stream, LOCK_UN);

        usleep(50 * 1000);
        $results = $process->listenForChildren();
        if (empty($results)) {
            $process->killChildren();
            $this->fail('Failed to write in a locked file after the unlock.');
        }

        unlink($tmpFilename);
    }
}
