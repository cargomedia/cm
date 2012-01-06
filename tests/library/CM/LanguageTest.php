<?php
require_once __DIR__ . '/../../TestCase.php';

class CM_LanguageTest extends TestCase {

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testCreate() {
		$keyId1 = CM_LanguageEdit::createKey('test.' . uniqid() . '.' . uniqid(), 'name');
		$this->assertGreaterThan(0, $keyId1);

		$keyId2 = CM_LanguageEdit::createKey('test.' . uniqid() . '.' . uniqid(), 'name');
		$this->assertGreaterThan($key1, $keyId2);
	}
}
