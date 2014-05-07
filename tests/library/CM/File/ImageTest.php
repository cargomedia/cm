<?php

class CM_File_ImageTest extends CMTest_TestCase {

    public function testValidateImage() {
        $path = DIR_TEST_DATA . 'img/test.jpg';
        $image = new CM_File_Image($path);
        $image->validateImage();
        $this->assertTrue(true);
    }

    public function testValidateImageCorruptContent() {
        $path = DIR_TEST_DATA . 'img/corrupt-content.jpg';
        $image = new CM_File_Image($path);
        $image->validateImage();
        $this->assertTrue(true);
    }

    public function testValidateImageJpgNoExtension() {
        $path = DIR_TEST_DATA . 'img/jpg-no-extension';
        $image = new CM_File_Image($path);
        $image->validateImage();
        $this->assertTrue(true);
    }

    /**
     * @expectedException CM_Exception
     * @expectedExceptionMessage Unsupported format
     */
    public function testValidateImageUnsupportedFormat() {
        $path = DIR_TEST_DATA . 'img/test.tiff';
        $file = new CM_File_Image($path);
        $file->validateImage();
    }

    /**
     * @expectedException CM_Exception
     * @expectedExceptionMessage Cannot load Imagick instance
     */
    public function testValidateImageCorruptHeader() {
        $path = DIR_TEST_DATA . 'img/corrupt-header.jpg';
        $image = new CM_File_Image($path);
        $image->validateImage();
    }

    /**
     * @expectedException CM_Exception
     * @expectedExceptionMessage Cannot load Imagick instance
     */
    public function testValidateImageEmptyFile() {
        $path = DIR_TEST_DATA . 'img/empty.jpg';
        $image = new CM_File_Image($path);
        $image->validateImage();
    }

    /**
     * @expectedException CM_Exception
     * @expectedExceptionMessage Cannot load Imagick instance
     */
    public function testValidateImageNoImage() {
        $path = DIR_TEST_DATA . 'test.jpg.zip';
        $image = new CM_File_Image($path);
        $image->validateImage();
    }

    public function testDimensions() {
        $path = DIR_TEST_DATA . 'img/test.jpg';
        list($width, $height) = getimagesize($path);

        $image = new CM_File_Image($path);
        $this->assertSame($width, $image->getWidth());
        $this->assertSame($height, $image->getHeight());
    }

    public function testRotate() {
        $imageOriginal = new CM_File_Image(DIR_TEST_DATA . 'img/test.jpg');
        $image = CM_File_Image::createTmp(null, $imageOriginal->read());
        $this->assertNotSame($imageOriginal->getWidth(), $imageOriginal->getHeight());

        $image->rotate(90);
        $this->assertSame($imageOriginal->getHeight(), $image->getWidth());
        $this->assertSame($imageOriginal->getWidth(), $image->getHeight());
    }

    public function testRotateAnimatedGif() {
        $imageOriginal = new CM_File_Image(DIR_TEST_DATA . 'img/animated.gif');
        $image = CM_File_Image::createTmp(null, $imageOriginal->read());
        $this->assertNotSame($imageOriginal->getWidth(), $imageOriginal->getHeight());

        $image->rotate(90, CM_File_Image::FORMAT_GIF);
        $this->assertSame($imageOriginal->getHeight(), $image->getWidth());
        $this->assertSame($imageOriginal->getWidth(), $image->getHeight());
        $this->assertEquals(148987, $image->getSize(), '', 5000);
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
        $image = CM_File_Image::createTmp('gif', $file->read());
        $this->assertTrue($image->isAnimated());

        $image->convert(CM_File_Image::FORMAT_GIF);
        $this->assertTrue($image->isAnimated());

        $image->convert(CM_File_Image::FORMAT_JPEG);
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
        $imageOriginal = new CM_File_Image(DIR_TEST_DATA . 'img/test.jpg');
        $image = CM_File_Image::createTmp(null, $imageOriginal->read());

        $imageNew = CM_File_Image::createTmp();
        $image->convert(CM_File_Image::FORMAT_GIF, $imageNew);
        $this->assertSame($image->getWidth(), $imageNew->getWidth());
        $this->assertSame($image->getHeight(), $imageNew->getHeight());
        $this->assertNotSame($image->read(), $imageNew->read());
    }

    public function testConvertSameFile() {
        $imageOriginal = new CM_File_Image(DIR_TEST_DATA . 'img/test.jpg');
        $image = CM_File_Image::createTmp(null, $imageOriginal->read());

        $image->convert(CM_File_Image::FORMAT_GIF);
        $this->assertSame($imageOriginal->getWidth(), $image->getWidth());
        $this->assertSame($imageOriginal->getHeight(), $image->getHeight());
        $this->assertNotSame($imageOriginal->read(), $image->read());
    }

    public function testConvertSameFileSameFormat() {
        $imageOriginal = new CM_File_Image(DIR_TEST_DATA . 'img/test.jpg');
        $image = CM_File_Image::createTmp(null, $imageOriginal->read());

        $image->convert(CM_File_Image::FORMAT_JPEG);
        $this->assertSame($imageOriginal->getWidth(), $image->getWidth());
        $this->assertSame($imageOriginal->getHeight(), $image->getHeight());
        $this->assertSame($imageOriginal->read(), $image->read());
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
                $imageOriginal = new CM_File_Image($path);
                $image = CM_File_Image::createTmp(null, $imageOriginal->read());

                $image->convert($format);
                $this->assertSame($imageOriginal->getWidth(), $image->getWidth());
                $this->assertSame($imageOriginal->getHeight(), $image->getHeight());
                $this->assertGreaterThan(0, $image->getSize());
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
            $imageOriginal = new CM_File_Image($path);
            $image = CM_File_Image::createTmp(null, $imageOriginal->read());

            $image->setCompressionQuality($quality);
            $image->convert(CM_File_Image::FORMAT_JPEG);
            $fileSizeDelta = $expectedFileSize * 0.05;
            $this->assertEquals($expectedFileSize, $image->getSize(), 'File size mismatch for quality `' . $quality . '`', $fileSizeDelta);
        }
    }

    public function testResize() {
        $imageOriginal = new CM_File_Image(DIR_TEST_DATA . 'img/test.jpg');
        $image = CM_File_Image::createTmp(null, $imageOriginal->read());

        $image->resize(50, 50, false);
        $scale = ($image->getWidth() / 50 > $image->getHeight() / 50) ? 50 / $image->getWidth() : 50 / $image->getHeight();
        $widthExpected = (int) ($image->getWidth() * $scale);
        $heightExpected = (int) ($image->getHeight() * $scale);
        $this->assertSame($widthExpected, $image->getWidth());
        $this->assertSame($heightExpected, $image->getHeight());
    }

    public function testResizeSquare() {
        $imageOriginal = new CM_File_Image(DIR_TEST_DATA . 'img/test.jpg');
        $image = CM_File_Image::createTmp(null, $imageOriginal->read());

        $image->resize(50, 50, true);
        $this->assertSame(50, $image->getWidth());
        $this->assertSame(50, $image->getHeight());
    }

    public function testResizeSquareNoBlowup() {
        $imageOriginal = new CM_File_Image(DIR_TEST_DATA . 'img/test.jpg');
        $image = CM_File_Image::createTmp(null, $imageOriginal->read());

        $sizeExpected = min($image->getWidth(), $image->getHeight());
        $image->resize(5000, 5000, true);
        $this->assertSame($sizeExpected, $image->getWidth());
        $this->assertSame($sizeExpected, $image->getHeight());
    }

    public function testResizeFileSize() {
        $imageOriginal = new CM_File_Image(DIR_TEST_DATA . 'img/test.jpg');
        $image = CM_File_Image::createTmp(null, $imageOriginal->read());
        $this->assertEquals(17661, $image->getSize(), '', 300);

        $image->setCompressionQuality(90);
        $image->resize(100, 100);
        $this->assertEquals(4620, $image->getSize(), '', 300);
    }

    public function testResizeAnimatedGif() {
        $imageOriginal = new CM_File_Image(DIR_TEST_DATA . 'img/animated.gif');
        $image = CM_File_Image::createTmp(null, $imageOriginal->read());

        $image->resize(50, 50, true, CM_File_Image::FORMAT_GIF);
        $this->assertSame('image/gif', $image->getMimeType());
        $this->assertSame(50, $image->getWidth());
        $this->assertSame(50, $image->getHeight());
        $this->assertEquals(25697, $image->getSize(), '', 2000);
    }

    public function testResizeAnimatedGifToJpeg() {
        $imageOriginal = new CM_File_Image(DIR_TEST_DATA . 'img/animated.gif');
        $image = CM_File_Image::createTmp(null, $imageOriginal->read());

        $image->resize(50, 50, true, CM_File_Image::FORMAT_JPEG);
        $this->assertSame('image/jpeg', $image->getMimeType());
        $this->assertSame(50, $image->getWidth());
        $this->assertSame(50, $image->getHeight());
        $this->assertEquals(1682, $image->getSize(), '', 100);
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
        $image = CM_File_Image::create(CM_Bootloader::getInstance()->getDirTmp() . 'test.jpg', $rawImageData);
        $this->assertEquals('image/jpeg', $image->getMimeType());
        $image->delete();
    }

}
