<?php

class CM_Captcha {

	private $_id;
	private $_text;
	private $_fontPath;

	/**
	 * @param int $id
	 * @throws CM_Exception_Nonexistent
	 */
	public function __construct($id) {
		$this->_id = (int) $id;
		$this->_text = CM_Db_Db::select(TBL_CM_CAPTCHA, 'number', array('captcha_id' => $this->getId()))->fetchColumn();
		if (!$this->_text) {
			throw new CM_Exception_Nonexistent('Invalid captcha id `' . $id . '`');
		}
		$this->_fontPath = DIR_PUBLIC . 'static' . DIRECTORY_SEPARATOR . 'font' . DIRECTORY_SEPARATOR . 'comicsans.ttf';
	}

	/**
	 * @return CM_Captcha
	 */
	public static function create() {
		$number = rand(10000, 99999);
		$id = CM_Db_Db::insert(TBL_CM_CAPTCHA, array('number' => $number, 'create_time' => time()));
		return new self($id);
	}

	/**
	 * @param int $age
	 */
	public static function deleteOlder($age) {
		$age = (int) $age;
		CM_Db_Db::delete(TBL_CM_CAPTCHA, '`create_time` < ' . (time() - $age));
	}

	/**
	 * @return int
	 */
	public function getId() {
		return $this->_id;
	}

	/**
	 * @return string
	 */
	public function getText() {
		return $this->_text;
	}

	/**
	 * @param string $text
	 * @return bool
	 */
	public function check($text) {
		$result = ($this->getText() == $text);
		$this->_delete();
		return $result;
	}

	private function _delete() {
		CM_Mysql::delete(TBL_CM_CAPTCHA, array('captcha_id' => $this->getId()));
	}

	/**
	 * @param int $width
	 * @param int $height
	 * @return string PNG-data
	 * @throws CM_Exception
	 */
	public function render($width, $height) {
		if (!$resource = imagecreate($width, $height)) {
			throw new CM_Exception('Could not create captcha image');
		}

		// Fill background
		$red = $green = $blue = null;
		sscanf('F0A0C0', "%2x%2x%2x", $red, $green, $blue);
		for ($i = 0, $rd = ($red > 0) ? $red : rand(0, 100), $gr = ($green > 0) ? $green : rand(0, 100), $bl = ($blue > 0) ? $blue : rand(0, 100);
			 $i <= $height; $i++) {
			$color = @imagecolorallocate($resource, $rd += ($rd < 250) ? 2 : 0, $gr += ($gr < 250) ? 2 : 0, $bl += ($bl < 250) ? 2 : 0);
			@imageline($resource, 0, $i, $width, $i, $color);
		}
		$colorBackground = $color;

		// Apply text
		$colorShadow = @imagecolorallocate($resource, 0, rand(0, 255), rand(0, 255));
		$size = 20;
		$text = strtoupper($this->getText());
		for ($i = 0, $strlen = strlen($text), $p = floor(abs((($width - ($size * $strlen)) / 2) - floor($size / 2)));
			 $i < $strlen; $i++, $p += $size) {
			$d = rand(-8, 8);
			$y = rand(floor($height / 2) + floor($size / 2), $height - floor($size / 2));
			for ($b = 0; $b <= 3; $b++) {
				imagettftext($resource, $size, $d, $p++, $y++, $colorShadow, $this->_fontPath, $text{$i});
			}
			@imagettftext($resource, $size, $d, $p, $y, $colorBackground, $this->_fontPath, $text{$i});
		}

		// Apply grid
		$size = rand($size, 30);
		for ($i = 0, $x = 0, $z = $width; $i < $width; $i++, $z -= $size, $x += $size) {
			@imageline($resource, $x, 0, $x + 10, $height, $colorBackground);
			@imageline($resource, $z, 0, $z - 10, $height, $colorBackground);
		}

		// Apply dots
		for ($i = 0; $i < $width * 2; $i++) {
			$color = @imagecolorallocate($resource, rand(0, 255), rand(0, 255), rand(0, 255));
			imagesetpixel($resource, rand(0, $width), rand(0, $height), $color);
		}

		ob_start();
		@imagepng($resource);
		$image = ob_get_clean();
		@imagedestroy($resource);
		return $image;
	}
}
