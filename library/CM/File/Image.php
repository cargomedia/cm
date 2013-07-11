<?php

class CM_File_Image extends CM_File {

	const FORMAT_JPEG = 1;
	const FORMAT_GIF = 2;
	const FORMAT_PNG = 3;

	/** @var Imagick */
	private $_imagick;

	/** @var int */
	private $_compressionQuality = 90;

	/** @var bool */
	private $_animated = false;

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
		$pathNew = isset($pathNew) ? (string) $pathNew : $this->getPath();

		if ($format === $this->getFormat()) {
			// Copy image if no conversion necessary
			if ($this->getPath() !== $pathNew) {
				$this->copy($pathNew);
				@chmod($pathNew, 0666);
			}
			return;
		}

		$imagick = $this->_getImagickClone();
		$this->_writeImagick($imagick, $pathNew, $format);
		@chmod($pathNew, 0666);
	}

	/**
	 * @param int         $widthMax
	 * @param int         $heightMax
	 * @param bool|null   $square
	 * @param string|null $pathNew
	 * @param int|null    $formatNew
	 * @throws CM_Exception
	 */
	public function resize($widthMax, $heightMax, $square = null, $pathNew = null, $formatNew = null) {
		$square = isset($square) ? (bool) $square : false;
		$pathNew = isset($pathNew) ? (string) $pathNew : $this->getPath();
		$formatNew = isset($formatNew) ? (int) $formatNew : $this->getFormat();

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

		try {
			$this->_invokeOnEveryFrame($imagick, function (Imagick $imagick) use ($offsetX, $offsetY, $width, $height, $widthResize, $heightResize) {
				if (null !== $offsetX && null !== $offsetY) {
					$imagick->cropImage($width, $height, $offsetX, $offsetY);
				}
				$imagick->thumbnailImage($widthResize, $heightResize);
			}, $formatNew);
		} catch (ImagickException $e) {
			throw new CM_Exception('Error when resizing image: ' . $e->getMessage());
		}

		$this->_writeImagick($imagick, $pathNew, $formatNew);
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
		$pathNew = isset($pathNew) ? (string) $pathNew : $this->getPath();
		$formatNew = isset($formatNew) ? (int) $formatNew : $this->getFormat();

		$imagick = $this->_getImagickClone();

		$this->_invokeOnEveryFrame($imagick, function (Imagick $imagick) use ($angle) {
			if (true !== $imagick->rotateImage(new ImagickPixel('#00000000'), $angle)) {
				throw new CM_Exception('Cannot rotate image by `' . $angle . '` degrees');
			}
		}, $formatNew);

		$this->_writeImagick($imagick, $pathNew, $formatNew);
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
	 */
	public function isAnimated() {
		return $this->_animated;
	}

	/**
	 * @param int $quality 1-100
	 * @throws CM_Exception_Invalid
	 */
	public function setCompressionQuality($quality) {
		$quality = (int) $quality;
		if ($quality < 1 || $quality > 100) {
			throw new CM_Exception_Invalid('Invalid compression quality `' . $quality . '`, should be between 1-100.');
		}
		$this->_compressionQuality = $quality;
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
				$imagick = new Imagick($this->getPath());
				if ($imagick->getIteratorIndex() > 0) {
					$this->_animated = true;
					$imagick = $imagick->coalesceImages();
				}
			} catch (ImagickException $e) {
				throw new CM_Exception('Cannot load Imagick instance for `' . $this->getPath() . '`: ' . $e->getMessage());
			}
			$this->_imagick = $imagick;
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
	 * @param Imagick $imagick
	 * @param string  $path
	 * @param int     $format
	 * @throws CM_Exception
	 */
	private function _writeImagick(Imagick $imagick, $path, $format) {
		if (true !== $imagick->setFormat($this->_getImagickFormat($format))) {
			throw new CM_Exception('Cannot set image format `' . $format . '`');
		}
		$compressionQuality = $this->_getCompressionQuality();
		if (true !== $imagick->setImageCompressionQuality($compressionQuality)) {
			throw new CM_Exception('Cannot set compression quality to `' . $compressionQuality . '`.');
		}
		if (!$this->_getAnimationRequired($format)) {
			if (true !== $imagick->writeImage($path)) {
				throw new CM_Exception('Cannot write image to `' . $path . '`');
			}
		} else {
			if (true !== $imagick->writeImages($path, true)) {
				throw new CM_Exception('Cannot write images to `' . $path . '`');
			}
		}
		if ($path === $this->getPath()) {
			$this->_imagick = $imagick;
			$this->_animated = $this->_getAnimationRequired($format);
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

	/**
	 * @param Imagick  $imagick
	 * @param callable $callback fn(Imagick)
	 * @param int      $format
	 */
	private function _invokeOnEveryFrame(Imagick $imagick, Closure $callback, $format) {
		if (!$this->_getAnimationRequired($format)) {
			$callback($imagick);
		} else {
			/** @var Imagick $frame */
			foreach ($imagick as $frame) {
				$callback($imagick);
			}
		}
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
				throw new CM_Exception_Invalid('Invalid format `' . $format . '`.');
		}
	}

	public static function create($path, $content = null) {
		$file = CM_File::create($path, $content);

		if (!self::isValid($file)) {
			$file->delete();
			throw new CM_Exception('Could not create `' . $path . '`');
		}

		return new self($path);
	}

	/**
	 * @param CM_File $file
	 * @return bool
	 */
	public static function isValid(CM_File $file) {
		if (!extension_loaded('imagick')) {
			throw new CM_Exception('Missing `imagick` extension');
		}

		try {
			$imagick = new Imagick($file->getPath());
		} catch (ImagickException $e) {
			return false;
		}

		return $imagick->valid();
	}
}
