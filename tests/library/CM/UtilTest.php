<?php

class CM_UtilTest extends CMTest_TestCase {

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

	public function testGetNameSpace() {
		$this->assertInternalType('string', CM_Util::getNamespace('CM_Util'));

		$this->assertNull(CM_Util::getNamespace('NoNamespace', true));

		try {
			CM_Util::getNamespace('NoNamespace', false);
			$this->fail('Namespace detected in a className without namespace.');
		} catch (CM_Exception_Invalid $ex) {
			$this->assertContains('Could not detect namespace of `NoNamespace`.', $ex->getMessage());
		}
	}

}
