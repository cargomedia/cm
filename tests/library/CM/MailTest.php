<?php
require_once dirname(__FILE__) . '/../../TestCase.php';

class CM_MailTest extends TestCase {
	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testWithTemplate() {
		$this->markTestIncomplete('No mail template available');
		$user = new CM_Model_User_Mock(TH::createUser()->getId());
		try {
			$msg = new CM_Mail($user, 'template_test');
			$msg->send();
			$this->assertTrue(true);
		} catch (Exception $ex) {
			$this->fail('Cannot send mail: ' . $ex->getMessage());
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
		try {
			$msg->setTplParam('bla', 'bla');
			$this->failure('Should have thrown an exception');
		} catch (CM_Exception_Invalid $ex) {
			$this->assertTrue(true);
		}
	}

	public function testQueue() {
		$user = new CM_Model_User_Mock(TH::createUser()->getId());
		$msg = new CM_Mail($user, null, true);
		$msg->setSubject('testSubject');
		$msg->setHtml('<b>hallo</b>');
		$msg->send();
		$this->assertRow(TBL_CM_MAIL, array('subject' => 'testSubject', 'text' => 'hallo', 'html' => '<b>hallo</b>',
			'recipientAddress' => 'foo@example.com'));
	}
}

class CM_Model_User_Mock extends CM_Model_User {
	public function getEmail() {
		return 'foo@example.com';
	}
}