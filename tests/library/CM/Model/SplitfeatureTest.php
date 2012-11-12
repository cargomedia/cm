<?php
require_once __DIR__ . '/../../../TestCase.php';

class CM_Model_SplitfeatureTest extends TestCase {

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testCreate() {
		$splitFeature = CM_Model_Splitfeature::create(array('name' => 'foo', 'percentage' => 50));
		$this->assertInstanceOf('CM_Model_SplitFeature', $splitFeature);

		$splitFeature->delete();
	}

	public function testCreateDuplicate() {
		$splitFeature = CM_Model_Splitfeature::create(array('name' => 'foo', 'percentage' => 50));

		try {
			CM_Model_Splitfeature::create(array('name' => 'foo', 'percentage' => 50));
			$this->fail('Could create duplicate splitfeature');
		} catch (CM_Exception $e) {
			$this->assertContains("`Duplicate entry 'foo' for key 'name'`", $e->getMessage());
		}

		$splitFeature->delete();
	}

	public function testCreateNegativPercentage() {
		try {
			CM_Model_Splitfeature::create(array('name' => 'foo', 'percentage' => -1));
			$this->fail('Could create splitfeature with negativ percentage');
		} catch (CM_Exception_InvalidParam $e) {
			$this->assertSame('Percentage must be between 0 and 100 -1 was given', $e->getMessage());
		}
	}

	public function testCreatePercentageOutOfRange() {
		try {
			CM_Model_Splitfeature::create(array('name' => 'foo', 'percentage' => 110));
			$this->fail('Could create splitfeature with more then 100%');
		} catch (CM_Exception $e) {
			$this->assertSame('Percentage must be between 0 and 100 110 was given', $e->getMessage());
		}
	}

	public function testConstruct() {
		$splitFeature = CM_Model_Splitfeature::create(array('name' => 'foo', 'percentage' => 50));
		$splitFeature2 = new CM_Model_Splitfeature('foo');
		$this->assertModelEquals($splitFeature, $splitFeature2);

		$splitFeature->delete();
	}

	public function testGetId() {
		$splitFeature = CM_Model_Splitfeature::create(array('name' => 'foo', 'percentage' => 50));
		$this->assertGreaterThanOrEqual(1, $splitFeature->getId());

		$splitFeature->delete();
	}

	public function testGetName() {
		/** @var CM_Model_Splitfeature $splitFeature */
		$splitFeature = CM_Model_Splitfeature::create(array('name' => 'foo', 'percentage' => 50));
		$this->assertSame('foo', $splitFeature->getName());

		$splitFeature->delete();
	}

	public function testGetPercentage() {
		/** @var CM_Model_Splitfeature $splitFeature */
		$splitFeature = CM_Model_Splitfeature::create(array('name' => 'foo', 'percentage' => 50));
		$this->assertSame(50, $splitFeature->getPercentage());

		$splitFeature->delete();
	}

	public function testSetPercentage() {
		/** @var CM_Model_Splitfeature $splitFeature */
		$splitFeature = CM_Model_Splitfeature::create(array('name' => 'foo', 'percentage' => 50));

		$splitFeature->setPercentage(80);
		$this->assertSame(80, $splitFeature->getPercentage());

		try {
			$splitFeature->setPercentage(110);
			$this->fail('Could set percentage > 100%');
		} catch (CM_Exception $e) {
			$this->assertTrue(true);
		}

		$splitFeature->delete();
	}

	public function testGetEnabled() {
		/** @var CM_Model_Splitfeature $splitFeature */
		$splitFeature = CM_Model_Splitfeature::create(array('name' => 'foo', 'percentage' => 50));

		/** @var CM_Model_Splitfeature $splitFeature2 */
		$splitFeature2 = CM_Model_Splitfeature::create(array('name' => 'bar', 'percentage' => 10));

		$i = 0;
		$userArray = array();
		while($i < 200) {
			$user = TH::createUser();
			$splitFeature->getEnabled($user);
			$splitFeature2->getEnabled($user);
			$userArray[] = $user;
			$i++;
		}

		TH::clearCache();
		$this->_checkEnabledFlag($userArray, $splitFeature);
		$this->_checkEnabledFlag($userArray, $splitFeature2);

		$splitFeature->setPercentage(99);
		$this->_checkEnabledFlag($userArray, $splitFeature);

		$splitFeature2->getPercentage(2);
		$this->_checkEnabledFlag($userArray, $splitFeature2);

		$splitFeature->setPercentage(14);
		$this->_checkEnabledFlag($userArray, $splitFeature);

		$splitFeature2->setPercentage(66);
		$this->_checkEnabledFlag($userArray, $splitFeature2);
	}

	/**
	 * @param CM_Model_User[]       $userList
	 * @param CM_Model_Splitfeature $splitFeature
	 */
	private function _checkEnabledFlag($userList, CM_Model_Splitfeature $splitFeature) {
		foreach($userList as $key => $user) {
			if ($key % 100 < $splitFeature->getPercentage()) {
				$this->assertTrue($splitFeature->getEnabled($user));
			} else {
				$this->assertFalse($splitFeature->getEnabled($user));
			}
		}
	}

}

