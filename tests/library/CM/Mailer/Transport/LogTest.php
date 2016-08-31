<?php

class CM_Mailer_Transport_LogTest extends CMTest_TestCase {

    public function testConstruct() {
        $transport = new CM_Mailer_Transport_Log();
        $this->assertSame(CM_Log_Logger::INFO, $transport->getLogLevel());

        $transport = new CM_Mailer_Transport_Log(CM_Log_Logger::DEBUG);
        $this->assertSame(CM_Log_Logger::DEBUG, $transport->getLogLevel());
    }

    public function testStart() {
        $transport = new CM_Mailer_Transport_Log();
        $exception = $this->catchException(function () use ($transport) {
            $transport->start();
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        /** @var CM_Exception_Invalid $exception */
        $this->assertSame('Service manager not set', $exception->getMessage());
        $this->assertFalse($transport->isStarted());

        $serviceManager = new CM_Service_Manager();
        $transport->setServiceManager($serviceManager);
        $exception = $this->catchException(function () use ($transport) {
            $transport->start();
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        /** @var CM_Exception_Invalid $exception */
        $this->assertSame('Logger service not available', $exception->getMessage());
        $this->assertFalse($transport->isStarted());

        $logger = $this->mockObject('CM_Log_Logger');
        $serviceManager->registerInstance('logger', $logger);
        $transport->start();
        $this->assertTrue($transport->isStarted());
    }

    public function testStop() {
        $transport = new CM_Mailer_Transport_Log();
        $serviceManager = new CM_Service_Manager();
        $logger = $this->mockObject('CM_Log_Logger');
        $serviceManager->registerInstance('logger', $logger);
        $transport->setServiceManager($serviceManager);

        $transport->start();
        $this->assertTrue($transport->isStarted());
        $transport->stop();
        $this->assertFalse($transport->isStarted());
    }

    public function testSend() {
        $transport = new CM_Mailer_Transport_Log();
        $serviceManager = new CM_Service_Manager();
        $logger = $this->mockObject('CM_Log_Logger');
        $serviceManager->registerInstance('logger', $logger);
        $transport->setServiceManager($serviceManager);

        $message = new CM_Mailer_Message();
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
