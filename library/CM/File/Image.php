<?php

class CM_File_Image extends CM_File {

	const FORMAT_JPEG = 1;
	const FORMAT_GIF = 2;
	const FORMAT_PNG = 3;

	/** @var Imagick */
	private $_imagick;

	/** @var int */
	private $_compressionQuality = 90;

	public function __construct($file) {
		parent::__construct($file);

		$this->_getImagick(); // Make sure resource can be created
	}

	/**
	 * @param int         $format
	 * @param string|null $pathNew
	 * @throws CM_Exception
	 */
	public function convert($format, $pathNew = null) {
		$format = (int) $format;
		$pathNew = isset($pathNew) ? (string) $pathNew : null;
		if ($format === $this->getFormat()) {
			// Copy image if no conversion necessary
			if (null !== $pathNew) {
				$this->copy($pathNew);
				@chmod($pathNew, 0666);
			}
			return;
		}

		$imagick = $this->_getImagickClone();
		if (true !== $imagick->setImageFormat($this->_getImagickFormat($format))) {
			throw new CM_Exception('Cannot set image format `' . $format . '`');
		}
		$this->_writeImagick($imagick, $pathNew);
		@chmod($pathNew, 0666);
	}

	/**
	 * @param int         $widthMax
	 * @param int         $heightMax
	 * @param bool        $square     True if result image should be a square
	 * @param string|null $pathNew
	 * @param int|null    $formatNew
	 * @throws CM_Exception
	 */
	public function resize($widthMax, $heightMax, $square = false, $pathNew = null, $formatNew = null) {
		$width = $this->getWidth();
		$height = $this->getHeight();
		$offsetX = null;
		$offsetY = null;

		if ($square) {
			if ($width > $height) {
				$offsetX = floor(($width - $height) / 2);
				$offsetY = 0;
				$width = $height;
			} elseif ($width < $height) {
				$offsetX = 0;
				$offsetY = floor(($height - $width) / 2);
				$height = $width;
			}
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

		$imagick = $this->_getImagickClone();

		if (null !== $offsetX && null !== $offsetY) {
			if (true !== $imagick->cropImage($width, $height, $offsetX, $offsetY)) {
				throw new CM_Exception('Cannot crop image');
			}
		}

		try {
			$imagick->thumbnailImage($widthResize, $heightResize);
		} catch (ImagickException $e) {
			throw new CM_Exception('Cannot resize image to `' . $widthResize . '`x`' . $heightResize . '`.');
		}

		if (null !== $formatNew) {
			if (true !== $imagick->setImageFormat($this->_getImagickFormat($formatNew))) {
				throw new CM_Exception('Cannot set image format `' . $formatNew . '`');
			}
		}

		$this->_writeImagick($imagick, $pathNew);
		@chmod($pathNew, 0666);
	}

	/**
	 * @param int         $angle
	 * @param string|null $pathNew
	 * @param int|null    $formatNew
	 * @throws CM_Exception
	 */
	public function rotate($angle, $pathNew = null, $formatNew = null) {
		$angle = (int) $angle;
		$imagick = $this->_getImagickClone();
		if (true !== $imagick->rotateImage(new ImagickPixel('#00000000'), $angle)) {
			throw new CM_Exception('Cannot rotate image by `' . $angle . '` degrees');
		}

		if (null !== $formatNew) {
			if (true !== $imagick->setImageFormat($this->_getImagickFormat($formatNew))) {
				throw new CM_Exception('Cannot set image format `' . $formatNew . '`');
			}
		}

		$this->_writeImagick($imagick, $pathNew);
		@chmod($pathNew, 0666);
	}

	/**
	 * @return int
	 * @throws CM_Exception
	 */
	public function getWidth() {
		try {
			return $this->_getImagick()->getImageWidth();
		} catch (ImagickException $e) {
			throw new CM_Exception('Cannot detect image width: ' . $e->getMessage());
		}
	}

	/**
	 * @return int
	 * @throws CM_Exception
	 */
	public function getHeight() {
		try {
			return $this->_getImagick()->getImageHeight();
		} catch (ImagickException $e) {
			throw new CM_Exception('Cannot detect image height: ' . $e->getMessage());
		}
	}

	/**
	 * @return int
	 * @throws CM_Exception_Invalid
	 */
	public function getFormat() {
		$imagickFormat = $this->_getImagick()->getImageFormat();
		switch ($imagickFormat) {
			case 'JPEG':
				return self::FORMAT_JPEG;
			case 'GIF':
				return self::FORMAT_GIF;
			case'PNG':
				return self::FORMAT_PNG;
			default:
				throw new CM_Exception_Invalid('Unsupported format `' . $imagickFormat . '`.');
		}
	}

	/**
	 * @return bool
	 * @throws CM_Exception
	 */
	public function isAnimated() {
		try {
			$iteratorIndex = $this->_getImagick()->getIteratorIndex();
		} catch (ImagickException $e) {
			throw new CM_Exception('Cannot get iterator index: ' . $e->getMessage());
		}
		return ($iteratorIndex > 0);
	}

	/**
	 * @param int $quality 1-100
	 * @throws CM_Exception_Invalid
	 */
	public function setCompressionQuality($quality) {
		$this->_compressionQuality = (int) $quality;
		if ($quality < 1 || $quality > 100) {
			throw new CM_Exception_Invalid('Invalid compression quality `' . $quality . '`, should be between 1-100.');
		}
	}

	/*akec*
	 * @return Imagick
	 * @throws CM_Exception
	 */
	private function _getImagick() {
		if (!extension_loaded('imagick')) {
			throw new CM_Exception('Missing `imagick` extension');
		}
		if (!isset($this->_imagick)) {
			try {
				$this->_imagick = new Imagick($this->getPath());
			} catch (ImagickException $e) {
				throw new CM_Exception('Cannot load Imagick instance for `' . $this->getPath() . '`: ' . $e->getMessage());
			}
		}
		return $this->_imagick;
	}

	/**
	 * @return Imagick
	 */
	private function _getImagickClone() {
		$imagick = $this->_getImagick();
		return clone $imagick;
	}

	/**
	 * @param Imagick     $imagick
	 * @param string|null $path
	 * @throws CM_Exception
	 */
	private function _writeImagick(Imagick $imagick, $path = null) {
		if (null === $path) {
			$path = $this->getPath();
		}
		$compressionQuality = $this->_getCompressionQuality();
		if (true !== $imagick->setImageCompressionQuality($compressionQuality)) {
			throw new CM_Exception('Cannot set compression quality to `' . $compressionQuality . '`.');
		}
		if (true !== $imagick->writeImage($path)) {
			throw new CM_Exception('Cannot write to `' . $path . '`');
		}
		if ($path === $this->getPath()) {
			$this->_imagick = $imagick;
		}
	}

	/**
	 * @return int
	 */
	private function _getCompressionQuality() {
		return $this->_compressionQuality;
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
				throw new CM_Exception_Invalid('Invalid format `' . $format . '`.');
		}
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
}
