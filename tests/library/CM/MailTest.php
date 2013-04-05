<?php

class CM_MailTest extends CMTest_TestCase {

	public function tearDown() {
		CMTest_TH::clearEnv();
	}

	public function testWithTemplate() {
		$user = $this->getMock('CM_Model_User', array('getEmail', 'getSite'), array(CMTest_TH::createUser()->getId()));
		$user->expects($this->any())->method('getEmail')->will($this->returnValue('foo@example.com'));
		$user->expects($this->any())->method('getSite')->will($this->returnValue($this->_getSite()));

		$templateVariabels = array('foo' => 'bar');
		$msg = new CM_Mail_Welcome($user, $templateVariabels);
		list($subject, $html, $text) = $msg->send();
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
		list($subject, $html, $text) = $msg->send(null, $this->_getSite());
		$this->assertEquals('blabla', $subject);
		$this->assertEquals('<a href="http://www.foo.bar">Hello</a>', $html);
		$this->assertEquals('Hello (http://www.foo.bar)', $text);
	}

	public function testQueue() {
		$user = $this->getMock('CM_Model_User', array('getEmail', 'getSite'), array(CMTest_TH::createUser()->getId()));
		$user->expects($this->any())->method('getEmail')->will($this->returnValue('foo@example.com'));
		$user->expects($this->any())->method('getSite')->will($this->returnValue($this->_getSite()));

		$msg = new CM_Mail($user, null);
		$msg->setSubject('testSubject');
		$msg->setHtml('<b>hallo</b>');
		$msg->addReplyTo('foo@bar.com');
		$msg->addCc('foo@bar.org', 'foobar');
		$msg->addBcc('foo@bar.net');
		$msg->sendDelayed();
		$this->assertRow(TBL_CM_MAIL, array('subject' => 'testSubject',
											'text'    => 'hallo',
											'html'    => '<b>hallo</b>',
											'to'      => serialize($msg->getTo()),
											'replyTo' => serialize($msg->getReplyTo()),
											'cc'      => serialize($msg->getCc()),
											'bcc'     => serialize($msg->getBcc())));
		$this->assertEquals(1, CM_Db_Db::count(TBL_CM_MAIL, 'id'));
		CM_Mail::processQueue(1);
		$this->assertEquals(0, CM_Db_Db::count(TBL_CM_MAIL, 'id'));
	}

	public function testSetText() {
		$mail = new CM_Mail();
		$text = 'foo bar foo bar';
		$mail->setText($text);
		$this->assertSame($text, $mail->getText());
	}

	public function testSend() {
		$mail = $this->getMockBuilder('CM_Mail')->setMethods(array('_render', '_send'))->getMock();
		$mail->expects($this->any())->method('_render');
		$mail->expects($this->exactly(1))->method('_send');
		/** @var $mail CM_Mail */
		try {
			$mail->send();
			$this->fail('Sent mail without recipient');
		} catch (Exception $e) {
			$this->assertContains('No recipient specified', $e->getMessage());
		}
		$mail->addTo('tomasz@durka.pl');
		$mail->setSubject('foo');
		$mail->setText('bar');
		$this->assertSame(0, CM_Mail::getQueueSize());
		$mail->send();
		$this->assertSame(0, CM_Mail::getQueueSize());
		$mail->send(true);
		$this->assertSame(1, CM_Mail::getQueueSize());
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
}
