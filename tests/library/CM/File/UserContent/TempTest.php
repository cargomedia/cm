<?php

class CM_File_UserContent_TempTest extends CMTest_TestCase {

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		CMTest_TH::clearEnv();
	}

	public function testConstructorInvalid() {
		try {
			new CM_File_UserContent_Temp(uniqid());
			$this->fail('should throw an exception because id does not exist');
		} catch (CM_Exception_Nonexistent $e) {
			$this->assertTrue(true);
		}
	}

	public function testCreate() {
		$file = CM_File_UserContent_Temp::create('foo.txt');
		$this->assertInstanceOf('CM_File_UserContent_Temp', $file);
		$this->assertSame('foo.txt', $file->getFileName());
		$this->assertSame('txt', $file->getExtension());
		$this->assertInternalType('string', $file->getUniqid());
	}

	public function testCreateLongName() {
		$file = CM_File_UserContent_Temp::create(str_repeat('a', 500) . '.txt');
		$this->assertSame(str_repeat('a', 96) . '.txt', $file->getFileName());
	}

	public function testCreateNoExtension() {
		$file = CM_File_UserContent_Temp::create('foo');
		$this->assertSame('', $file->getExtension());
	}

	public function testCreateContent() {
		$file = CM_File_UserContent_Temp::create('foo.txt', 'bar');
		$this->assertSame('bar', $file->read());
	}

	public function testConstruct() {
		$file = CM_File_UserContent_Temp::create('foo.txt');
		$file2 = new CM_File_UserContent_Temp($file->getUniqid());
		$this->assertEquals($file2, $file);
	}

	public function testDelete() {
		$file = CM_File_UserContent_Temp::create('foo.txt', 'bar');
		$this->assertTrue($file->getExists());

		$file->delete();
		$this->assertFalse($file->getExists());
		try {
			new CM_File_UserContent_Temp($file->getUniqid());
			$this->fail('Can instantiate deleted temp file');
		} catch (CM_Exception_Nonexistent $e) {
			$this->assertTrue(true);
		}
	}

	public function testDeleteOlder() {
		$file = CM_File_UserContent_Temp::create('foo.txt', 'bar');
		$this->assertTrue($file->getExists());

		CM_File_UserContent_Temp::deleteOlder(100);
		$this->assertTrue($file->getExists());

		CMTest_TH::timeForward(1000);
		CM_File_UserContent_Temp::deleteOlder(100);
		$this->assertFalse($file->getExists());
	}

}
