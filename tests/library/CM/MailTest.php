<?php
require_once dirname(__FILE__) . '/../../TestCase.php';

class CM_MailTest extends TestCase {
	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testWithTemplate() {

		$user = TH::createUser();

		try {
			$msg = new CM_Mail($user, 'email_verify');
			$msg->send();
			$this->assertTrue(true);
		} catch (Exception $ex) {
			$this->fail("Something's wrong");
		}
	}

	public function testNoTemplate() {

		$msg = new CM_Mail('bla@bla.ch');
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
		$this->markTestIncomplete('Uses profile');
		$user = TH::createUser();
		$profile = TH::createProfile($user);
		$profile->setEmailVerified();
		$msg = new CM_Mail($user, null, true);
		$msg->setSubject('testSubject');
		$msg->setHtml('<b>hallo</b>');
		$msg->send();
		$this->assertRow(TBL_CM_MAIL, array('subject' => 'testSubject', 'text' => 'hallo', 'html' => '<b>hallo</b>',
			'recipientAddress' => $profile->getEmail()));
	}
}
