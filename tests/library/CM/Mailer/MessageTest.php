<?php

class CM_Mailer_MessageTest extends CMTest_TestCase {

    public function testGetHtml() {
        $message1 = new CM_Mailer_Message('foo', '<p>content</p>');
        $message2 = new CM_Mailer_Message('foo', '<p>content</p>', 'text/html');
        $message3 = new CM_Mailer_Message('foo', '<p>content</p>', 'text/plain');
        $message4 = new CM_Mailer_Message('foo');
        $message4->setBody('<p>content</p>');
        $message5 = new CM_Mailer_Message('foo');
        $message5->setBody('<p>content</p>', 'text/html');
        $message6 = new CM_Mailer_Message('foo');
        $message6->setBody('<p>content</p>', 'text/plain');
        $message7 = new CM_Mailer_Message();

        $this->assertNull($message1->getHtml());
        $this->assertSame('<p>content</p>', $message2->getHtml());
        $this->assertNull($message3->getHtml());
        $this->assertNull($message4->getHtml());
        $this->assertSame('<p>content</p>', $message5->getHtml());
        $this->assertNull($message6->getHtml());
        $this->assertNull($message7->getHtml());
    }

    public function testGetText() {
        $message1 = new CM_Mailer_Message('foo', 'content');
        $message2 = new CM_Mailer_Message('foo', 'content', 'text/html');
        $message3 = new CM_Mailer_Message('foo', 'content', 'text/plain');
        $message4 = new CM_Mailer_Message('foo');
        $message4->setBody('content');
        $message5 = new CM_Mailer_Message('foo');
        $message5->setBody('content', 'text/html');
        $message6 = new CM_Mailer_Message('foo');
        $message6->setBody('content', 'text/plain');
        $message7 = new CM_Mailer_Message();

        $this->assertSame('content', $message1->getText());
        $this->assertNull($message2->getText());
        $this->assertSame('content', $message3->getText());
        $this->assertSame('content', $message4->getText());
        $this->assertNull($message5->getText());
        $this->assertSame('content', $message6->getText());
        $this->assertNull($message7->getText());

        $message8 = new CM_Mailer_Message('foo');
        $message8->setBody('content', 'text/html');
        $this->assertNull($message8->getText());
        $message8->addPart('part');
        $this->assertSame('part', $message8->getText());

        $message9 = new CM_Mailer_Message('foo');
        $message9->setBody('content', 'text/html');
        $this->assertNull($message9->getText());
        $message9->addPart('part1', 'text/html');
        $this->assertNull($message9->getText());
        $message9->addPart('part2', 'text/plain');
        $this->assertSame('part2', $message9->getText());
        $message9->addPart('part3', 'text/plain');
        $this->assertSame('part2', $message9->getText());
    }

    public function testGetCustomHeaders() {
        $message = new CM_Mailer_Message();
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
}
