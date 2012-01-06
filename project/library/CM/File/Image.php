<?php

class CM_File_Image extends CM_File {
	const JPEG_QUALITY = 95;

	private static $_SUPPORTED_IMAGE_TYPES = array(
		IMAGETYPE_JPEG,
		IMAGETYPE_GIF,
		IMAGETYPE_PNG
	);

	public static $SUPPORTED_MIME_TYPES = array(
		'image/jpeg',
		'image/pjpeg',
		'image/gif',
		'image/png',
		'image/x-png',
	);

	protected $_resource = null;
	
	public function __construct($file) {
		parent::__construct($file);

		if (!in_array($this->getType(), self::$SUPPORTED_MIME_TYPES) || !in_array($this->getInternalType(), self::$_SUPPORTED_IMAGE_TYPES)) {
			throw new CM_Exception_Invalid('Invalid image type');
		}
		
		$this->_resource = $this->_image2resource($this->getInternalType());
	}
	
	public function __desctruct() {
		imagedestroy($this->_resource);
	}

	/**
	 * Uploads Image file to server
	 *
	 * @param array $options (file_uniqid, result_path, result_width, result_height,
	 * @return CM_File_Image
	 * @throws CM_Exception_Invalid
	 */
	public static function create(array $options) {
		if (!isset($options['path'])) {
			throw new CM_Exception_Invalid('path has to be set');
		}

		if (!isset($options['result_path'])) {
			throw new CM_Exception_Invalid('result_path has to be set');
		}

		$path = $options['path'];
		$newPath = $options['result_path'];
		$type = (int) IMAGETYPE_JPEG;

		$image = new self($path);

		$width = empty($options['result_width']) ? $image->getWidth() : (int) $options['result_width'];
		$height = empty($options['result_height']) ? $image->getHeight() : (int) $options['result_height'];

		$image->convert($type, $newPath);

		if ($width && $height) {
			$image->resize($width, $height, false, $newPath);
		}

		return $image;
	}

	private function _resource2image($resource, $path, $type = IMAGETYPE_JPEG) {

		$result = false;
		switch ($type) {
			case IMAGETYPE_JPEG:
				$result = imagejpeg($resource, $path, self::JPEG_QUALITY);
				break;
			case IMAGETYPE_GIF:
				$result = imagegif($resource, $path);
				break;
			case IMAGETYPE_PNG:
				$result = imagepng($resource, $path);
				break;
		}

		if (!$result) {
			throw new CM_Exception_Invalid('Could not convert image');
		}

		return $result;
	}
	
	/**
	 * @return resource Image object
	 */
	private function _getResource() {
		return $this->_resource;
	}

	private function _image2resource($type) {
		$path = $this->getPath();

		$resource = null;
		switch ($type) {
			case 1:
				$resource = @imagecreatefromgif($path);
				break;
			case 2:
				$resource = @imagecreatefromjpeg($path);
				break;
			case 3:
				$resource = @imagecreatefrompng($path);
				break;
			default:
				throw new CM_Exception_Invalid('wrong image type');
				break;
		}

		if (!$resource) {
			throw new CM_Exception_Invalid('Invalid image');
		}

		return $resource;
	}

	/**
	 * Converts the image to the given type.
	 *
	 * If newPath is empty, the original image will be overwritten
	 *
	 * @param int $type Image type. Use IMAGETYPE_* consts
	 * @param string $newPath OPTINAL Where the resulting image should be written
	 * @return bool True on success
	 */
	public function convert($type = IMAGETYPE_JPEG, $newPath = '') {
		$path = $this->getPath();

		$type = in_array($type, self::$_SUPPORTED_IMAGE_TYPES) ? $type : IMAGETYPE_JPEG;

		// Copy image if no conversion necessary
		if ($this->getInternalType() == $type) {
			if (!empty($newPath)) {
				copy($path, $newPath);
			}
			return true;
		}

		if (empty($newPath)) {
			$newPath = $path;
		}

		$result = $this->_resource2image($this->_getResource(), $newPath, $type);
		@chmod($newPath, 0777);

		return $result;
	}

	/**
	 * Resizes the image. If $newPath is empty, original image will be overwritten
	 *
	 * @param int $width New width
	 * @param int $height New height
	 * @param bool $square True if result image should be a square
	 * @param string $newPath OPTIONAL If set, image is stored a new location
	 * @return bool|null
	 */
	public function resize($width, $height, $square = false, $newPath = null) {
		$source_path = $this->getPath();

		$newPath = isset($newPath) ? trim($newPath) : $source_path;

		// Assign to variables because afterwards reference used -> todo
		$imgWidth = $this->getWidth();
		$imgHeight = $this->getHeight();

		if (($imgWidth == $width) && ($imgHeight == $height)) {
			if (isset($newPath)) {
				copy($source_path, $newPath);
			}
			return true;
		}

		$resource = $this->_getResource();
		
		if ($square) {
			$resource = $this->_makeSquare($imgWidth, $imgHeight);
		}

		if ((($imgWidth > $width) && $width) || (($imgHeight > $height) && $height)) {
			if ($imgHeight / $height > $imgWidth / $width) {
				$scale_coef = $height / $imgHeight;
			} else {
				$scale_coef = $width / $imgWidth;
			}

			$dest_height = $imgHeight * $scale_coef;
			$dest_width = $imgWidth * $scale_coef;
		} else {
			$dest_height = $imgHeight;
			$dest_width = $imgWidth;
		}

		$dist_resource = imagecreatetruecolor($dest_width, $dest_height);
		$result = imagecopyresampled($dist_resource, $resource, 0, 0, 0, 0, $dest_width, $dest_height, $imgWidth, $imgHeight);

		if (!$result) {
			return null;
		}

		$result = $this->_resource2image($dist_resource, $newPath, $this->getInternalType());

		@chmod($newPath, 0777);

		return $result;
	}

	private function _makeSquare(&$width, &$height) {
		$src_x = 0;
		$src_y = 0;

		if ($width == $height) {
			return $this->_getResource();
		}

		if ($width > $height) {
			$src_x = ($width - $height) / 2;
			$side_size = $width - ($width - $height);
		} else {
			$src_y = ($height - $width) / 2;
			$side_size = $height - ($height - $width);
		}

		$result = false;

		$distResource = imagecreatetruecolor($side_size, $side_size);
		$result = imagecopyresampled($distResource, $this->_getResource(), 0, 0, $src_x, $src_y, $width, $height, $width, $height);

		// These are pointers!
		$width = $height = $side_size;
		return $distResource;
	}


	/**
	 * Rotates the image
	 *
	 * If no new path is set, the original will be overwritten
	 *
	 * @param string $angle Angle to rotate the image
	 * @param string $newPath OPTIONAL New path for the rotate image
	 * @throws CM_Exception_Invalid If something goes wrong during rotation or conversion
	 */
	public function rotate($angle, $newPath = '') {
		$path = $this->getPath();

		$resource = $this->_resource;

		$rotated = imagerotate($resource, $angle + 180, 0);

		if (!$rotated) {
			throw new CM_Exception_Invalid('Error during rotation');
		}

		if (empty($newPath)) {
			$newPath = $path;
		}

		return $this->_resource2image($rotated, $newPath);
	}

	/**
	 * Returns the image width
	 *
	 * @return int Image width
	 */
	public function getWidth() {
		$data = getimagesize($this->getPath());
		return $data[0];
	}

	/**
	 * Returns the image height
	 *
	 * @return int Image height
	 */
	public function getHeight() {
		$data = getimagesize($this->getPath());
		return $data[1];
	}

	/**
	 * Returns the image type
	 *
	 * @return string Image type base on IMAGETYPE consts
	 */
	public function getInternalType() {
		$data = getimagesize($this->getPath());
		return $data[2];
	}
}
