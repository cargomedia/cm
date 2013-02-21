<?php

class CM_View_AbstractTest extends CMTest_TestCase {

	public function testGetClassesJavascript() {
		$namespaces = CM_Bootloader::getInstance()->getNamespaces();
		$pathClasses = CM_View_Abstract::getClasses($namespaces, CM_View_Abstract::CONTEXT_JAVASCRIPT);
		foreach ($pathClasses as $path => $className) {
			$path = preg_replace('#\.php$#', '.js', $path);
			$this->assertFileExists($path);
		}
	}
}
