<?php

require_once __DIR__ . '/../../../TestCase.php';

class CM_File_ImageTest extends TestCase {

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testConstruct() {
		$path = DIR_TEST_DATA . 'img/test.jpg';
		$image = new CM_File_Image($path);

		$this->assertEquals($path, $image->getPath());
		$this->assertEquals('image/jpeg', $image->getMimeType());
	}

	public function testConstructCorruptFile() {
		$this->markTestIncomplete('something wrong -> imagick version problem');
		$path = DIR_TEST_DATA . 'img/corrupt-content.jpg';

		try {
			$image = new CM_File_Image($path);
			$this->fail('Should throw exception because invalid file');
		} catch (CM_Exception_Invalid $e) {
			$this->assertTrue(true);
		}
	}

	public function testConstructCorruptHeader() {
		$path = DIR_TEST_DATA . 'img/corrupt-header.jpg';

		try {
			$image = new CM_File_Image($path);
			$this->fail('Should throw exception because invalid file');
		} catch (CM_Exception_Invalid $e) {
			$this->assertTrue(true);
		}
	}

	public function testConstructJpgNoExtension() {
		$path = DIR_TEST_DATA . 'img/jpg-no-extension';

		// Should work as image is identified by header and not extension
		$image = new CM_File_Image($path);

		$this->assertEquals($path, $image->getPath());
		$this->assertEquals('image/jpeg', $image->getMimeType());
	}

	public function testConstructNoImage() {
		$path = DIR_TEST_DATA . 'test.jpg.zip';

		try {
			$image = new CM_File_Image($path);
			$this->fail('Should throw exception because invalid file');
		} catch (CM_Exception_Invalid $e) {
			$this->assertTrue(true);
		}
	}

	public function testDimensions() {
		$path = DIR_TEST_DATA . 'img/test.jpg';
		list($width, $height) = getimagesize($path);

		$image = new CM_File_Image($path);
		$this->assertSame($width, $image->getWidth());
		$this->assertSame($height, $image->getHeight());
	}

	public function testRotate() {
		$path = DIR_TEST_DATA . 'img/test.jpg';
		$pathDest = '/tmp/' . uniqid();
		$image = new CM_File_Image($path);
		$image->rotate(90, $pathDest);
	}

	public function testSquareImages() {
		$sourcePath = DIR_TEST_DATA . 'img/square-image.';
		$path = '/tmp/' . uniqid();

		$image = new CM_File_Image($sourcePath);
		$image->resize(900, 900, false, $path);

		$image->resize(250, 250, false, $path);

		$imagePreview = new CM_File_Image($path);
		$imagePreview->resize(100, 100, true, $path);
	}
}
