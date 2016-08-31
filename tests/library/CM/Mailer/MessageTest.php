<?php

class CM_Mailer_MessageTest extends CMTest_TestCase {

    public function testSetBodyWithAlternativel() {
        $message = new CM_Mailer_Message();
        $message->setBodyWithAlternative('content');
        $this->assertSame('text/plain', $message->getContentType());
        $this->assertSame('content', $message->getBody());
        $this->assertSame('content', $message->getText());
        $this->assertNull($message->getHtml());

        $message = new CM_Mailer_Message();
        $message->setBodyWithAlternative('content', '<p>content</p>');
        $this->assertSame('multipart/alternative', $message->getContentType());
        $this->assertSame('<p>content</p>', $message->getBody());
        $this->assertSame('text/plain', $message->getChildren()[0]->getContentType());
        $this->assertSame('content', $message->getChildren()[0]->getBody());
        $this->assertSame('content', $message->getText());
        $this->assertSame('<p>content</p>', $message->getHtml());
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
