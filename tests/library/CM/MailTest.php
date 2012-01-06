<?php
require_once __DIR__ . '/../../TestCase.php';

class CM_MailTest extends TestCase {
	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testWithTemplate() {
		$user = $this->getMock('CM_Model_User', array('getEmail'), array(TH::createUser()->getId()));
		$user->expects($this->any())->method('getEmail')->will($this->returnValue('foo@example.com'));

		try {
			$msg = new CM_Mail_Welcome($user);
			list($subject, $html, $text) = $msg->send();
			$this->assertNotEmpty($subject);
			$this->assertNotEmpty($html);
			$this->assertNotEmpty($text);
		} catch (Exception $e) {
			$this->fail('Cannot send mail: ' . $e->getMessage());
		}
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
		list($subject, $html, $text) = $msg->send();
		$this->assertEquals('blabla', $subject);
		$this->assertEquals('<a href="http://www.foo.bar">Hello</a>', $html);
		$this->assertEquals('Hello (http://www.foo.bar)', $text);
	}

	public function testQueue() {
		$user = $this->getMock('CM_Model_User', array('getEmail'), array(TH::createUser()->getId()));
		$user->expects($this->any())->method('getEmail')->will($this->returnValue('foo@example.com'));
		$msg = new CM_Mail($user, null, true);
		$msg->setSubject('testSubject');
		$msg->setHtml('<b>hallo</b>');
		$msg->send();
		$this->assertRow(TBL_CM_MAIL, array('subject' => 'testSubject', 'text' => 'hallo', 'html' => '<b>hallo</b>',
			'recipientAddress' => 'foo@example.com'));
	}
}
