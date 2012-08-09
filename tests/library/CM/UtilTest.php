<?php
require_once __DIR__ . '/../../TestCase.php';

class CM_UtilTest extends TestCase {

	public function testGetClasses() {
		$classesExpected = array();
		$classesExpected[DIR_LIBRARY . 'CM/Class/Abstract.php'] = 'CM_Class_Abstract';
		$classesExpected[DIR_LIBRARY . 'CM/Paging/Abstract.php'] = 'CM_Paging_Abstract';
		$classesExpected[DIR_LIBRARY . 'CM/Paging/Action/Abstract.php'] = 'CM_Paging_Action_Abstract';
		$classesExpected[DIR_LIBRARY . 'CM/Paging/Action/User.php'] = 'CM_Paging_Action_User';

		$paths = array_keys($classesExpected);
		$paths = array_reverse($paths);
		$this->assertSame($classesExpected, CM_Util::getClasses($paths));
	}

}