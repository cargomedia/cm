<?php
require_once dirname(__FILE__) . '/../../TestCase.php';

class CM_MailTest extends TestCase {
	public static function setUpBeforeClass() {
		self::markTestIncomplete();

	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testWithTemplate() {

		$user = TH::createUser();
		$profile = TH::createProfile($user);
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

	public function testRender() {
		$user = TH::createUser();
		$profile = TH::createProfile($user);
		$msg = new CM_Mail($user, 'email_verify');
		list($subject, $html, $text) = CM_Render::getInstance()->render($msg);
		$this->assertEquals($profile->getUsername() . ', welcome to Fuckbook!', $subject);
		$page = new TH_Page($html);
		$this->assertContains($subject, $page->getText('h1'));
		$this->assertContains('Dear ' . $profile->getUsername(), $page->getHtml());
		$this->assertContains('Copyright &copy; Fuckbook', $page->getHtml());
		$this->assertContains('Then, it’s time to start - you can explore the site as you wish and browse members near you, then once you’ve found someone, you can reach out to them.', $page->getHtml());

		//now without tpl
		$msg = new CM_Mail($user);
		$msg->setSubject('haha');
		$html1 = '<p>
<a href="www.blabla.ch">hohoho</a>
</p>
blabla<br/>
hihi<br />
muahaha';
		$msg->setHtml($html1);
		list($subject, $html, $text) = CM_Render::getInstance()->render($msg);
		$this->assertEquals($html1, $html);
		$expectedText = 'hohoho (www.blabla.ch) 
blabla
hihi
muahaha';
		$this->assertEquals($expectedText, $text);
		$msg->setRenderLayout();
		list($subject, $html, $text) = CM_Render::getInstance()->render($msg);
		$page = new TH_Page($html);
		$this->assertContains('haha', $page->getText('h1'));
		$this->assertContains('Contact us: ' . URL_ROOT . 'about/contact', $text);
	}

	public function testQueue() {

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
