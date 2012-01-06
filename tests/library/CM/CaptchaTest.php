<?php
require_once __DIR__ . '/../../TestCase.php';

class CM_CaptchaTest extends TestCase {

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testCreate() {
		$captcha = CM_Captcha::create();
		$this->assertInstanceOf('CM_Captcha', $captcha);
	}

	public function testConstructor() {
		$id = CM_Captcha::create()->getId();
		$captcha = new CM_Captcha($id);
		$this->assertSame($id, $captcha->getId());
	}

	public function testGetText() {
		$captcha = CM_Captcha::create();
		$this->assertInternalType('string', $captcha->getText());
	}

	public function testCheck() {
		$captcha = CM_Captcha::create();
		$id = $captcha->getId();
		$this->assertFalse($captcha->check('foooo'));
		try {
			new CM_Captcha($id);
			$this->fail('Can construct checked captcha');
		} catch (CM_Exception_Nonexistent $e) {
			$this->assertTrue(true);
		}

		$captcha = CM_Captcha::create();
		$id = $captcha->getId();
		$this->assertTrue($captcha->check($captcha->getText()));
		try {
			new CM_Captcha($id);
			$this->fail('Can construct checked captcha');
		} catch (CM_Exception_Nonexistent $e) {
			$this->assertTrue(true);
		}
	}

	public function testDeleteOlder() {
		$captcha = CM_Captcha::create();
		$id = $captcha->getId();
		TH::timeForward(200);
		CM_Captcha::deleteOlder(100);
		try {
			new CM_Captcha($id);
			$this->fail('Can construct old captcha');
		} catch (CM_Exception_Nonexistent $e) {
			$this->assertTrue(true);
		}
	}
}
