<?php

class CM_Image_Image {

    const FORMAT_JPEG = 1;
    const FORMAT_GIF = 2;
    const FORMAT_PNG = 3;
    const FORMAT_SVG = 4;

    /** @var Imagick|null */
    private $_imagick;

    /** @var bool|null */
    private $_animated;

    /** @var int */
    private $_compressionQuality = 80;

    /** @var string|null * */
    private $_imageContainer;

    /**
     * @param string|Imagick $imageContainer
     * @throws CM_Exception
     */
    public function __construct($imageContainer) {
        if (!is_a($imageContainer, 'Imagick')) {
            $imagick = new Imagick();
            $this->_imageContainer = (string) $imageContainer;
            $imagick->pingImageBlob($this->_imageContainer);
        } else {
            $imagick = $imageContainer;
            try {
                $imagick->valid(); //seems to be the only method to check if it contains an image
            } catch (ImagickException $e) {
                throw new CM_Exception_Invalid('$imagick does not contain any image', null, ['originalExceptionMessage' => $e->getMessage()]);
            }
        }
        try {
            if ($imagick->getIteratorIndex() > 0) {
                $this->_animated = true;
                $imagick = $imagick->coalesceImages();
            } else {
                $this->_animated = false;
            }
        } catch (ImagickException $e) {
            throw new CM_Exception('Cannot process image', null, ['originalExceptionMessage' => $e->getMessage()]);
        }
        $this->_imagick = $imagick;
    }

    public function __clone() {
        $this->_imagick = clone $this->_imagick;
    }

    private function _readImageBlob() {
        try {
            if (false === $this->_imagick->readImageBlob($this->_imageContainer)) {
                throw new ImagickException('Unreadable instance variable containing image blob');
            }
        } catch (ImagickException $e) {
            throw new CM_Exception_Invalid('Cannot load image blob', null, ['originalExceptionMessage' => $e->getMessage()]);
        }
    }

    /**
     * @return $this
     */
    public function clearAndApplyExifRotation() {
        $orientation = $this->_imagick->getImageOrientation();
        switch ($orientation) {
            case Imagick::ORIENTATION_TOPRIGHT: // flipped
            case Imagick::ORIENTATION_UNDEFINED: // undefined
            case Imagick::ORIENTATION_TOPLEFT: // normal
                break;
            case Imagick::ORIENTATION_BOTTOMLEFT: // 180° flipped
            case Imagick::ORIENTATION_BOTTOMRIGHT: // 180°
                $this->rotate(-180);
                $this->_imagick->setImageOrientation(1);
                break;
            case Imagick::ORIENTATION_LEFTTOP: // 270° flipped
            case Imagick::ORIENTATION_RIGHTTOP: // 270°
                $this->rotate(-270);
                $this->_imagick->setImageOrientation(1);
                break;
            case Imagick::ORIENTATION_RIGHTBOTTOM: // 90° flipped
            case Imagick::ORIENTATION_LEFTBOTTOM: // 90°
                $this->rotate(-90);
                $this->_imagick->setImageOrientation(1);
                break;
        }
        return $this;
    }

    /**
     * @param int      $width
     * @param int      $height
     * @param int|null $offsetX
     * @param int|null $offsetY
     * @return $this
     * @throws CM_Exception
     */
    public function crop($width, $height, $offsetX = null, $offsetY = null) {
        $width = (int) $width;
        $height = (int) $height;
        $offsetX = (null !== $offsetX) ? (int) $offsetX : null;
        $offsetY = (null !== $offsetY) ? (int) $offsetY : null;

        if (null === $offsetX) {
            $offsetX = ($this->getWidth() - $width) / 2;
        }
        if (null === $offsetY) {
            $offsetY = ($this->getHeight() - $height) / 2;
        }
        $this->_invokeOnEveryFrame(function (Imagick $frame) use ($width, $height, $offsetX, $offsetY) {
            $frame->cropImage($width, $height, $offsetX, $offsetY);
        });
        return $this;
    }

    /**
     * @return string
     * @throws CM_Exception
     */
    public function getBlob() {
        $this->_imagick->setImageCompressionQuality($this->getCompressionQuality());
        try {
            if ($this->_getAnimationRequired($this->getFormat())) {
                $imageBlob = $this->_imagick->getImagesBlob();
            } else {
                $imageBlob = $this->_imagick->getImageBlob();
            }
        } catch (ImagickException $e) {
            throw new CM_Exception('Cannot get image blob', null, ['originalExceptionMessage' => $e->getMessage()]);
        }
        return $imageBlob;
    }

    /**
     * @return CM_Image_Image
     */
    public function getClone() {
        return clone $this;
    }

    /**
     * @return int
     * @throws CM_Exception_Invalid
     */
    public function getFormat() {
        $imagickFormat = $this->_imagick->getImageFormat();
        switch ($imagickFormat) {
            case 'JPEG':
                return self::FORMAT_JPEG;
            case 'GIF':
                return self::FORMAT_GIF;
            case'PNG':
                return self::FORMAT_PNG;
            case'SVG':
                return self::FORMAT_SVG;
            default:
                throw new CM_Exception_Invalid('Unsupported format', null, ['format' => $imagickFormat]);
        }
    }

    /**
     * @param CM_Image_Image $image
     * @param int            $x
     * @param int            $y
     */
    public function compositeImage(CM_Image_Image $image, $x, $y) {
        $this->_imagick->compositeImage($image->_imagick, Imagick::COMPOSITE_DEFAULT, (int) $x, (int) $y);
    }

    /**
     * @param int $format
     * @return $this
     * @throws CM_Exception
     */
    public function setFormat($format) {
        if (true !== $this->_imagick->setImageFormat($this->_getImagickFormat($format))) {
            throw new CM_Exception('Cannot set image format', null, ['format' => $format]);
        }
        $this->_animated = $this->_getAnimationRequired($format);
        return $this;
    }

    /**
     * @return int
     * @throws CM_Exception
     */
    public function getHeight() {
        try {
            return $this->_imagick->getImageHeight();
        } catch (ImagickException $e) {
            throw new CM_Exception('Cannot detect image height', null, ['originalExceptionMessage' => $e->getMessage()]);
        }
    }

    /**
     * @return int
     * @throws CM_Exception
     */
    public function getWidth() {
        try {
            return $this->_imagick->getImageWidth();
        } catch (ImagickException $e) {
            throw new CM_Exception('Cannot detect image width', null, ['originalExceptionMessage' => $e->getMessage()]);
        }
    }

    /**
     * @return bool
     */
    public function isAnimated() {
        return $this->_animated;
    }

    /**
     * @param int       $widthMax
     * @param int       $heightMax
     * @param bool|null $square
     * @return $this
     * @throws CM_Exception
     */
    public function resize($widthMax, $heightMax, $square = null) {
        $square = (boolean) $square;

        $width = $this->getWidth();
        $height = $this->getHeight();
        $dimensions = self::calculateDimensions($width, $height, $widthMax, $heightMax, $square);
        $widthResize = $dimensions['width'];
        $heightResize = $dimensions['height'];

        if ($square && ($width !== $height)) {
            $cropSize = min($width, $height);
            $this->crop($cropSize, $cropSize);
        }

        $this->resizeSpecific($widthResize, $heightResize);
        return $this;
    }

    /**
     * @param int $widthResize
     * @param int $heightResize
     * @return $this
     * @throws CM_Exception
     * @throws CM_Exception_Invalid
     */
    public function resizeSpecific($widthResize, $heightResize) {
        $width = $this->getWidth();
        $height = $this->getHeight();

        try {
            $this->_invokeOnEveryFrame(function (Imagick $frame) use ($width, $height, $widthResize, $heightResize) {
                $frame->resizeImage($widthResize, $heightResize, Imagick::FILTER_CATROM, 1);
            });
        } catch (ImagickException $e) {
            throw new CM_Exception('Error when resizing image', null, ['originalExceptionMessage' => $e->getMessage()]);
        }
        return $this;
    }

    /**
     * @param int $angle
     * @return $this
     */
    public function rotate($angle) {
        $angle = (int) $angle;

        $this->_invokeOnEveryFrame(function (Imagick $frame) use ($angle) {
            if (true !== $frame->rotateImage(new ImagickPixel('#00000000'), $angle)) {
                throw new CM_Exception('Cannot rotate image by given angle', null, ['angleDegrees' => $angle]);
            }
        });
        return $this;
    }

    /**
     * @param int $quality 1-100
     * @return $this
     * @throws CM_Exception_Invalid
     */
    public function setCompressionQuality($quality) {
        $quality = (int) $quality;
        if ($quality < 1 || $quality > 100) {
            throw new CM_Exception_Invalid('Invalid compression quality. It should be between 1-100.', null, ['quality' => $quality]);
        }
        $this->_compressionQuality = $quality;
        return $this;
    }

    /**
     * @return int
     */
    public function getCompressionQuality() {
        return $this->_compressionQuality;
    }

    /**
     * @return $this
     */
    public function stripProfileData() {
        $this->_imagick->stripImage();
        return $this;
    }

    /**
     * @return $this
     * @throws CM_Exception_Invalid
     */
    public function validate() {
        $this->getFormat();
        return $this;
    }

    /**
     * @param int $format
     * @return bool
     */
    private function _getAnimationRequired($format) {
        if (self::FORMAT_GIF === $format && $this->isAnimated()) {
            return true;
        }
        return false;
    }

    /**
     * @param int $format
     * @return string
     * @throws CM_Exception_Invalid
     */
    private function _getImagickFormat($format) {
        switch ($format) {
            case self::FORMAT_JPEG:
                return 'JPEG';
            case self::FORMAT_GIF:
                return 'GIF';
            case self::FORMAT_PNG:
                return 'PNG';
            default:
                throw new CM_Exception_Invalid('Invalid format', null, ['format' => $format]);
        }
    }

    /**
     * @param Closure $callback
     */
    private function _invokeOnEveryFrame(Closure $callback) {
        $this->_readImageBlob();
        if (!$this->_getAnimationRequired($this->getFormat())) {
            $callback($this->_imagick);
        } else {
            /** @var Imagick $frame */
            foreach ($this->_imagick as $frame) {
                $callback($frame);
            }
        }
    }

    /**
     * @param int  $width
     * @param int  $height
     * @param int  $widthMax
     * @param int  $heightMax
     * @param bool $square
     * @return array ['width' => int, 'height' => int]
     */
    public static function calculateDimensions($width, $height, $widthMax, $heightMax, $square) {
        if ($square) {
            $width = $height = min($width, $height);
        }

        if (($width > $widthMax) || ($height > $heightMax)) {
            if ($height / $heightMax > $width / $widthMax) {
                $scaleCoefficient = $heightMax / $height;
            } else {
                $scaleCoefficient = $widthMax / $width;
            }
            $heightResize = $height * $scaleCoefficient;
            $widthResize = $width * $scaleCoefficient;
        } else {
            // Don't blow image up
            $heightResize = $height;
            $widthResize = $width;
        }

        $heightResize = max($heightResize, 1);
        $widthResize = max($widthResize, 1);

        return [
            'width'  => (int) $widthResize,
            'height' => (int) $heightResize,
        ];
    }

    /**
     * @param int $format
     * @return string
     * @throws CM_Exception_Invalid
     */
    public static function getExtensionByFormat($format) {
        switch ($format) {
            case self::FORMAT_JPEG:
                return 'jpg';
            case self::FORMAT_GIF:
                return 'gif';
            case self::FORMAT_PNG:
                return 'png';
            default:
                throw new CM_Exception_Invalid('Invalid format', null, ['format' => $format]);
        }
    }

    /**
     * @param string      $imageBlob
     * @param float       $resolutionX
     * @param float       $resolutionY
     * @param string|null $backgroundColor
     * @return CM_Image_Image
     * @throws CM_Exception_Invalid
     */
    public static function createFromSVG($imageBlob, $resolutionX, $resolutionY, $backgroundColor = null) {
        $imageBlob = (string) $imageBlob;
        if ('<?xml' !== substr($imageBlob, 0, 5)) {
            $imageBlob = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>' . $imageBlob;
        }
        $imagick = new Imagick();
        $imagick->setResolution((float) $resolutionX, (float) $resolutionY);
        if (null !== $backgroundColor) {
            $backgroundColor = (string) $backgroundColor;
        } else {
            $backgroundColor = new ImagickPixel('transparent');
        }
        $imagick->setBackgroundColor($backgroundColor);
        try {
            $imagick->readImageBlob($imageBlob);
        } catch (ImagickException $e) {
            throw new CM_Exception_Invalid('Cannot load Imagick instance', null, ['originalExceptionMessage' => $e->getMessage()]);
        }
        return new self($imagick);
    }

    /**
     * @param string      $imageBlob
     * @param int         $width
     * @param int         $height
     * @param string|null $backgroundColor
     * @return CM_Image_Image
     */
    public static function createFromSVGWithSize($imageBlob, $width, $height, $backgroundColor = null) {
        $image = self::createFromSVG($imageBlob, 72, 72);
        $scale = max([$width / $image->getWidth(), $height / $image->getHeight()]);
        $image = self::createFromSVG($imageBlob, 72 * $scale, 72 * $scale, $backgroundColor);
        return $image;
    }
}
