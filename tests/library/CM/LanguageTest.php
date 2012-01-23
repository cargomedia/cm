<?php
require_once __DIR__ . '/../../TestCase.php';

class CM_LanguageTest extends TestCase {

	public static function setUpBeforeClass() {
		CM_Mysql::insert(TBL_CM_LANG, array('abbrev' => 'EN', 'label' => 'English', 'enabled' => 1));
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testCreate() {
		$keyId1 = CM_LanguageEdit::createKey('test.' . uniqid() . '.' . uniqid(), 'name');
		$this->assertGreaterThan(0, $keyId1);

		$keyId2 = CM_LanguageEdit::createKey('test.' . uniqid() . '.' . uniqid(), 'name');
		$this->assertGreaterThan($keyId1, $keyId2);
	}

	public function testText() {
		$configBackup = CM_Config::get();

		CM_LanguageEdit::createSection(0, 'foo');
		CM_Config::get()->CM_Language->autoCreate = false;
		try {
			CM_Language::text('foo.bar');
			$this->fail('Can access nonexistent language path');
		} catch (CM_Exception $e) {
			$this->assertContains('not found', $e->getMessage());
		}

		CM_Config::set($configBackup);
	}

	public function testAutoCreate() {
		$configBackup = CM_Config::get();

		CM_Config::get()->CM_Language->autoCreate = true;
		$this->assertSame('bar', CM_Language::text('foo.bar'));

		CM_Config::set($configBackup);
	}
}
