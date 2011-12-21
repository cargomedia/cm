<?php

class CM_Captcha {
	private $_id;
	private $_text;
	private $_fontDir;

	/**
	 * @param int $id
	 * @throws CM_Exception_Invalid
	 */
	public function __construct($id) {
		$this->_id = (int) $id;
		$this->_text = CM_Mysql::select(TBL_CAPTCHA, 'number', array('captcha_id' => $this->getId()))->fetchOne();
		if (!$this->_text) {
			throw new CM_Exception_Nonexistent('Invalid captcha id `' . $id . '`');
		}
		$this->_fontDir = DIR_PUBLIC . DIRECTORY_SEPARATOR . 'static' . DIRECTORY_SEPARATOR . 'font';
	}

	/**
	 * @return CM_Captcha
	 */
	public static function create() {
		$number = rand(10000, 99999);
		$id = CM_Mysql::insert(TBL_CAPTCHA, array('number' => $number, 'create_time' => time()));
		return new self($id);
	}

	/**
	 * @param int $age
	 */
	public static function deleteOlder($age) {
		CM_Mysql::exec('DELETE FROM TBL_CAPTCHA WHERE `create_time` < ?', (time() - (int) $age));
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
		CM_Mysql::delete(TBL_CAPTCHA, array('captcha_id' => $this->getId()));
	}

	/**
	 * Renders and directly outputs the image.
	 * 
	 * @param int $width
	 * @param int $height
	 * @throws CM_Exception
	 */
	public function render($width, $height) {
		if (!$resource = imagecreate($width, $height)) {
			throw new CM_Exception('Could not create captcha image');
		}

		$fonts = CM_Util::rglob('*.ttf', $this->_fontDir);
		if (!count($fonts)) {
			throw new CM_Exception('Couldnt load any fonts');
		}

		// Fill background
		sscanf('F0A0C0', "%2x%2x%2x", $red, $green, $blue);
		for ($i = 0, $rd = ($red > 0) ? $red : rand(0, 100), $gr = ($green > 0) ? $green : rand(0, 100), $bl = ($blue > 0) ? $blue : rand(0, 100); $i
				<= $height; $i++) {
			$color = @imagecolorallocate($resource, $rd += ($rd < 250) ? 2 : 0, $gr += ($gr < 250) ? 2 : 0, $bl += ($bl < 250) ? 2 : 0);
			@imageline($resource, 0, $i, $width, $i, $color);
		}
		$colorBackground = $color;
		$this->_bg = $colorBackground;

		// Apply text
		$colorShadow = @imagecolorallocate($resource, 0, rand(0, 255), rand(0, 255));
		$size = 20;
		$text = strtoupper($this->getText());
		$num_fonts = count($fonts);
		for ($i = 0, $strlen = strlen($text), $p = floor(abs((($width - ($size * $strlen)) / 2) - floor($size / 2))); $i < $strlen; $i++, $p += $size) {
			$font = $fonts[array_rand($fonts)];
			$d = rand(-8, 8);
			$y = rand(floor($height / 2) + floor($size / 2), $height - floor($size / 2));
			for ($b = 0; $b <= 3; $b++) {
				imagettftext($resource, $size, $d, $p++, $y++, $colorShadow, $font, $text{$i});
			}
			@imagettftext($resource, $size, $d, $p, $y, $colorBackground, $font, $text{$i});
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

		@imageinterlace($resource, 1);
		@imagepng($resource);
		@imagedestroy($resource);
	}
}
