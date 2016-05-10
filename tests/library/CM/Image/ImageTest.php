<?php

class CM_Image_ImageTest extends CMTest_TestCase {

    public function testConstructor() {
        $exception = $this->catchException(function () {
            new CM_Image_Image('');
        });
        $this->assertInstanceOf('CM_Exception', $exception);
        $this->assertRegExp('/^Cannot load image blob /', $exception->getMessage());

        $exception = $this->catchException(function () {
            $imageFile = new CM_File(DIR_TEST_DATA . 'img/corrupt-header.jpg');
            new CM_Image_Image($imageFile->read());
        });
        $this->assertInstanceOf('CM_Exception', $exception);
        $this->assertRegExp('/^Cannot load image blob /', $exception->getMessage());

        $exception = $this->catchException(function () {
            new CM_Image_Image('foobar');
        });
        $this->assertInstanceOf('CM_Exception', $exception);
        $this->assertRegExp('/^Cannot load image blob /', $exception->getMessage());

        $exception = $this->catchException(function () {
            new CM_Image_Image(new Imagick());
        });
        $this->assertInstanceOf('CM_Exception', $exception);
        $this->assertRegExp('/^\$imagick does not contain any image /', $exception->getMessage());

        $imagick = new Imagick();
        $imagick->newPseudoImage(100, 100, 'canvas:black');
        $image = new CM_Image_Image($imagick);
        $this->assertInstanceOf('CM_Image_Image', $image);
        $this->assertSame(100, $image->getHeight());
        $this->assertSame(100, $image->getWidth());
    }

    public function testCrop() {
        $imageFile = new CM_File(DIR_TEST_DATA . 'img/test.jpg');
        $imageOriginal = new CM_Image_Image($imageFile->read());

        // centered cutout
        $image = $imageOriginal->getClone()->crop(100, 50);
        $this->assertSame(100, $image->getWidth());
        $this->assertSame(50, $image->getHeight());

        // cutout that exceeds the bottom right corner
        $image = $imageOriginal->getClone()->crop(100, 100, $imageOriginal->getWidth() - 60, $imageOriginal->getHeight() - 30);
        $this->assertSame(60, $image->getWidth());
        $this->assertSame(30, $image->getHeight());

        // cutout that exceeds the top left corner
        $image = $imageOriginal->getClone()->crop(100, 100, -30, -40);
        $this->assertSame(70, $image->getWidth());
        $this->assertSame(60, $image->getHeight());
    }

    public function testValidate() {
        $imageFile = new CM_File(DIR_TEST_DATA . 'img/test.jpg');
        $image = new CM_Image_Image($imageFile->read());
        $image->validate();
        $this->assertTrue(true);
    }

    public function testValidateCorruptContent() {
        $imageFile = new CM_File(DIR_TEST_DATA . 'img/corrupt-content.jpg');
        $image = new CM_Image_Image($imageFile->read());
        $image->validate();
        $this->assertTrue(true);
    }

    /**
     * @expectedException CM_Exception
     * @expectedExceptionMessage Unsupported format
     */
    public function testValidateUnsupportedFormat() {
        $imageFile = new CM_File(DIR_TEST_DATA . 'img/test.tiff');
        $image = new CM_Image_Image($imageFile->read());
        $image->validate();
    }

    public function testRotate() {
        $imageFileOriginal = new CM_File(DIR_TEST_DATA . 'img/test.jpg');
        $imageOriginal = new CM_Image_Image($imageFileOriginal->read());
        $this->assertNotSame($imageOriginal->getWidth(), $imageOriginal->getHeight());

        $image = $imageOriginal->getClone()->rotate(90);

        $this->assertSame($imageOriginal->getHeight(), $image->getWidth());
        $this->assertSame($imageOriginal->getWidth(), $image->getHeight());
    }

    public function testRotateAnimatedGif() {
        $imageFileOriginal = new CM_File(DIR_TEST_DATA . 'img/animated.gif');
        $imageOriginal = new CM_Image_Image($imageFileOriginal->read());
        $this->assertNotSame($imageOriginal->getWidth(), $imageOriginal->getHeight());

        $image = $imageOriginal->getClone()->rotate(90);

        $this->assertSame($imageOriginal->getHeight(), $image->getWidth());
        $this->assertSame($imageOriginal->getWidth(), $image->getHeight());
        $imageFile = CM_File::createTmp(null, $image->getBlob());
        $this->assertEquals(148987, $imageFile->getSize(), '', 5000);
    }

    public function testclearAndApplyExifRotation() {
        $imageFileOriginal = new CM_File(DIR_TEST_DATA . 'img/test-rotated.jpg');
        $imageOriginal = new CM_Image_Image($imageFileOriginal->read());
        $this->assertSame(6, $this->_getImagickObject($imageOriginal)->getImageOrientation());
        $image = $imageOriginal->getClone()->clearAndApplyExifRotation();
        $image = new CM_Image_Image($image->getBlob());
        $this->assertSame(6, $this->_getImagickObject($imageOriginal)->getImageOrientation());
        $this->assertSame($imageOriginal->getHeight(), $image->getWidth());
        $this->assertSame($imageOriginal->getWidth(), $image->getHeight());
    }

    public function testStripProfileData() {
        $imageOriginal = new CM_File(DIR_TEST_DATA . 'img/test-rotated.jpg');
        $image = new CM_Image_Image($imageOriginal->read());
        $this->assertSame(6, $this->_getImagickObject($image)->getImageOrientation());
        $image->stripProfileData();
        $image = new CM_Image_Image($image->getBlob());
        $this->assertSame(0, $this->_getImagickObject($image)->getImageOrientation());
    }

    public function testGetFormat() {
        $pathList = array(
            DIR_TEST_DATA . 'img/test.jpg'            => CM_Image_Image::FORMAT_JPEG,
            DIR_TEST_DATA . 'img/test.gif'            => CM_Image_Image::FORMAT_GIF,
            DIR_TEST_DATA . 'img/test.png'            => CM_Image_Image::FORMAT_PNG,
            DIR_TEST_DATA . 'img/corrupt-content.jpg' => CM_Image_Image::FORMAT_JPEG,
        );

        foreach ($pathList as $path => $format) {
            $imageFile = new CM_File($path);
            $image = new CM_Image_Image($imageFile->read());
            $this->assertSame($format, $image->getFormat());
        }
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage Unsupported format
     */
    public function testGetFormatUnsupportedFormat() {
        $imageFile = new CM_File(DIR_TEST_DATA . 'img/test.tiff');
        $image = new CM_Image_Image($imageFile->read());
        $image->getFormat();
    }

    public function testIsAnimated() {
        $imageFile = new CM_File(DIR_TEST_DATA . 'img/test.jpg');
        $imageJpeg = new CM_Image_Image($imageFile->read());
        $this->assertFalse($imageJpeg->isAnimated());

        $imageFile = new CM_File(DIR_TEST_DATA . 'img/animated.gif');
        $imageAnimatedGif = new CM_Image_Image($imageFile->read());
        $this->assertTrue($imageAnimatedGif->isAnimated());
    }

    public function testIsAnimatedSetFormatToNonAnimated() {
        $imageFile = new CM_File(DIR_TEST_DATA . 'img/animated.gif');
        $image = new CM_Image_Image($imageFile->read());
        $this->assertTrue($image->isAnimated());

        $image->setFormat(CM_Image_Image::FORMAT_GIF);
        $this->assertTrue($image->isAnimated());

        $image->setFormat(CM_Image_Image::FORMAT_JPEG);
        $this->assertFalse($image->isAnimated());
    }

    public function testGetWidthHeight() {
        /** @var CM_File[] $fileList */
        $fileList = array(
            new CM_File(DIR_TEST_DATA . 'img/test.jpg'),
            new CM_File(DIR_TEST_DATA . 'img/test.gif'),
            new CM_File(DIR_TEST_DATA . 'img/test.png'),
        );
        foreach ($fileList as $file) {
            $image = new CM_Image_Image($file->read());
            $this->assertSame(363, $image->getWidth());
            $this->assertSame(214, $image->getHeight());
        }
    }

    public function testGetWidthHeightAnimatedGif() {
        $imageFile = new CM_File(DIR_TEST_DATA . 'img/animated.gif');
        $image = new CM_Image_Image($imageFile->read());
        $this->assertSame(180, $image->getWidth());
        $this->assertSame(135, $image->getHeight());
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage Invalid compression quality
     */
    public function testSetCompressionQualityInvalid() {
        $imageFile = new CM_File(DIR_TEST_DATA . 'img/test.jpg');
        $image = new CM_Image_Image($imageFile->read());
        $image->setCompressionQuality(-188);
    }

    public function testSetFormat() {
        $imageFile = new CM_File(DIR_TEST_DATA . 'img/test.jpg');
        $image = new CM_Image_Image($imageFile->read());
        $this->assertSame(CM_Image_Image::FORMAT_JPEG, $image->getFormat());
        $image->setFormat(CM_Image_Image::FORMAT_GIF);
        $this->assertSame(CM_Image_Image::FORMAT_GIF, $image->getFormat());
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
        $imageFileOriginal = new CM_File($path);
        foreach ($qualityList as $quality => $expectedFileSize) {
            $image = new CM_Image_Image($imageFileOriginal->read());

            $image->setFormat(CM_Image_Image::FORMAT_JPEG)->setCompressionQuality($quality);

            $imageFile = CM_File::createTmp(null, $image->getBlob());
            $fileSizeDelta = $expectedFileSize * 0.05;
            $this->assertEquals($expectedFileSize, $imageFile->getSize(), 'File size mismatch for quality `' . $quality . '`', $fileSizeDelta);
        }
    }

    public function testResize() {
        $image = $this->mockClass('CM_Image_Image')->newInstanceWithoutConstructor();
        $image->mockMethod('getWidth')->set(250);
        $image->mockMethod('getHeight')->set(150);

        $resizeSpecificMethod = $image->mockMethod('resizeSpecific')
            ->set(function ($width, $height) {
                $this->assertSame(250, $width);
                $this->assertSame(150, $height);
            });
        /** @var CM_Image_Image $image */
        $image->resize(500, 400, false);
        $this->assertSame(1, $resizeSpecificMethod->getCallCount());
    }

    public function testResizeNoInvalidDimensions() {
        $imageFileOriginal = new CM_File(DIR_TEST_DATA . 'img/test.jpg');
        $image = new CM_Image_Image($imageFileOriginal->read());
        $width = $image->getWidth();
        $height = $image->getHeight();
        $widthResize = ($width > $height) ? 1 : (int) round($width / $height);
        $heightResize = ($height > $width) ? 1 : (int) round($height / $width);
        $image->resize($widthResize, $heightResize);
        $this->assertSame($widthResize, $image->getWidth());
        $this->assertSame($heightResize, $image->getHeight());
    }

    public function testResizeSquare() {
        // width > height
        $image = $this->mockClass('CM_Image_Image')->newInstanceWithoutConstructor();
        $image->mockMethod('getWidth')->set(250);
        $image->mockMethod('getHeight')->set(150);

        $resizeSpecificMethod = $image->mockMethod('resizeSpecific')
            ->set(function ($width, $height) {
                $this->assertSame(150, $width);
                $this->assertSame(150, $height);
            });
        $cropMethod = $image->mockMethod('crop')
            ->set(function ($width, $height, $offsetX = null, $offsetY = null) {
                $this->assertSame(150, $width);
                $this->assertSame(150, $height);
                $this->assertSame(null, $offsetX);
                $this->assertSame(null, $offsetY);
            });
        /** @var CM_Image_Image $image */
        $image->resize(500, 400, true);
        $this->assertSame(1, $resizeSpecificMethod->getCallCount());
        $this->assertSame(1, $cropMethod->getCallCount());

        // height > width
        /** @var \Mocka\ClassMock $image */
        $image = $this->mockClass('CM_Image_Image')->newInstanceWithoutConstructor();
        $image->mockMethod('getWidth')->set(150);
        $image->mockMethod('getHeight')->set(250);

        $resizeSpecificMethod = $image->mockMethod('resizeSpecific')
            ->set(function ($width, $height) {
                $this->assertSame(150, $width);
                $this->assertSame(150, $height);
            });
        $cropMethod = $image->mockMethod('crop')
            ->set(function ($width, $height, $offsetX = null, $offsetY = null) {
                $this->assertSame(150, $width);
                $this->assertSame(150, $height);
                $this->assertSame(null, $offsetX);
                $this->assertSame(null, $offsetY);
            });
        /** @var CM_Image_Image $image */
        $image->resize(500, 400, true);
        $this->assertSame(1, $resizeSpecificMethod->getCallCount());
        $this->assertSame(1, $cropMethod->getCallCount());

        // height == width
        /** @var \Mocka\ClassMock $image */
        $image = $this->mockClass('CM_Image_Image')->newInstanceWithoutConstructor();
        $image->mockMethod('getWidth')->set(150);
        $image->mockMethod('getHeight')->set(150);

        $resizeSpecificMethod = $image->mockMethod('resizeSpecific')
            ->set(function ($width, $height) {
                $this->assertSame(150, $width);
                $this->assertSame(150, $height);
            });
        $cropMethod = $image->mockMethod('crop')
            ->set(function ($width, $height, $offsetX = null, $offsetY = null) {
            });
        /** @var CM_Image_Image $image */
        $image->resize(500, 400, true);
        $this->assertSame(1, $resizeSpecificMethod->getCallCount());
        $this->assertSame(0, $cropMethod->getCallCount());
    }

    public function testResizeSquareNoBlowup() {
        $imageFileOriginal = new CM_File(DIR_TEST_DATA . 'img/test.jpg');
        $image = new CM_Image_Image($imageFileOriginal->read());

        $sizeExpected = min($image->getWidth(), $image->getHeight());
        $image->resize(5000, 5000, true);
        $this->assertSame($sizeExpected, $image->getWidth());
        $this->assertSame($sizeExpected, $image->getHeight());
    }

    public function testResizeFileSize() {
        $imageFileOriginal = new CM_File(DIR_TEST_DATA . 'img/test.jpg');
        $image = new CM_Image_Image($imageFileOriginal->read());
        $this->assertEquals(17661, $imageFileOriginal->getSize(), '', 300);

        $image->setCompressionQuality(90)->resize(100, 100);
        $imageFile = CM_File::createTmp(null, $image->getBlob());
        $this->assertEquals(4620, $imageFile->getSize(), '', 300);
    }

    public function testResizeAnimatedGif() {
        $imageFileOriginal = new CM_File(DIR_TEST_DATA . 'img/animated.gif');
        $image = new CM_Image_Image($imageFileOriginal->read());

        $image->resize(50, 50, true);

        $imageFile = CM_File::createTmp(null, $image->getBlob());
        $this->assertSame('image/gif', $imageFile->getMimeType());
        $this->assertSame(50, $image->getWidth());
        $this->assertSame(50, $image->getHeight());
        $this->assertEquals(25697, $imageFile->getSize(), '', 2000);
    }

    public function testResizeAnimatedGifToJpeg() {
        $imageFileOriginal = new CM_File(DIR_TEST_DATA . 'img/animated.gif');
        $image = new CM_Image_Image($imageFileOriginal->read());

        $image->setFormat(CM_Image_Image::FORMAT_JPEG)->resize(50, 50, true);
        $imageFile = CM_File::createTmp(null, $image->getBlob());
        $this->assertSame('image/jpeg', $imageFile->getMimeType());
        $this->assertSame(50, $image->getWidth());
        $this->assertSame(50, $image->getHeight());
        $this->assertEquals(1682, $imageFile->getSize(), '', 100);
    }

    public function testResizeSpecific() {
        $imageFileOriginal = new CM_File(DIR_TEST_DATA . 'img/test.jpg');
        $image = new CM_Image_Image($imageFileOriginal->read());

        $image->resizeSpecific(50, 50);
        $this->assertSame(50, $image->getWidth());
        $this->assertSame(50, $image->getHeight());
    }

    public function testResizeSpecificKeepExif() {
        $imageFileOriginal = new CM_File(DIR_TEST_DATA . 'img/test-rotated.jpg');
        $image = new CM_Image_Image($imageFileOriginal->read());
        $image->resize($image->getWidth(), $image->getHeight());
        $imageFile = CM_File::createTmp(null, $image->getBlob());
        $newImage = new CM_Image_Image($imageFile->read());
        $this->assertSame(6, $this->_getImagickObject($newImage)->getImageOrientation());
    }

    public function testGetExtensionByFormat() {
        $this->assertSame('jpg', CM_Image_Image::getExtensionByFormat(CM_Image_Image::FORMAT_JPEG));
        $this->assertSame('gif', CM_Image_Image::getExtensionByFormat(CM_Image_Image::FORMAT_GIF));
        $this->assertSame('png', CM_Image_Image::getExtensionByFormat(CM_Image_Image::FORMAT_PNG));
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage Invalid format
     */
    public function testGetExtensionByFormatInvalid() {
        CM_Image_Image::getExtensionByFormat(-999);
    }

    public function testCalculateDimensions() {
        $dimensions = CM_Image_Image::calculateDimensions(2000, 1600, 500, 600, false);

        $this->assertEquals(500, $dimensions['width']);
        $this->assertEquals(400, $dimensions['height']);
    }

    public function testCalculateDimensionsSquare() {
        $dimensions = CM_Image_Image::calculateDimensions(2000, 1600, 500, 500, true);

        $this->assertEquals(500, $dimensions['width']);
        $this->assertEquals(500, $dimensions['height']);
    }

    public function testCalculateDimensionsLower() {
        $dimensions = CM_Image_Image::calculateDimensions(100, 200, 1000, 500, false);

        $this->assertEquals(100, $dimensions['width']);
        $this->assertEquals(200, $dimensions['height']);
    }

    /**
     * @param CM_Image_Image $image
     * @return Imagick
     */
    private function _getImagickObject(CM_Image_Image $image) {
        $reflectionProperty = new ReflectionProperty('CM_Image_Image', '_imagick');
        $reflectionProperty->setAccessible(true);
        return $reflectionProperty->getValue($image);
    }
}
