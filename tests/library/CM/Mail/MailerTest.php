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

    public function testSendFailed() {
        $transport = $this->mockInterface('Swift_Transport')->newInstance();
        $transport->mockMethod('isStarted')->set(true);
        $message = new Swift_Message('foo', 'content');
        $message->setTo('foo@example.com');
        $message->setCc('bar@example.com', 'bar');
        $client = new CM_Mail_Mailer($transport);

        $sendMethod = $transport->mockMethod('send')->set(0);
        $exception = $this->catchException(function () use ($client, $message) {
            $client->send($message);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        /** @var CM_Exception_Invalid $exception */
        $this->assertSame('Failed to send email', $exception->getMessage());
        $this->assertSame(['message', 'failedRecipients'], array_keys($exception->getMetaInfo()));
        $this->assertEquals($message, $exception->getMetaInfo()['message']);
    }
}
