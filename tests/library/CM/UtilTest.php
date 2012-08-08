<?php
require_once __DIR__ . '/../../TestCase.php';

class CM_UtilTest extends TestCase {

	public function testGetClasses() {
		$expected = array(
			// DIR_LIBRARY . 'CM/Class/Abstract.php'         => 'CM_Class_Abstract',
			DIR_LIBRARY . 'CM/Paging/Abstract.php'        => 'CM_Paging_Abstract',
			DIR_LIBRARY . 'CM/Paging/Action/Abstract.php' => 'CM_Paging_Action_Abstract',
			DIR_LIBRARY . 'CM/Paging/Action/User.php'     => 'CM_Paging_Action_User',
		);
		$paths = array_keys($expected);
		shuffle($paths);
		$this->assertSame($expected, CM_Util::getClasses($paths));
	}

}