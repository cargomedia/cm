<?php

class CM_Config_MappingTest extends CMTest_TestCase {

	public function testGetConfigKey() {
		$map = array(
			'foo' => 'CM_Foo',
		);
		$mapping = $this->getMockBuilder('CM_Config_Mapping')->setMethods(array('_getMapping'))->getMock();
		$mapping->expects($this->any())->method('_getMapping')->will($this->returnValue($map));
		/** @var CM_Config_Mapping $mapping */

		$this->assertSame('CM_Foo', $mapping->getConfigKey('foo'));

		try {
			$mapping->getConfigKey('bar');
			$this->fail('Got configkey from undefined mapping');
		} catch (CM_Exception_Invalid $ex) {
			$this->assertContains('There is no mapping for `bar`', $ex->getMessage());
		}
	}
}
