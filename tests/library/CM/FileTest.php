<?php

class CM_FileTest extends CMTest_TestCase {

	protected static $_backupContent;

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
	}

	protected $_testFilePath = '';

	public function setUp() {
		$this->_testFilePath = DIR_TEST_DATA . 'img/test.jpg';
		self::$_backupContent = file_get_contents($this->_testFilePath);
	}

	public function tearDown() {
		file_put_contents($this->_testFilePath, self::$_backupContent);
	}

	public function testConstruct() {
		$file = new CM_File($this->_testFilePath);

		$this->assertEquals($this->_testFilePath, $file->getPath());
		$this->assertEquals('image/jpeg', $file->getMimeType());
		$this->assertEquals('jpg', $file->getExtension());
		$this->assertEquals('37b1b8cb44ed126b0cd2fa25565b844b', $file->getHash());
		$this->assertEquals('test.jpg', $file->getFileName());
		$this->assertEquals(filesize($this->_testFilePath), $file->getSize());
		$this->assertEquals(file_get_contents($this->_testFilePath), $file->read());
		$this->assertEquals(file_get_contents($this->_testFilePath), '' . $file);
	}

	public function testConstructNonExistent() {
		try {
			new CM_File(DIR_TEST_DATA . '/nonexistent-file');
			$this->fail('Could instantiate non-existing file');
		} catch (Exception $e) {
			$this->assertContains('does not exist or is not a file', $e->getMessage());
		}
	}

	public function testDelete() {
		$file = new CM_File($this->_testFilePath);

		$this->assertTrue(file_exists($this->_testFilePath));

		$file->delete();

		$this->assertFalse(file_exists($this->_testFilePath));

		// Should do nothing if already deleted
		$file->delete();
	}

	public function testSanitizeFilename() {
		$filename = "~foo@! <}\   b\0a=r.tar.(gz";
		$this->assertSame("foo-bar.tar.gz", CM_File::sanitizeFilename($filename));

		try {
			CM_File::sanitizeFilename('&/&*<');
		} catch (CM_Exception_Invalid $ex) {
			$this->assertContains('Invalid filename.', $ex->getMessage());
		}
	}

	public function testWrite() {
		$file = new CM_File($this->_testFilePath);
		$this->assertNotEquals('foo', $file->read());

		$file->write('foo');
		$this->assertEquals('foo', $file->read());
	}

	public function testCreate() {
		$path = DIR_TEST_DATA . 'foo';
		$this->assertFalse(file_exists($path));

		$file = CM_File::create($path);
		$this->assertTrue(file_exists($path));
		$this->assertInstanceOf('CM_File', $file);
		$this->assertEquals($path, $file->getPath());
		$this->assertEquals('', $file->read());
		$file->delete();

		$file = CM_File::create($path, 'bar');
		$this->assertEquals('bar', $file->read());
		$file->delete();

		try {
			CM_File::create(DIR_TEST_DATA);
			$this->fail('Could create file with invalid path');
		} catch (CM_Exception $e) {
			$this->assertContains('Cannot write to', $e->getMessage());
		}
	}

	public function testCreateTmp() {
		$file = CM_File::createTmp();
		$this->assertTrue(file_exists($file->getPath()));
		$file->delete();

		$file = CM_File::createTmp('testExtension', 'bar');
		$this->assertContains('testextension', $file->getExtension());
		$this->assertEquals('bar', $file->read());
		$file->delete();
	}

	public function testTruncate() {
		$file = new CM_File($this->_testFilePath);
		$file->write('foo');
		$this->assertNotSame('', $file->read());
		$file->truncate();
		$this->assertSame('', $file->read());
	}

	public function testCopy() {
		$path = DIR_TMP . 'filecopytest.txt';
		$file = new CM_File($this->_testFilePath);
		$this->assertFileNotExists($path);
		$file->copy($path);
		$copiedFile = new CM_File($path);
		$this->assertTrue($copiedFile->getExists());
		$copiedFile->delete();

		try {
			$file->copy('/non-existent-path/not-existent-file');
			$this->fail('Should not be able to copy');
		} catch (Exception $e) {
			$this->assertContains('Cannot copy', $e->getMessage());
		}
	}

	public function testMove() {
		$newPath = DIR_TMP . 'filemovetest.txt';
		$file = new CM_File($this->_testFilePath);
		$oldPath = $file->getPath();

		$file->move($newPath);
		$this->assertFileNotExists($oldPath);
		$this->assertFileExists($newPath);
		$this->assertSame($newPath, $file->getPath());
		try {
			$file->move('/non-existent-path/not-existent-file');
			$this->fail('Should not be able to copy');
		} catch (Exception $e) {
			$this->assertFileExists($newPath);
			$this->assertContains('Cannot move', $e->getMessage());
		}
		$file->delete();
	}
}
