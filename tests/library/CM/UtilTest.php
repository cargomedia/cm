<?php
require_once __DIR__ . '/../../TestCase.php';

class CM_UtilTest extends TestCase {

	public function testGetClasses() {
		$classPaths = array();
		$classPaths['CM_Class_Abstract'] = 'CM/Class/Abstract.php';
		$classPaths['CM_Paging_Abstract'] = 'CM/Paging/Abstract.php';
		$classPaths['CM_Paging_Action_Abstract'] = 'CM/Paging/Action/Abstract.php';
		$classPaths['CM_Paging_Action_User'] = 'CM/Paging/Action/User.php';

		foreach($classPaths as $className => &$path){
			$path = CM_Util::getNamespacePath(CM_Util::getNamespace($className)) . 'library/' . $path;
		}

		$paths = array_reverse($classPaths);
		$this->assertSame(array_flip($classPaths), CM_Util::getClasses($paths));
	}

}
