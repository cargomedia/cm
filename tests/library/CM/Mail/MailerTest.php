<?php

class CM_Mail_MailerTest extends CMTest_TestCase {

    public function testSend() {
        $transport = $this->mockInterface('Swift_Transport')->newInstance();
        $transport->mockMethod('isStarted')->set(true);
        $message = new Swift_Message('foo', 'content');
        $message->setTo('foo@example.com');
        $message->setCc('bar@example.com', 'bar');
        $client = new CM_Mail_Mailer($transport);

        $sendMethod = $transport->mockMethod('send')->set(2);

        $failedRecipients = [];
        $numSent = $client->send($message, $failedRecipients);
        $this->assertSame(1, $sendMethod->getCallCount());
        $this->assertSame(2, $numSent);
        $this->assertSame([], $failedRecipients);
    }

    public function testSendNoRecipient() {
        $transport = $this->mockInterface('Swift_Transport')->newInstance();
        $transport->mockMethod('isStarted')->set(true);
        $message = new Swift_Message();
        $client = new CM_Mail_Mailer($transport);

        $sendMethod = $transport->mockMethod('send');

        $exception = $this->catchException(function () use ($client, $message) {
            $client->send($message);
        });

        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        /** @var CM_Exception_Invalid $exception */
        $this->assertSame('No recipient specified', $exception->getMessage());
        $this->assertSame(0, $sendMethod->getCallCount());
    }

    public function testSendThrows() {
        $serviceManager = new CM_Service_Manager();
        $logger = $this->mockObject('CM_Log_Logger');
        $serviceManager->registerInstance('logger', $logger);
        $transport = $this->mockInterface('Swift_Transport')->newInstance();
        $transport->mockMethod('isStarted')->set(true);
        $message = new Swift_Message('foo', 'content');
        $message->setFrom('foobar@example.com', 'Foobar');
        $message->setTo('foo@example.com');
        $message->setCc('bar@example.com', 'bar');

        $client = new CM_Mail_Mailer($transport);
        $client->setServiceManager($serviceManager);

        $sendMethod = $transport->mockMethod('send')->set(function () {
            throw new Exception('Failed');
        });
        $errorMethod = $logger->mockMethod('error')->set(function ($message, CM_Log_Context $context = null) {
            $exception = $context->getException();
            $this->assertSame('Failed to send email', $message);
            $this->assertSame([
                'message'          => [
                    'subject' => 'foo',
                    'from'    => ['foobar@example.com' => 'Foobar'],
                    'to'      => ['foo@example.com' => null],
                    'cc'      => ['bar@example.com' => 'bar'],
                    'bcc'     => null,
                ],
                'failedRecipients' => [],
            ], $context->getExtra());
            $this->assertInstanceOf('Exception', $exception);
            $this->assertSame('Failed', $exception->getMessage());
        });

        $client->send($message);
        /** @var CM_Exception_Invalid $exception */
        $this->assertSame(1, $sendMethod->getCallCount());
        $this->assertSame(1, $errorMethod->getCallCount());
    }

    public function testSendFailed() {
        $serviceManager = new CM_Service_Manager();
        $logger = $this->mockObject('CM_Log_Logger');
        $serviceManager->registerInstance('logger', $logger);
        $transport = $this->mockInterface('Swift_Transport')->newInstance();
        $transport->mockMethod('isStarted')->set(true);
        $message = new Swift_Message('foo', 'content');
        $message->setFrom('foobar@example.com', 'Foobar');
        $message->setTo('foo@example.com');
        $message->setCc('bar@example.com', 'bar');

        $client = new CM_Mail_Mailer($transport);
        $client->setServiceManager($serviceManager);

        $sendMethod = $transport->mockMethod('send')->set(0);
        $errorMethod = $logger->mockMethod('error')->set(function ($message, CM_Log_Context $context = null) {
            $this->assertSame('Failed to send email', $message);
            $this->assertSame([
                'message'          => [
                    'subject' => 'foo',
                    'from'    => ['foobar@example.com' => 'Foobar'],
                    'to'      => ['foo@example.com' => null],
                    'cc'      => ['bar@example.com' => 'bar'],
                    'bcc'     => null,
                ],
                'failedRecipients' => ['foo@example.com', 'bar@example.com'],
            ], $context->getExtra());
        });

        $failedRecipients = ['foo@example.com', 'bar@example.com'];
        $client->send($message, $failedRecipients);

        /** @var CM_Exception_Invalid $exception */
        $this->assertSame(1, $sendMethod->getCallCount());
        $this->assertSame(1, $errorMethod->getCallCount());
    }
}
