<?php

class CM_Mail_MessageTest extends CMTest_TestCase {

    public function testSetBodyWithAlternativel() {
        $message = new CM_Mail_Message();
        $message->setBodyWithAlternative('content');
        $this->assertSame('text/plain', $message->getContentType());
        $this->assertSame('content', $message->getBody());
        $this->assertSame('content', $message->getText());
        $this->assertNull($message->getHtml());

        $message = new CM_Mail_Message();
        $message->setBodyWithAlternative('content', '<p>content</p>');
        $this->assertSame('multipart/alternative', $message->getContentType());
        $this->assertSame('<p>content</p>', $message->getBody());
        $this->assertSame('text/plain', $message->getChildren()[0]->getContentType());
        $this->assertSame('content', $message->getChildren()[0]->getBody());
        $this->assertSame('content', $message->getText());
        $this->assertSame('<p>content</p>', $message->getHtml());
    }

    public function testGetCustomHeaders() {
        $message = new CM_Mail_Message();
        $this->assertSame([], $message->getCustomHeaders());

        $message->getHeaders()->addTextHeader('foo', 'bar');
        $this->assertSame([], $message->getCustomHeaders());

        $message->getHeaders()->addTextHeader('X-foo', 'bar');
        $this->assertSame(['X-foo' => ['bar']], $message->getCustomHeaders());

        $message->getHeaders()->addTextHeader('X-bar', 'foo');
        $this->assertSame(['X-foo' => ['bar'], 'X-bar' => ['foo']], $message->getCustomHeaders());

        $message->getHeaders()->addTextHeader('X-bar', 'foobar');
        $this->assertSame(['X-foo' => ['bar'], 'X-bar' => ['foo', 'foobar']], $message->getCustomHeaders());
    }

    public function testToArray() {
        $message1 = new CM_Mail_Message();
        $this->assertSame([
            'subject'       => null,
            'html'          => null,
            'text'          => null,
            'sender'        => null,
            'replyTo'       => null,
            'to'            => null,
            'cc'            => null,
            'bcc'           => null,
            'customHeaders' => [],
        ], $message1->toArray());

        $message2 = new CM_Mail_Message();
        $message2
            ->setSubject('foo')
            ->setSender('foo@example.com')
            ->setReplyTo('bar@example.com')
            ->addTo('bar@example.com', 'Bar')
            ->addCc('bar1@example.com')
            ->addCc('bar2@example.com', 'Bar2');
        $message2->setBody('<p>content</p>', 'text/html');
        $message2->addPart('content', 'text/plain');
        $message2->getHeaders()->addTextHeader('foo', 'bar');
        $message2->getHeaders()->addTextHeader('X-foo', 'bar');
        $message2->getHeaders()->addTextHeader('X-bar', 'foo');
        $message2->getHeaders()->addTextHeader('X-bar', 'foobar');

        $array = $message2->toArray();
        $this->assertSame([
            'subject'       => 'foo',
            'html'          => '<p>content</p>',
            'text'          => 'content',
            'sender'        => ['foo@example.com' => null],
            'replyTo'       => ['bar@example.com' => null],
            'to'            => ['bar@example.com' => 'Bar'],
            'cc'            => [
                'bar1@example.com' => null,
                'bar2@example.com' => 'Bar2',
            ],
            'bcc'           => null,
            'customHeaders' => [
                'X-foo' => ['bar'],
                'X-bar' => ['foo', 'foobar'],
            ],
        ], $array);
    }

    public function testFromArray() {
        $message = CM_Mail_Message::fromArray([
            'subject'       => null,
            'html'          => null,
            'text'          => null,
            'sender'        => null,
            'replyTo'       => null,
            'to'            => null,
            'cc'            => null,
            'bcc'           => null,
            'customHeaders' => [],
        ]);
        $this->assertInstanceOf('CM_Mail_Message', $message);
        $this->assertSame((new CM_Mail_Message())->toArray(), $message->toArray());

        $message = CM_Mail_Message::fromArray([
            'subject'       => 'foo',
            'html'          => '<p>content</p>',
            'text'          => 'content',
            'sender'        => ['foo@example.com' => null],
            'replyTo'       => ['bar@example.com' => null],
            'to'            => ['bar@example.com' => 'Bar'],
            'cc'            => [
                'bar1@example.com' => null,
                'bar2@example.com' => 'Bar2',
            ],
            'bcc'           => null,
            'customHeaders' => [
                'X-foo' => ['bar'],
                'X-bar' => ['foo', 'foobar'],
            ],
        ]);
        $expectedMessage = new CM_Mail_Message();
        $expectedMessage
            ->setSubject('foo')
            ->setSender('foo@example.com')
            ->setReplyTo('bar@example.com')
            ->addTo('bar@example.com', 'Bar')
            ->addCc('bar1@example.com')
            ->addCc('bar2@example.com', 'Bar2');
        $expectedMessage->setBody('<p>content</p>', 'text/html');
        $expectedMessage->addPart('content', 'text/plain');
        $expectedMessage->getHeaders()->addTextHeader('foo', 'bar');
        $expectedMessage->getHeaders()->addTextHeader('X-foo', 'bar');
        $expectedMessage->getHeaders()->addTextHeader('X-bar', 'foo');
        $expectedMessage->getHeaders()->addTextHeader('X-bar', 'foobar');

        $this->assertInstanceOf('CM_Mail_Message', $message);
        $this->assertSame($expectedMessage->toArray(), $message->toArray());
    }
}
