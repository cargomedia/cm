<?php

class CM_MailTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testWithTemplate() {
        $user = $this->getMockUser();
        $templateVariables = array('foo' => 'bar');
        $msg = new CM_Mail_Welcome($user, $templateVariables);
        list($subject, $html, $text) = $msg->render();
        $this->assertNotEmpty($subject);
        $this->assertNotEmpty($html);
        $this->assertNotEmpty($text);
    }

    public function testNoTemplate() {
        $msg = new CM_Mail('foo@example.com');
        try {
            $msg->send();
            $this->fail('Should have thrown an exception');
        } catch (CM_Exception_Invalid $ex) {
            $this->assertTrue(true);
        }
        $msg->setSubject('blabla');
        try {
            $msg->send();
            $this->fail('Should have thrown an exception');
        } catch (CM_Exception_Invalid $ex) {
            $this->assertTrue(true);
        }
        $msg->setBody('<a href="http://www.foo.bar">Hello</a>');
        list($subject, $html, $text) = $msg->render();
        $this->assertEquals('blabla', $subject);
        $this->assertEquals('<a href="http://www.foo.bar">Hello</a>', $html);
        $this->assertEquals('Hello (http://www.foo.bar)', $text);
    }

    public function testQueue() {
        $user = $this->getMock('CM_Model_User', array('getEmail', 'getSite'), array(CMTest_TH::createUser()->getId()));
        $user->expects($this->any())->method('getEmail')->will($this->returnValue('foo@example.com'));
        $user->expects($this->any())->method('getSite')->will($this->returnValue(CM_Site_Abstract::factory()));

        $msg = new CM_Mail($user, null);
        $msg->setSubject('testSubject');
        $msg->setBody('<b>hallo</b>');
        $msg->addReplyTo('foo@bar.com');
        $msg->addCc('foo@bar.org', 'foobar');
        $msg->addBcc('foo@bar.net');
        $msg->sendDelayed();
        $this->assertRow('cm_mail', array(
            'subject' => 'testSubject',
            'text'    => 'hallo',
            'html'    => '<b>hallo</b>',
            'to'      => serialize($msg->getTo()),
            'replyTo' => serialize($msg->getReplyTo()),
            'cc'      => serialize($msg->getCc()),
            'bcc'     => serialize($msg->getBcc()),
        ));
        $this->assertEquals(1, CM_Db_Db::count('cm_mail', 'id'));
        CM_Mail::processQueue(1);
        $this->assertEquals(0, CM_Db_Db::count('cm_mail', 'id'));
    }

    public function testAddPart() {
        $mail = new CM_Mail();
        $text = 'foo bar foo bar';
        $mail->addPart($text);
        $this->assertSame($text, $mail->getText());
    }

    public function testSend() {
        CM_Config::get()->CM_Mail->send = true;

        $transport = $this->mockInterface('Swift_Transport')->newInstance();
        $sendMethod = $transport->mockMethod('send')->set(1);
        $mailer = new CM_Mailer_Client($transport);

        $mail = new CM_Mail('foo@example.com', null, null, $mailer);
        $mail->setSender('sender@example.com', 'Sender');
        $mail->setSubject('testSubject');
        $mail->setBody('<b>hallo</b>');
        $mail->addReplyTo('foo@bar.com');
        $mail->addCc('foo@bar.org', 'foobar');
        $mail->addBcc('foo@bar.net');
        $mail->addCustomHeader('X-Foo', 'bar');
        $mail->addCustomHeader('X-Bar', 'foo');
        $mail->addCustomHeader('X-Foo', 'foo');

        $message = $mail->getMessage();
        $this->assertSame(['sender@example.com' => 'Sender'], $message->getSender());
        $this->assertSame('testSubject', $message->getSubject());
        $this->assertSame('<b>hallo</b>', $message->getBody());
        $this->assertSame(['foo@bar.com' => null], $message->getReplyTo());
        $this->assertSame(['foo@bar.org' => 'foobar'], $message->getCc());
        $this->assertSame(['foo@bar.net' => null], $message->getBcc());
        $this->assertSame('foo', $message->getHeaders()->get('X-Bar')->getFieldBody());
        $this->assertSame('bar', $message->getHeaders()->get('X-Foo', 0)->getFieldBody());
        $this->assertSame('foo', $message->getHeaders()->get('X-Foo', 1)->getFieldBody());

        $mail->send();
        $this->assertSame(1, $sendMethod->getCallCount());
    }

    public function testProcessQueue() {
        $mail = new CM_Mail();
        $mail->addTo('foo@example.com');
        $mail->setSubject('foo');
        $mail->addPart('bar');
        $mail->addCustomHeader('X-Foo', 'bar');
        $mail->send(true);
        $mail->send(true);
        $mail->send(true);
        $this->assertSame(3, CM_Mail::getQueueSize());
        $mail->processQueue(1);
        $this->assertSame(2, CM_Mail::getQueueSize());
        $mail->processQueue(100);
        $this->assertSame(0, CM_Mail::getQueueSize());
    }

    public function testGetRender() {
        $site = $this->getMockSite();
        $mail = new CM_Mail(null, null, $site);
        $this->assertEquals($site, $mail->getRender()->getSite());
    }

    public function testGetRenderRecipient() {
        $site = $this->getMockSite();
        $recipient = $this->getMockUser('foo@example.com', $site);
        $mail = new CM_Mail($recipient);
        $this->assertEquals($site, $mail->getRender()->getSite());
    }

    public function testGetRenderDefault() {
        $mail = new CM_Mail();
        $this->assertEquals(CM_Site_Abstract::factory(), $mail->getRender()->getSite());
    }

    public function testGetSite() {
        $site = $this->getMockSite();
        $mail = new CM_Mail(null, null, $site);
        $this->assertEquals($site, $mail->getSite());
    }

    public function testGetSiteDefault() {
        $mail = new CM_Mail();
        $this->assertEquals(CM_Site_Abstract::factory(), $mail->getSite());
    }

    public function testGetSiteRecipient() {
        $site = $this->getMockSite();
        $recipient = $this->getMockUser('foo@example.com', $site);
        $mail = new CM_Mail($recipient);
        $this->assertEquals($site, $mail->getSite());
    }

    public function testCustomHeadersQueue() {
        $mail = new CM_Mail();
        $subject = uniqid();
        $mail->setSubject($subject);
        $mail->addCustomHeader('X-Foo', 'bar');
        $mail->addCustomHeader('X-Bar', 'foo');
        $mail->addCustomHeader('X-Foo', 'baz');
        $mail->addTo('foo@example.com');
        $mail->addPart('bla');
        $mail->send(true);
        $result = CM_Db_Db::select('cm_mail', 'customHeaders', array('subject' => $subject));
        $row = $result->fetch();
        $this->assertEquals(unserialize($row['customHeaders']), array('X-Foo' => ['bar', 'baz'], 'X-Bar' => ['foo']));
    }
}
