<?php
		require_once __DIR__ . '/../../../TestCase.php';

class CM_Model_LanguageTest extends TestCase {

	/** @var CM_Model_Language $_language */
	protected $_language;

	public function setUp() {
		$languageId = CM_Mysql::insert(TBL_CM_LANGUAGE, array('name' => 'English', 'abbreviation' => 'EN', 'enabled' => 1));
		$this->_language = new CM_Model_Language($languageId);
	}

	public function tearDown() {
		TH::clearEnv();
	}

	public function testSettingTranslation() {
		$this->assertSame(array(), $this->_language->_get('translations'));

		$languageKeyGet = 'test-by-getTranslation';
		$languageKeySet = 'test-by-setTranslation';
		$languageValue = 'This is the value';

		// Test adding languageKey by getTranslation() and setting value by setTranslation
		$this->assertSame($languageKeyGet, $this->_language->getTranslation($languageKeyGet));
		$this->_language->setTranslation($languageKeyGet, $languageValue);
		$this->assertSame($languageValue, $this->_language->getTranslation($languageKeyGet));
		$this->assertSame(array($languageKeyGet => $languageValue), $this->_language->_get('translations'));

		// Test adding languageKey and setting value by setTranslation()
		$this->_language->setTranslation($languageKeySet, $languageValue);
		$this->assertSame($languageValue, $this->_language->getTranslation($languageKeySet));
		$this->assertSame(array($languageKeyGet => $languageValue, $languageKeySet => $languageValue), $this->_language->_get('translations'));
	}

	public function testCreate() {
		/** @var CM_Model_Language $language */
		$language = CM_Model_Language::create(array('name' => 'Deutsch', 'abbreviation' => 'de', 'enabled' => true));

		$this->assertSame('Deutsch', $language->getName());
		$this->assertSame('de', $language->getAbbreviation());
		$this->assertSame(1, $language->getEnabled());
	}

	public function testCreateWithoutName() {
		try {
			CM_Model_Language::create(array('abbreviation' => 'de',));
			$this->fail('Could create language without name');
		} catch (CM_Exception_InvalidParam $e) {
			$this->assertContains('`name`', $e->getMessage());
		}
	}

	public function testDelete() {
		$this->_language->delete();
		try {
			new CM_Model_Language($this->_language->getId());
			$this->fail('Language has not been deleted');
		} catch (CM_Exception_Nonexistent $e) {
			$this->assertContains('CM_Model_Language', $e->getMessage());
		}
	}
}
