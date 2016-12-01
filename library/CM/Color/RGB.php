<?php

class CM_Color_RGB {

    /** @var \MischiefCollective\ColorJizz\Formats\RGB */
    private $_colorJizz;

    /**
     * @param float $red   0-255
     * @param float $green 0-255
     * @param float $blue  0-255
     */
    public function __construct($red, $green, $blue) {
        $this->_colorJizz = new \MischiefCollective\ColorJizz\Formats\RGB($red, $green, $blue);
    }

    /**
     * @return float 0-255
     */
    public function getRed() {
        return $this->_colorJizz->red;
    }

    /**
     * @return float 0-255
     */
    public function getGreen() {
        return $this->_colorJizz->green;
    }

    /**
     * @return float 0-255
     */
    public function getBlue() {
        return $this->_colorJizz->blue;
    }

    /**
     * @param int       $hue 0-360
     * @param bool|null $relative
     * @return CM_Color_RGB
     */
    public function setHue($hue, $relative = null) {
        $hsl = $this->_colorJizz->toHSL();
        $hsl->hue = $relative ? $hsl->hue + $hue : $hue;
        $hsl->hue = fmod($hsl->hue, 360);
        return self::_factoryByColorJizz($hsl);
    }

    /**
     * @param int       $saturation 0-100
     * @param bool|null $relative
     * @return CM_Color_RGB
     */
    public function setSaturation($saturation, $relative = null) {
        $hsl = $this->_colorJizz->toHSL();
        $hsl->saturation = $relative ? $hsl->saturation + $saturation : $saturation;
        $hsl->saturation = fmod($hsl->saturation, 100);
        return self::_factoryByColorJizz($hsl);
    }

    /**
     *
     * @param int       $lightness 0-100
     * @param bool|null $relative
     * @return CM_Color_RGB
     */
    public function setLightness($lightness, $relative = null) {
        $hsl = $this->_colorJizz->toHSL();
        $hsl->lightness = $relative ? $hsl->lightness + $lightness : $lightness;
        $hsl->lightness = fmod($hsl->lightness, 100);
        return self::_factoryByColorJizz($hsl);
    }

    /**
     * @return string
     */
    public function toHexString() {
        return $this->_colorJizz->toHex()->__toString();
    }

    /**
     * @param string $hexString
     * @return CM_Color_RGB
     */
    public static function factoryByHexString($hexString) {
        $hex = \MischiefCollective\ColorJizz\Formats\Hex::fromString($hexString);
        $rgb = $hex->toRGB();
        return new self($rgb->red, $rgb->green, $rgb->blue);
    }

    /**
     * @param \MischiefCollective\ColorJizz\ColorJizz $colorJizz
     * @return CM_Color_RGB
     */
    private static function _factoryByColorJizz(\MischiefCollective\ColorJizz\ColorJizz $colorJizz) {
        $rgb = $colorJizz->toRGB();
        return new self($rgb->red, $rgb->green, $rgb->blue);
    }
}
