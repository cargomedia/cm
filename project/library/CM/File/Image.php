<?php

class CM_File_Image extends CM_File {
	const QUALITY_JPEG = 95;

	/**
	 * @var int
	 */
	private $_imageType;

	/**
	 * @var resource
	 */
	private $_resource;

	public function __construct($file) {
		parent::__construct($file);

		$this->_getResource(); // Make sure resource can be created
	}

	public function __destruct() {
		imagedestroy($this->_resource);
	}

	/**
	 * @param int		 $type	Image type. Use IMAGETYPE_* consts
	 * @param string|null $newPath Where the resulting image should be written
	 */
	public function convert($type, $newPath = null) {
		if ($this->_getImageType() == $type) {
			// Copy image if no conversion necessary
			if (isset($newPath)) {
				$this->copy($newPath);
			}
			return;
		}

		$this->_writeResource($this->_resource, $newPath, $type);
		@chmod($newPath, 0777);
	}

	/**
	 * @param int	$widthMax   New width
	 * @param int	$heightMax  New height
	 * @param bool   $square     True if result image should be a square
	 * @param string $pathNew    OPTIONAL If set, image is stored a new location
	 */
	public function resize($widthMax, $heightMax, $square = false, $pathNew = null) {
		$width = $this->getWidth();
		$height = $this->getHeight();

		if (($width == $widthMax) && ($height == $heightMax)) {
			if (isset($pathNew)) {
				$this->copy($pathNew);
			}
			return;
		}

		$resource = $this->_getResource();
		if ($square) {
			$resource = $this->_getResourceSquare();
			$width = $height = ($width < $height ? $width : $height);
		}

		if (($width > $widthMax) || ($height > $heightMax)) {
			if ($height / $heightMax > $width / $widthMax) {
				$scale_coef = $heightMax / $height;
			} else {
				$scale_coef = $widthMax / $width;
			}
			$heightNew = $height * $scale_coef;
			$widthNew = $width * $scale_coef;
		} else {
			// Don't blow image up
			$heightNew = $height;
			$widthNew = $width;
		}

		$resourceNew = imagecreatetruecolor($widthNew, $heightNew);
		$result = imagecopyresampled($resourceNew, $resource, 0, 0, 0, 0, $widthNew, $heightNew, $width, $height);
		if (!$result) {
			throw new CM_Exception_Invalid('Cannot resample image');
		}
		$this->_writeResource($resourceNew, $pathNew);
		@chmod($pathNew, 0777);
	}

	/**
	 * @param int		 $angle   Angle to rotate the image
	 * @param string|null $pathNew OPTIONAL New path for the rotate image
	 * @throws CM_Exception_Invalid If something goes wrong during rotation or conversion
	 */
	public function rotate($angle, $pathNew = null) {
		$resource = imagerotate($this->_resource, $angle + 180, 0);
		if (!$resource) {
			throw new CM_Exception_Invalid('Cannot rotate image');
		}
		$this->_writeResource($resource, $pathNew);
	}

	/**
	 * @return int
	 */
	public function getWidth() {
		$width = imagesx($this->_resource);
		if (false === $width) {
			throw new CM_Exception_Invalid('Cannot detect image width');
		}
		return $width;
	}

	/**
	 * @return int
	 */
	public function getHeight() {
		$height = imagesy($this->_resource);
		if (false === $height) {
			throw new CM_Exception_Invalid('Cannot detect image height');
		}
		return $height;
	}

	/**
	 * @return resource
	 * @throws CM_Exception_Invalid
	 */
	private function _getResourceSquare() {
		$width = $this->getWidth();
		$height = $this->getHeight();
		if ($width == $height) {
			return $this->_getResource();
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

		$resource = imagecreatetruecolor($size, $size);
		$result = imagecopyresampled($resource, $this->_getResource(), 0, 0, $offsetX, $offsetY, $width, $height, $width, $height);
		if (!$result) {
			throw new CM_Exception_Invalid('Cannot resample image');
		}
		return $resource;
	}

	/**
	 * @return int
	 * @throws CM_Exception_Invalid
	 */
	private function _getImageType() {
		if (!isset($this->_imageType)) {
			$imageType = exif_imagetype($this->getPath());
			if (false === $imageType) {
				throw new CM_Exception_Invalid('Cannot detect image type of `' . $this->getPath() . '`.');
			}
			$this->_imageType = $imageType;
		}
		return $this->_imageType;
	}

	/**
	 * @return resource
	 * @throws CM_Exception_Invalid
	 */
	private function _getResource() {
		if (!isset($this->_resource)) {
			switch ($this->_getImageType()) {
				case IMAGETYPE_GIF:
					$resource = @imagecreatefromgif($this->getPath());
					break;
				case IMAGETYPE_JPEG:
					$resource = @imagecreatefromjpeg($this->getPath());
					break;
				case IMAGETYPE_PNG:
					$resource = @imagecreatefrompng($this->getPath());
					break;
				default:
					throw new CM_Exception_Invalid('Unsupported image type `' . $this->_getImageType() . '`.');
					break;
			}
			if (!$resource) {
				throw new CM_Exception_Invalid('Cannot create image resource from `' . $this->getPath() . '`.');
			}
			$this->_resource = $resource;
		}
		return $this->_resource;
	}

	/**
	 * @param resource $resource
	 * @param string|null $path
	 * @param int|null $type
	 * @throws CM_Exception_Invalid
	 */
	private function _writeResource($resource, $path = null, $type = null) {
		if (null === $path) {
			$path = $this->getPath();
		}
		if (null === $type) {
			$type = $this->_getImageType();
		}
		switch ($type) {
			case IMAGETYPE_JPEG:
				$result = imagejpeg($resource, $path, self::QUALITY_JPEG);
				break;
			case IMAGETYPE_GIF:
				$result = imagegif($resource, $path);
				break;
			case IMAGETYPE_PNG:
				$result = imagepng($resource, $path);
				break;
			default:
				throw new CM_Exception_Invalid('Unsupported image type `' . $type . '`.');
				break;
		}
		if (!$result) {
			throw new CM_Exception_Invalid('Could not convert image');
		}
		if ($path == $this->getPath()) {
			$this->_resource = $resource;
			$this->_imageType = $type;
		}
	}
}
