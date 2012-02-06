<?php

class CM_Color {
	/**
	 * @var float
	 */
	private $_red, $_green, $_blue;

	/**
	 * @var float
	 */
	private $_alpha;

	/**
	 * @param float      $red
	 * @param float      $green
	 * @param float      $blue
	 * @param float|null $alpha
	 */
	public function __construct($red, $green, $blue, $alpha = null) {
		if (null === $alpha) {
			$alpha = 1;
		}
		$this->_red = max(0, min(1, (float) $red));
		$this->_green = max(0, min(1, (float) $green));
		$this->_blue = max(0, min(1, (float) $blue));
		$this->_alpha = max(0, min(1, (float) $alpha));
	}

	/**
	 * @return float
	 */
	public function getRed() {
		return $this->_red;
	}

	/**
	 * @return float
	 */
	public function getGreen() {
		return $this->_green;
	}

	/**
	 * @return float
	 */
	public function getBlue() {
		return $this->_blue;
	}

	/**
	 * @return float
	 */
	public function getAlpha() {
		return $this->_alpha;
	}
}
