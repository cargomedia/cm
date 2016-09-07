<?php

class CM_Mail_Transport_LogTest extends CMTest_TestCase {

    public function testConstruct() {
        $logger = $this->mockObject('CM_Log_Logger');
        $transport = new CM_Mail_Transport_Log($logger);
        $this->assertSame(CM_Log_Logger::INFO, $transport->getLogLevel());

        $transport = new CM_Mail_Transport_Log($logger, CM_Log_Logger::DEBUG);
        $this->assertSame(CM_Log_Logger::DEBUG, $transport->getLogLevel());
    }

    public function testStart() {
        $logger = $this->mockObject('CM_Log_Logger');
        $transport = new CM_Mail_Transport_Log($logger);
        $this->assertFalse($transport->isStarted());
        $transport->start();
        $this->assertTrue($transport->isStarted());
    }

    public function testStop() {
        $logger = $this->mockObject('CM_Log_Logger');
        $transport = new CM_Mail_Transport_Log($logger);
        $transport->start();
        $this->assertTrue($transport->isStarted());
        $transport->stop();
        $this->assertFalse($transport->isStarted());
    }

    public function testSend() {
        $logger = $this->mockObject('CM_Log_Logger');
        $transport = new CM_Mail_Transport_Log($logger);

        $message = new CM_Mail_Message();
        $message
            ->setSubject('foo')
            ->setSender('foo@example.com')
            ->setReplyTo('bar@example.com')
            ->addTo('bar@example.com', 'Bar')
            ->addCc('bar1@example.com')
            ->addCc('bar2@example.com', 'Bar2');

        $message->setBody('<p>content</p>', 'text/html');
        $message->addPart('content', 'text/plain');

        $failedRecipients = [];
        $logger->mockMethod('addMessage')->set(function ($message, $level, CM_Log_Context $context = null) {
            $this->assertSame('* foo *' . PHP_EOL . PHP_EOL . 'content' . PHP_EOL, $message);
            $this->assertSame(CM_Log_Logger::INFO, $level);
            $this->assertSame([
                'type'    => CM_Paging_Log_Mail::getTypeStatic(),
                'sender'  => ['foo@example.com' => null],
                'replyTo' => ['bar@example.com' => null],
                'to'      => ['bar@example.com' => 'Bar'],
                'cc'      => ['bar1@example.com' => null, 'bar2@example.com' => 'Bar2'],
                'bcc'     => null,
            ], $context->getExtra());
        });
        $numSent = $transport->send($message, $failedRecipients);
        $this->assertSame(3, $numSent);
        $this->assertSame([], $failedRecipients);
    }
}
