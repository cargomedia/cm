<?php

class CM_File_ImageTest extends CMTest_TestCase {

	public function testConstruct() {
		$path = DIR_TEST_DATA . 'img/test.jpg';
		$image = new CM_File_Image($path);

		$this->assertEquals($path, $image->getPath());
		$this->assertEquals('image/jpeg', $image->getMimeType());
	}

	public function testConstructCorruptFile() {
		$this->markTestSkipped('something wrong -> imagick version problem');
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

	public function testConstructorEmptyFile() {
		$path = DIR_TEST_DATA . 'img/empty.jpg';

		try {
			$image = new CM_File_Image($path);
			$this->fail('Should throw exception because of no exif data');
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
		$pathNew = '/tmp/' . uniqid();
		$image = new CM_File_Image($path);

		$image->rotate(90, $pathNew);
		$imageNew = new CM_File_Image($pathNew);
		$this->assertSame($image->getHeight(), $imageNew->getWidth());
		$this->assertSame($image->getWidth(), $imageNew->getHeight());
	}

	public function testConvert() {
		$path = DIR_TEST_DATA . 'img/test.jpg';
		$pathNew = '/tmp/' . uniqid();
		$image = new CM_File_Image($path);

		$image->convert(IMAGETYPE_GIF, $pathNew);
		$imageNew = new CM_File_Image($pathNew);
		$this->assertSame($image->getWidth(), $imageNew->getWidth());
		$this->assertSame($image->getHeight(), $imageNew->getHeight());
	}

	public function testResize() {
		$path = DIR_TEST_DATA . 'img/test.jpg';
		$pathNew = '/tmp/' . uniqid();
		$image = new CM_File_Image($path);

		$image->resize(50, 50, false, $pathNew);
		$imageNew = new CM_File_Image($pathNew);
		$scale = ($image->getWidth() / 50 > $image->getHeight() / 50) ? 50 / $image->getWidth() : 50 / $image->getHeight();
		$widthExpected = (int) ($image->getWidth() * $scale);
		$heightExpected = (int) ($image->getHeight() * $scale);
		$this->assertSame($widthExpected, $imageNew->getWidth());
		$this->assertSame($heightExpected, $imageNew->getHeight());
	}

	public function testResizeSquare() {
		$path = DIR_TEST_DATA . 'img/test.jpg';
		$pathNew = '/tmp/' . uniqid();
		$image = new CM_File_Image($path);

		$image->resize(50, 50, true, $pathNew);
		$imageNew = new CM_File_Image($pathNew);
		$this->assertSame(50, $imageNew->getWidth());
		$this->assertSame(50, $imageNew->getHeight());
	}

	public function testResizeSquareNoBlowup() {
		$path = DIR_TEST_DATA . 'img/test.jpg';
		$pathNew = '/tmp/' . uniqid();
		$image = new CM_File_Image($path);

		$image->resize(5000, 5000, true, $pathNew);
		$imageNew = new CM_File_Image($pathNew);
		$sizeExpected = min($image->getWidth(), $image->getHeight());
		$this->assertSame($sizeExpected, $imageNew->getWidth());
		$this->assertSame($sizeExpected, $imageNew->getHeight());
	}
}
