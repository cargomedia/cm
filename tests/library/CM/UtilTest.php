<?php
require_once __DIR__ . '/../../TestCase.php';

class CM_UtilTest extends TestCase {

	public function testGetChildrenClasses() {
		$parent = 'CM_Model_Abstract';
		$grandParent = get_parent_class($parent);
		$childrenClasses = CM_Util::getChildrenClasses($parent);
		$this->assertContains('CM_Model_Language', $childrenClasses);
		$this->assertContains('CM_Model_StreamChannel_Message', $childrenClasses);
		$this->assertNotContains($grandParent, $childrenClasses);

		foreach ($childrenClasses as $className) {
			$this->assertTrue(is_subclass_of($className, $parent));
			$this->assertNotSame(get_parent_class($className), $grandParent);
		}
	}
}