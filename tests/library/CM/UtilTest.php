<?php

class CM_UtilTest extends CMTest_TestCase {

	public function testGetClasses() {
		$classPaths = array(
			'CM_Class_Abstract'         => 'CM/Class/Abstract.php',
			'CM_Paging_Abstract'        => 'CM/Paging/Abstract.php',
			'CM_Paging_Action_Abstract' => 'CM/Paging/Action/Abstract.php',
			'CM_Paging_Action_User'     => 'CM/Paging/Action/User.php',
		);
		foreach ($classPaths as $className => &$path) {
			$path = CM_Util::getNamespacePath(CM_Util::getNamespace($className)) . 'library/' . $path;
		}
		$paths = array_reverse($classPaths);
		$this->assertSame(array_flip($classPaths), CM_Util::getClasses($paths));
	}

	public function testGetNamespace() {
		$this->assertInternalType('string', CM_Util::getNamespace('CM_Util'));

		$this->assertNull(CM_Util::getNamespace('NoNamespace', true));

		try {
			CM_Util::getNamespace('NoNamespace', false);
			$this->fail('Namespace detected in a className without namespace.');
		} catch (CM_Exception_Invalid $ex) {
			$this->assertContains('Could not detect namespace of `NoNamespace`.', $ex->getMessage());
		}
	}

	public function testTitleize() {
		$testCases = array(
			'foo'     => 'Foo',
			'Foo'     => 'Foo',
			'foo bar' => 'Foo bar',
			'foo-bar' => 'Foo Bar',
			'foo.bar' => 'Foo.bar',
		);
		foreach ($testCases as $actual => $expected) {
			$this->assertSame($expected, CM_Util::titleize($actual));
		}
	}

	public function testGetResourceFiles() {
		$files = CM_Util::getResourceFiles('config/default.php');
		if (!count($files)) {
			$this->markTestSkipped('There are no files to test this functionality');
		}
		foreach ($files as $file) {
			$this->assertInstanceOf('CM_File', $file);
			$this->assertSame('default.php', $file->getFileName());
		}
	}

	public function testBenchmark() {
		$reverse = function ($timeString) {
			$results = sscanf($timeString, '%f ms');
			return $results[0];
		};
		$this->assertEquals(0, $reverse(CM_Util::benchmark()));
		$this->assertGreaterThan(0, $reverse(CM_Util::benchmark()));

		CM_Util::benchmark('foo');
		usleep(100);
		CM_Util::benchmark('bar');
		$this->assertGreaterThan($reverse(CM_Util::benchmark('bar')), $reverse(CM_Util::benchmark('foo')));
	}
}
