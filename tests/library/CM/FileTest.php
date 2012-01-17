<?php

require_once __DIR__ . '/../../TestCase.php';

class CM_FileTest extends TestCase {

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
		$this->assertEquals('image/jpeg', $file->getType());
		$this->assertEquals('jpg', $file->getExtension());
		$this->assertEquals('test.jpg', $file->getFileName());
		$this->assertEquals(filesize($this->_testFilePath), $file->getSize());
		$this->assertEquals(file_get_contents($this->_testFilePath), $file->read());
	}
	
	public function testDelete() {
		$file = new CM_File($this->_testFilePath);
		
		$this->assertTrue(file_exists($this->_testFilePath));
		
		$file->delete();
		
		$this->assertFalse(file_exists($this->_testFilePath));
		
		// Should do nothing if already deleted
		$file->delete();
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
	}
}
