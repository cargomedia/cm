<?php

class CM_UtilTest extends CMTest_TestCase {

	public function testBenchmark() {
		$this->assertSame(0.0, (float) CM_Util::benchmark());
		$this->assertSame(0.0, (float) CM_Util::benchmark('CM'));

		$this->assertGreaterThan(0, (float) CM_Util::benchmark());
		$this->assertGreaterThan(0, (float) CM_Util::benchmark('CM'));
	}

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

	public function testGetArrayTree() {
		$array = array(array('id' => 1, 'type' => 1, 'amount' => 1), array('id' => 2, 'type' => 1, 'amount' => 2),
			array('id' => 3, 'type' => 1, 'amount' => 3), array('id' => 4, 'type' => 1, 'amount' => 4));

		$this->assertSame(array(1 => array('type' => 1, 'amount' => 1), 2 => array('type' => 1, 'amount' => 2),
								3 => array('type' => 1, 'amount' => 3), 4 => array('type' => 1, 'amount' => 4)), CM_Util::getArrayTree($array));

		$this->assertSame(array(1 => array('id' => 1, 'type' => 1), 2 => array('id' => 2, 'type' => 1), 3 => array('id' => 3, 'type' => 1),
								4 => array('id' => 4, 'type' => 1)), CM_Util::getArrayTree($array, 1, true, 'amount'));

		$this->assertSame(array(1 => array(1 => 1), 2 => array(1 => 2), 3 => array(1 => 3),
								4 => array(1 => 4)), CM_Util::getArrayTree($array, 2, true, array('amount', 'type')));

		try {
			CM_Util::getArrayTree($array, 1, true, 'foo');
			$this->fail('Item has key `foo`.');
		} catch (CM_Exception_Invalid $ex) {
			$this->assertContains('Item has no key `foo`.', $ex->getMessage());
		}

		try {
			CM_Util::getArrayTree(array(1, 2));
			$this->fail('Item is not an array.');
		} catch (CM_Exception_Invalid $ex) {
			$this->assertContains('Item is not an array or has less than `2` elements.', $ex->getMessage());
		}

		try {
			CM_Util::getArrayTree(array(array(1), array(2)));
			$this->fail('Item has less than two elements.');
		} catch (CM_Exception_Invalid $ex) {
			$this->assertContains('Item is not an array or has less than `2` elements.', $ex->getMessage());
		}
	}

	public function testParseXml() {
		$xml = CM_Util::parseXml('<?xml version="1.0" encoding="utf-8"?><document><foo>bar</foo></document>');
		$this->assertInstanceOf('SimpleXMLElement', $xml);
		$this->assertSame('bar', (string) $xml->foo);

		try {
			CM_Util::parseXml('invalid xml');
			$this->fail('No exception for invalid xml');
		} catch (CM_Exception_Invalid $e) {
			$this->assertTrue(true);
		}
	}

	/**
	 * @expectedException CM_Exception
	 * @expectedExceptionMessage Cannot mkdir
	 */
	public function testMkDirExistingFile() {
		$path = CM_File::createTmp()->getPath();
		CM_File::create($path);
		CM_Util::mkDir($path);
	}

	/**
	 * @expectedException CM_Exception
	 * @expectedExceptionMessage Could not delete directory
	 */
	public function testRmDirNonexisting() {
		$path = CM_File::createTmp()->getPath();
		CM_Util::rmDir($path);
	}

	public function testRmDirContents() {
		$path = CM_Util::mkDir(DIR_TMP . uniqid());

		CM_File::create($path . '/.test');
		CM_File::create($path . '/test');

		CM_Util::rmDirContents($path);
	}
}
