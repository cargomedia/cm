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

		// Test adding languageKey by getTranslation() and setting value by setTranslation
		$this->assertSame('keyFirst', $this->_language->getTranslation('keyFirst', null, true));
		$this->_language->setTranslation('keyFirst', 'abc');
		$this->assertSame('abc', $this->_language->getTranslation('keyFirst', null, true));
		$this->assertSame(array('keyFirst' => 'abc'), $this->_language->getTranslations()->getAssociativeArray());

		// Test adding languageKey and setting value by setTranslation()
		$this->_language->setTranslation('keySecond', 'abc');
		$this->assertSame('abc', $this->_language->getTranslation('keySecond', null, true));
		$this->assertSame(array('keyFirst' => 'abc', 'keySecond' => 'abc'), $this->_language->getTranslations()->getAssociativeArray());

		// Test adding languageKey with not flushing and flushing cache
		$this->_language->getTranslation('keySecond'); // Make sure its cached
		$this->_language->setTranslation('keySecond', 'xyz');
		$this->assertNotSame('xyz', $this->_language->getTranslation('keySecond'));
		CM_Model_Language::flushCacheLocal();
		$this->assertSame('xyz', $this->_language->getTranslation('keySecond'));
	}

	public function testVariables() {
		$this->_language->setTranslation('abc {$variable}', 'translated stuff is {$variable}');
		$this->assertSame('translated stuff is cool', $this->_language->getTranslation('abc {$variable}', array('variable' => 'cool')));
	}

	public function testCreate() {
		/** @var CM_Model_Language $language */
		$language = CM_Model_Language::create(array('name' => 'Deutsch', 'abbreviation' => 'de', 'enabled' => true));

		$this->assertSame('Deutsch', $language->getName());
		$this->assertSame('de', $language->getAbbreviation());
		$this->assertSame(true, $language->getEnabled());
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
