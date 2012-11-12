<?php
require_once __DIR__ . '/../../../TestCase.php';

class CM_Model_SplitFeatureTest extends TestCase {

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testCreate() {
		$splitFeature = CM_Model_SplitFeature::create(array('name' => 'foo', 'percentage' => 50));
		$this->assertInstanceOf('CM_Model_SplitFeature', $splitFeature);

		try {
			CM_Model_SplitFeature::create(array('name' => 'foo', 'percentage' => 50));
			$this->fail('Could create duplicate splitfeature');
		} catch (CM_Exception $e) {
			$this->assertTrue(true);
		}

		$splitFeature->delete();

		try {
			CM_Model_SplitFeature::create(array('name' => 'foo', 'percentage' => -1));
			$this->fail('Could create splitfeature with negativ percentage');
		} catch (CM_Exception $e) {
			$this->assertTrue(true);
		}

		try {
			CM_Model_SplitFeature::create(array('name' => 'foo', 'percentage' => 110));
			$this->fail('Could create splitfeature with more then 100%');
		} catch (CM_Exception $e) {
			$this->assertTrue(true);
		}

	}

	public function testConstruct() {
		$splitFeature = CM_Model_SplitFeature::create(array('name' => 'foo', 'percentage' => 50));
		$splitFeature2 = new CM_Model_SplitFeature('foo');
		$this->assertModelEquals($splitFeature, $splitFeature2);

		$splitFeature->delete();
	}

	public function testGetId() {
		$splitFeature = CM_Model_SplitFeature::create(array('name' => 'foo', 'percentage' => 50));
		$this->assertGreaterThanOrEqual(1, $splitFeature->getId());

		$splitFeature->delete();
	}

	public function testGetName() {
		/** @var CM_Model_SplitFeature $splitFeature */
		$splitFeature = CM_Model_SplitFeature::create(array('name' => 'foo', 'percentage' => 50));
		$this->assertSame('foo', $splitFeature->getName());

		$splitFeature->delete();

	}

	public function testGetPercentage() {
		/** @var CM_Model_SplitFeature $splitFeature */
		$splitFeature = CM_Model_SplitFeature::create(array('name' => 'foo', 'percentage' => 50));
		$this->assertSame(50, $splitFeature->getPercentage());

		$splitFeature->delete();

	}

	public function testSetPercentage() {
		/** @var CM_Model_SplitFeature $splitFeature */
		$splitFeature = CM_Model_SplitFeature::create(array('name' => 'foo', 'percentage' => 50));

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
		/** @var CM_Model_SplitFeature $splitFeature */
		$splitFeature = CM_Model_SplitFeature::create(array('name' => 'foo', 'percentage' => 50));

		/** @var CM_Model_SplitFeature $splitFeature2 */
		$splitFeature2 = CM_Model_SplitFeature::create(array('name' => 'bar', 'percentage' => 10));

		$i = 0;
		$userArray = array();
		while($i < 200) {
			$user = TH::createUser();
			$splitFeature->getEnabled($user);
			$splitFeature2->getEnabled($user);
			$userArray[] = $user;
			$i++;
		}

		$this->assertTrue($splitFeature->getEnabled($userArray[49]));
		$this->assertFalse($splitFeature->getEnabled($userArray[50]));
		$this->assertTrue($splitFeature->getEnabled($userArray[149]));
		$this->assertFalse($splitFeature->getEnabled($userArray[150]));

		$this->assertTrue($splitFeature2->getEnabled($userArray[0]));
		$this->assertFalse($splitFeature2->getEnabled($userArray[10]));
		$this->assertTrue($splitFeature2->getEnabled($userArray[102]));
		$this->assertFalse($splitFeature2->getEnabled($userArray[110]));

		$splitFeature->setPercentage(80);
		$this->assertTrue($splitFeature->getEnabled($userArray[79]));
		$this->assertFalse($splitFeature->getEnabled($userArray[80]));
		$this->assertTrue($splitFeature->getEnabled($userArray[179]));
		$this->assertFalse($splitFeature->getEnabled($userArray[180]));

		$splitFeature->setPercentage(10);
		$this->assertTrue($splitFeature->getEnabled($userArray[9]));
		$this->assertFalse($splitFeature->getEnabled($userArray[79]));
		$this->assertTrue($splitFeature->getEnabled($userArray[109]));
		$this->assertFalse($splitFeature->getEnabled($userArray[179]));

	}

}

