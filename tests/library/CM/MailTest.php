<?php

class CM_MailTest extends CMTest_TestCase {

    /** @var PHPMailer|\Mocka\AbstractClassTrait $_mockPHPMailer*/
    private $_mockPHPMailer;

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testWithTemplate() {
        $user = $this->getMock('CM_Model_User', array('getEmail', 'getSite'), array(CMTest_TH::createUser()->getId()));
        $user->expects($this->any())->method('getEmail')->will($this->returnValue('foo@example.com'));
        $user->expects($this->any())->method('getSite')->will($this->returnValue(CM_Site_Abstract::factory()));

        $templateVariabels = array('foo' => 'bar');
        $msg = new CM_Mail_Welcome($user, $templateVariabels);
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
        $msg->setHtml('<a href="http://www.foo.bar">Hello</a>');
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
        $msg->setHtml('<b>hallo</b>');
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

    public function testSetText() {
        $mail = new CM_Mail();
        $text = 'foo bar foo bar';
        $mail->setText($text);
        $this->assertSame($text, $mail->getText());
    }

    public function getMockPhpMailer() {
        return $this->_mockPHPMailer;
    }

    public function testSend() {
        CM_Config::get()->CM_Mail->send = true;
        $this->_mockPHPMailer = $this->mockObject('PHPMailer');

        $phpMailer = $this->getMockPhpMailer();

        $setFromMethod = $phpMailer->mockMethod('SetFrom')->set(function ($address, $name) {
            $this->assertSame('sender@example.com', $address);
            $this->assertSame('Sender', $name);
        });
        $addReplyToMethod = $phpMailer->mockMethod('AddReplyTo')->set(function ($address) {
            $this->assertSame('foo@bar.com', $address);
        });
        $addAddress = $phpMailer->mockMethod('AddAddress')->set(function ($address) {
            $this->assertSame('foo@example.com', $address);
        });
        $addCC = $phpMailer->mockMethod('AddCC')->set(function ($address, $name) {
            $this->assertSame('foo@bar.org', $address);
            $this->assertSame('foobar', $name);
        });
        $addBCC = $phpMailer->mockMethod('AddBCC')->set(function ($address) {
            $this->assertSame('foo@bar.net', $address);
        });
        $addCustomHeader = $phpMailer->mockMethod('AddCustomHeader')
            ->at(0, function ($name, $value) {
                $this->assertSame('X-Foo', $name);
                $this->assertSame('bar,foo', $value);
            })
            ->at(1, function ($name, $value) {
                $this->assertSame('X-Bar', $name);
                $this->assertSame('foo', $value);
            });

        $sendMethod = $phpMailer->mockMethod('Send');

        $mail = new Test_CM_Mail('foo@example.com', null, null, $this);
        $mail->setSender('sender@example.com', 'Sender');
        $mail->setSubject('testSubject');
        $mail->setHtml('<b>hallo</b>');
        $mail->addReplyTo('foo@bar.com');
        $mail->addCc('foo@bar.org', 'foobar');
        $mail->addBcc('foo@bar.net');
        $mail->setCustomHeader('X-Foo', 'bar');
        $mail->setCustomHeader('X-Bar', 'foo');
        $mail->setCustomHeader('X-Foo', 'foo');
        $mail->send();

        $this->assertSame(1, $setFromMethod->getCallCount());
        $this->assertSame(1, $addReplyToMethod->getCallCount());
        $this->assertSame(1, $addAddress->getCallCount());
        $this->assertSame(1, $addCC->getCallCount());
        $this->assertSame(1, $addBCC->getCallCount());
        $this->assertSame(2, $addCustomHeader->getCallCount());
        $this->assertSame(1, $sendMethod->getCallCount());
    }

    public function testProcessQueue() {
        $mail = new CM_Mail();
        $mail->addTo('foo@example.com');
        $mail->setSubject('foo');
        $mail->setText('bar');
        $mail->send(true);
        $mail->send(true);
        $mail->send(true);
        $this->assertSame(3, CM_Mail::getQueueSize());
        $mail->processQueue(1);
        $this->assertSame(2, CM_Mail::getQueueSize());
        $mail->processQueue(100);
        $this->assertSame(0, CM_Mail::getQueueSize());
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
        $recipient = CMTest_TH::createUser();
        $recipient->setSite($site);
        $mail = new CM_Mail($recipient);
        $this->assertEquals($site, $mail->getSite());
    }

    public function testCustomHeadersQueue() {
        $mail = new CM_Mail();
        $subject = uniqid();
        $mail->setSubject($subject);
        $mail->setCustomHeader('X-Foo', 'bar');
        $mail->setCustomHeader('X-Bar', 'foo');
        $mail->setCustomHeader('X-Foo', 'baz');
        $mail->addTo('test');
        $mail->setText('bla');
        $mail->send(true);
        $result = CM_Db_Db::select('cm_mail', 'customHeaders', array('subject' => $subject));
        $row = $result->fetch();
        $this->assertEquals(unserialize($row['customHeaders']), array('X-Foo' => ['bar', 'baz'], 'X-Bar' => ['foo']));
    }
}

class Test_CM_Mail extends CM_Mail {

    /** @var CM_MailTest $_test */
    private $_test = null;

    /**
     * @param CM_MailTest $test
     */
    public function __construct($recipient = null, array $tplParams = null, CM_Site_Abstract $site = null, $test) {
        $this->_test = $test;
        parent::__construct($recipient, $tplParams, $site);
    }

    protected function _getPHPMailer() {
        return $this->_test->getMockPhpMailer();
    }
}
