<?php

class CM_File_ImageTest extends CMTest_TestCase {

	public function testConstruct() {
		$path = DIR_TEST_DATA . 'img/test.jpg';
		$image = new CM_File_Image($path);

		$this->assertEquals($path, $image->getPath());
		$this->assertEquals('image/jpeg', $image->getMimeType());
	}

	public function testConstructCorruptContent() {
		$path = DIR_TEST_DATA . 'img/corrupt-content.jpg';
		$image = new CM_File_Image($path);

		$this->assertEquals('image/jpeg', $image->getMimeType());
	}

	public function testConstructJpgNoExtension() {
		$path = DIR_TEST_DATA . 'img/jpg-no-extension';
		$image = new CM_File_Image($path);

		$this->assertEquals('image/jpeg', $image->getMimeType());
	}

	public function testConstructUnsupportedFormat() {
		$path = DIR_TEST_DATA . 'img/test.tiff';

		try {
			new CM_File_Image($path);
			$this->fail('Could instantiate a image with an unsupported format');
		} catch (CM_Exception $e) {
			$this->assertSame('Unsupported format `TIFF`.', $e->getMessage());
		}
	}

	/**
	 * @expectedException CM_Exception
	 * @expectedExceptionMessage Cannot load Imagick instance
	 */
	public function testConstructCorruptHeader() {
		$path = DIR_TEST_DATA . 'img/corrupt-header.jpg';
		$image = new CM_File_Image($path);
	}

	/**
	 * @expectedException CM_Exception
	 * @expectedExceptionMessage Cannot load Imagick instance
	 */
	public function testConstructorEmptyFile() {
		$path = DIR_TEST_DATA . 'img/empty.jpg';
		$image = new CM_File_Image($path);
	}

	/**
	 * @expectedException CM_Exception
	 * @expectedExceptionMessage Cannot load Imagick instance
	 */
	public function testConstructNoImage() {
		$path = DIR_TEST_DATA . 'test.jpg.zip';
		$image = new CM_File_Image($path);
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
		$pathNew = DIR_TMP . uniqid();
		$image = new CM_File_Image($path);

		$image->rotate(90, $pathNew);
		$imageNew = new CM_File_Image($pathNew);
		$this->assertSame($image->getHeight(), $imageNew->getWidth());
		$this->assertSame($image->getWidth(), $imageNew->getHeight());
	}

	public function testRotateAnimatedGif() {
		$path = DIR_TEST_DATA . 'img/animated.gif';
		$pathNew = DIR_TMP . uniqid();
		$image = new CM_File_Image($path);

		$image->rotate(90, $pathNew, CM_File_Image::FORMAT_GIF);
		$imageNew = new CM_File_Image($pathNew);
		$this->assertSame($image->getHeight(), $imageNew->getWidth());
		$this->assertSame($image->getWidth(), $imageNew->getHeight());
		$this->assertEquals(148987, $imageNew->getSize(), '', 5000);
	}

	public function testGetFormat() {
		$pathList = array(
			DIR_TEST_DATA . 'img/test.jpg'            => CM_File_Image::FORMAT_JPEG,
			DIR_TEST_DATA . 'img/test.gif'            => CM_File_Image::FORMAT_GIF,
			DIR_TEST_DATA . 'img/test.png'            => CM_File_Image::FORMAT_PNG,
			DIR_TEST_DATA . 'img/jpg-no-extension'    => CM_File_Image::FORMAT_JPEG,
			DIR_TEST_DATA . 'img/corrupt-content.jpg' => CM_File_Image::FORMAT_JPEG,
		);

		foreach ($pathList as $path => $format) {
			$image = new CM_File_Image($path);
			$this->assertSame($format, $image->getFormat());
		}
	}

	/**
	 * @expectedException CM_Exception_Invalid
	 * @expectedExceptionMessage Unsupported format
	 */
	public function testGetFormatUnsupportedFormat() {
		$path = DIR_TEST_DATA . 'img/test.tiff';
		$image = new CM_File_Image($path);
		$image->getFormat();
	}

	public function testIsAnimated() {
		$imageJpeg = new CM_File_Image(DIR_TEST_DATA . 'img/test.jpg');
		$this->assertFalse($imageJpeg->isAnimated());

		$imageAnimatedGif = new CM_File_Image(DIR_TEST_DATA . 'img/animated.gif');
		$this->assertTrue($imageAnimatedGif->isAnimated());
	}

	public function testIsAnimatedConvertingToNonAnimated() {
		$file = new CM_File(DIR_TEST_DATA . 'img/animated.gif');
		$path = DIR_TMP . uniqid();
		$file->copy($path);
		$image = new CM_File_Image($path);
		$this->assertTrue($image->isAnimated());

		$image->convert(CM_File_Image::FORMAT_GIF, $path);
		$this->assertTrue($image->isAnimated());

		$image->convert(CM_File_Image::FORMAT_JPEG, $path);
		$this->assertFalse($image->isAnimated());
	}

	public function testGetWidthHeight() {
		$pathList = array(
			DIR_TEST_DATA . 'img/test.jpg',
			DIR_TEST_DATA . 'img/test.gif',
			DIR_TEST_DATA . 'img/test.png',
		);
		foreach ($pathList as $path) {
			$image = new CM_File_Image($path);
			$this->assertSame(363, $image->getWidth());
			$this->assertSame(214, $image->getHeight());
		}
	}

	public function testGetWidthHeightAnimatedGif() {
		$image = new CM_File_Image(DIR_TEST_DATA . 'img/animated.gif');
		$this->assertSame(180, $image->getWidth());
		$this->assertSame(135, $image->getHeight());
	}

	/**
	 * @expectedException CM_Exception_Invalid
	 * @expectedExceptionMessage Invalid compression quality
	 */
	public function testSetCompressionQualityInvalid() {
		$path = DIR_TEST_DATA . 'img/test.jpg';
		$image = new CM_File_Image($path);
		$image->setCompressionQuality(-188);
	}

	public function testConvert() {
		$path = DIR_TEST_DATA . 'img/test.jpg';
		$pathNew = DIR_TMP . uniqid();
		$image = new CM_File_Image($path);

		$image->convert(CM_File_Image::FORMAT_GIF, $pathNew);
		$imageNew = new CM_File_Image($pathNew);
		$this->assertSame($image->getWidth(), $imageNew->getWidth());
		$this->assertSame($image->getHeight(), $imageNew->getHeight());
	}

	public function testConvertAllFormats() {
		$formatList = array(
			CM_File_Image::FORMAT_JPEG,
			CM_File_Image::FORMAT_GIF,
			CM_File_Image::FORMAT_PNG,
		);
		$pathList = array(
			DIR_TEST_DATA . 'img/test.jpg',
			DIR_TEST_DATA . 'img/test.gif',
			DIR_TEST_DATA . 'img/test.png',
		);
		foreach ($pathList as $path) {
			foreach ($formatList as $format) {
				$pathNew = DIR_TMP . uniqid();
				$image = new CM_File_Image($path);

				$image->convert($format, $pathNew);
				$imageNew = new CM_File_Image($pathNew);
				$this->assertSame($image->getWidth(), $imageNew->getWidth());
				$this->assertSame($image->getHeight(), $imageNew->getHeight());
				$this->assertGreaterThan(0, $imageNew->getSize());
			}
		}
	}

	public function testConvertJpegCompression() {
		$qualityList = array(
			1   => 4056,
			30  => 6439,
			60  => 8011,
			90  => 14865,
			95  => 18854,
			100 => 37649,
		);
		$path = DIR_TEST_DATA . 'img/test.gif';
		foreach ($qualityList as $quality => $expectedFileSize) {
			$pathNew = DIR_TMP . uniqid();
			$image = new CM_File_Image($path);

			$image->setCompressionQuality($quality);
			$image->convert(CM_File_Image::FORMAT_JPEG, $pathNew);
			$imageNew = new CM_File_Image($pathNew);
			$fileSizeDelta = $expectedFileSize * 0.05;
			$this->assertEquals($expectedFileSize, $imageNew->getSize(), 'File size mismatch for quality `' . $quality . '`', $fileSizeDelta);
		}
	}

	public function testConvertSamePath() {
		$file = new CM_File(DIR_TEST_DATA . 'img/test.jpg');
		$path = DIR_TMP . uniqid();
		$file->copy($path);
		$image = new CM_File_Image($path);
		$imageWidth = $image->getWidth();
		$imageHeight = $image->getHeight();
		$imageHash = $image->getHash();
		$image->convert(CM_File_Image::FORMAT_GIF, $path);

		$imageNew = new CM_File_Image($path);
		$this->assertSame($imageWidth, $imageNew->getWidth());
		$this->assertSame($imageHeight, $imageNew->getHeight());
		$this->assertNotSame($imageHash, $imageNew->getHash());
	}

	public function testConvertSamePathSameFormat() {
		$file = new CM_File(DIR_TEST_DATA . 'img/test.jpg');
		$path = DIR_TMP . uniqid();
		$file->copy($path);
		$image = new CM_File_Image($path);
		$imageHash = $image->getHash();
		$image->convert(CM_File_Image::FORMAT_JPEG, $path);

		$imageNew = new CM_File_Image($path);
		$this->assertSame($imageHash, $imageNew->getHash());
	}

	public function testResize() {
		$path = DIR_TEST_DATA . 'img/test.jpg';
		$pathNew = DIR_TMP . uniqid();
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
		$pathNew = DIR_TMP . uniqid();
		$image = new CM_File_Image($path);

		$image->resize(50, 50, true, $pathNew);
		$imageNew = new CM_File_Image($pathNew);
		$this->assertSame(50, $imageNew->getWidth());
		$this->assertSame(50, $imageNew->getHeight());
	}

	public function testResizeSquareNoBlowup() {
		$path = DIR_TEST_DATA . 'img/test.jpg';
		$pathNew = DIR_TMP . uniqid();
		$image = new CM_File_Image($path);

		$image->resize(5000, 5000, true, $pathNew);
		$imageNew = new CM_File_Image($pathNew);
		$sizeExpected = min($image->getWidth(), $image->getHeight());
		$this->assertSame($sizeExpected, $imageNew->getWidth());
		$this->assertSame($sizeExpected, $imageNew->getHeight());
	}

	public function testResizeFileSize() {
		$path = DIR_TEST_DATA . 'img/test.jpg';
		$pathNew = DIR_TMP . uniqid();
		$image = new CM_File_Image($path);
		$this->assertEquals(17661, $image->getSize(), '', 300);

		$image->setCompressionQuality(90);
		$image->resize(100, 100, null, $pathNew);
		$imageNew = new CM_File_Image($pathNew);
		$this->assertEquals(4620, $imageNew->getSize(), '', 300);
	}

	public function testResizeAnimatedGif() {
		$path = DIR_TEST_DATA . 'img/animated.gif';
		$pathNew = DIR_TMP . uniqid();
		$image = new CM_File_Image($path);

		$image->resize(50, 50, true, $pathNew, CM_File_Image::FORMAT_GIF);
		$imageNew = new CM_File_Image($pathNew);
		$this->assertSame('image/gif', $imageNew->getMimeType());
		$this->assertSame(50, $imageNew->getWidth());
		$this->assertSame(50, $imageNew->getHeight());
		$this->assertEquals(25697, $imageNew->getSize(), '', 2000);
	}

	public function testResizeAnimatedGifToJpeg() {
		$path = DIR_TEST_DATA . 'img/animated.gif';
		$pathNew = DIR_TMP . uniqid();
		$image = new CM_File_Image($path);

		$image->resize(50, 50, true, $pathNew, CM_File_Image::FORMAT_JPEG);
		$imageNew = new CM_File_Image($pathNew);
		$this->assertSame('image/jpeg', $imageNew->getMimeType());
		$this->assertSame(50, $imageNew->getWidth());
		$this->assertSame(50, $imageNew->getHeight());
		$this->assertEquals(1682, $imageNew->getSize(), '', 100);
	}

	public function testGetExtensionByFormat() {
		$this->assertSame('jpg', CM_File_Image::getExtensionByFormat(CM_File_Image::FORMAT_JPEG));
		$this->assertSame('gif', CM_File_Image::getExtensionByFormat(CM_File_Image::FORMAT_GIF));
		$this->assertSame('png', CM_File_Image::getExtensionByFormat(CM_File_Image::FORMAT_PNG));
	}

	/**
	 * @expectedException CM_Exception_Invalid
	 * @expectedExceptionMessage Invalid format
	 */
	public function testGetExtensionByFormatInvalid() {
		CM_File_Image::getExtensionByFormat(-999);
	}

	public function testCreate() {
		$rawImageData = file_get_contents(DIR_TEST_DATA . 'img/test.jpg', 'r');
		$image = CM_File_Image::create(DIR_TMP . 'test.jpg', $rawImageData);
		$this->assertEquals('image/jpeg', $image->getMimeType());
		$image->delete();
	}

	/**
	 * @expectedException CM_Exception
	 */
	public function testCreateFailure() {
		$rawImageData = 'false image data';
		CM_File_Image::create(DIR_TMP . 'test.jpg', $rawImageData);
		$this->fail('Could create image from false data');
	}
}
