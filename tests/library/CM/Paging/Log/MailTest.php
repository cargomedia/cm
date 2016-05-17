<?php

class CM_Paging_Log_MailTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testAddDelete() {
        $logger = CM_Service_Manager::getInstance()->getLogger();

        $this->assertSame(0, (new CM_Paging_Log_Mail([CM_Log_Logger::INFO]))->getCount());
        $this->assertSame(0, (new CM_Paging_Log([CM_Log_Logger::INFO]))->getCount());

        $context1 = new CM_Log_Context();
        $context1->setExtra([
            'type' => CM_Paging_Log_Mail::getTypeStatic(),
            'foo'  => 'foo',
        ]);
        $logger->info('mail foo', $context1);

        $context2 = new CM_Log_Context();
        $context2->setExtra([
            'type' => CM_Paging_Log_Mail::getTypeStatic(),
            'bar'  => 'bar',
        ]);
        $logger->info('mail bar', $context2);

        $context3 = new CM_Log_Context();
        $context3->setExtra([
            'baz' => 'baz',
        ]);
        $logger->info('not mail', $context3);
        $this->assertSame(2, (new CM_Paging_Log_Mail([CM_Log_Logger::INFO]))->getCount());
        $this->assertSame(3, (new CM_Paging_Log([CM_Log_Logger::INFO]))->getCount());

        $age = 7 * 86400 + 1;
        CMTest_TH::timeForward($age);

        (new CM_Paging_Log_Mail([CM_Log_Logger::INFO]))->cleanUp();
        $this->assertSame(0, (new CM_Paging_Log_Mail([CM_Log_Logger::INFO]))->getCount());
        $this->assertSame(1, (new CM_Paging_Log([CM_Log_Logger::INFO]))->getCount());
    }
}
