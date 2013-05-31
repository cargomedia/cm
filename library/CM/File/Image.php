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

		if ($square) {
			$imagick = $this->_getImagickCloneSquare();
			$width = $height = ($width < $height ? $width : $height);
		} else {
			$imagick = $this->_getImagickClone();
		}

		if (($width > $widthMax) || ($height > $heightMax)) {
			if ($height / $heightMax > $width / $widthMax) {
				$scaleCoefficient = $heightMax / $height;
			} else {
				$scaleCoefficient = $widthMax / $width;
			}
			$heightNew = $height * $scaleCoefficient;
			$widthNew = $width * $scaleCoefficient;
		} else {
			// Don't blow image up
			$heightNew = $height;
			$widthNew = $width;
		}

		try {
			$imagick->thumbnailImage($widthNew, $heightNew);
		} catch (ImagickException $e) {
			throw new CM_Exception('Cannot resize image to `' . $widthNew . '`x`' . $heightNew . '`.');
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
	 * @param int $quality 1-100
	 * @throws CM_Exception_Invalid
	 */
	public function setCompressionQuality($quality) {
		$this->_compressionQuality = (int) $quality;
		if ($quality < 1 || $quality > 100) {
			throw new CM_Exception_Invalid('Invalid compression quality `' . $quality . '`, should be between 1-100.');
		}
	}

	/**
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
	 * @return Imagick
	 * @throws CM_Exception
	 */
	private function _getImagickCloneSquare() {
		$imagick = $this->_getImagickClone();
		$width = $this->getWidth();
		$height = $this->getHeight();
		if ($width == $height) {
			return $imagick;
		}

		if ($width > $height) {
			$offsetX = ($width - $height) / 2;
			$offsetY = 0;
			$size = $height;
		} else {
			$offsetX = 0;
			$offsetY = ($height - $width) / 2;
			$size = $width;
		}

		if (true !== $imagick->cropImage($size, $size, $offsetX, $offsetY)) {
			throw new CM_Exception('Cannot crop image');
		}
		return $imagick;
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
}
