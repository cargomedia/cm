<?php

class CM_Paging_Log_MailTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testTyping() {
        $logger = CM_Service_Manager::getInstance()->getLogger();

        $this->assertSame(0, (new CM_Paging_Log_Mail(CM_Log_Logger::INFO))->getCount());
        $this->assertSame(0, (new CM_Paging_Log(CM_Log_Logger::INFO))->getCount());

        $logger->info('mail foo', new CM_Log_Context(null, null, null, [
            'type' => CM_Paging_Log_Mail::getTypeStatic(),
            'foo'  => 'foo',
        ]));
        $logger->info('mail bar', new CM_Log_Context(null, null, null, [
            'type' => CM_Paging_Log_Mail::getTypeStatic(),
            'bar'  => 'bar',
        ]));
        $logger->info('not mail', new CM_Log_Context(null, null, null, [
            'baz' => 'baz',
        ]));
        $this->assertSame(2, (new CM_Paging_Log_Mail(CM_Log_Logger::INFO))->getCount());
        $this->assertSame(1, (new CM_Paging_Log(CM_Log_Logger::INFO))->getCount());
    }
}
