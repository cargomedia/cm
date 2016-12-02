<?php

class CM_Color_RGBTest extends CMTest_TestCase {

    public function testConstructor() {
        $color = new CM_Color_RGB(0, 255, 12.3);

        $this->assertSame(0, $color->getRed());
        $this->assertSame(255, $color->getGreen());
        $this->assertSame(12.3, $color->getBlue());
    }

    /**
     * @expectedException CM_Exception_Invalid
     */
    public function testConstructorInvalid() {
        new CM_Color_RGB(0, 0, 999);
    }

    public function testSetHue() {
        $color = new CM_Color_RGB(0, 255, 0);

        $this->assertSame('FF8000', $color->setHue(30)->getHexString());
        $this->assertSame('00FF80', $color->setHue(30, true)->getHexString());
    }

    public function testSetSaturation() {
        $color = new CM_Color_RGB(0, 255, 0);

        $this->assertSame('59A659', $color->setSaturation(30)->getHexString());
        $this->assertSame('26D926', $color->setSaturation(-30, true)->getHexString());
    }

    public function testSetLightness() {
        $color = new CM_Color_RGB(0, 255, 0);

        $this->assertSame('009900', $color->setLightness(30)->getHexString());
        $this->assertSame('006600', $color->setLightness(-30, true)->getHexString());
    }

    public function testGetHexString() {
        $color = new CM_Color_RGB(0, 255, 0);

        $this->assertSame('00FF00', $color->getHexString());
    }

    public function factoryByHexString() {
        $color = CM_Color_RGB::factoryByHexString('00FF00');

        $this->assertSame(0, $color->getRed());
        $this->assertSame(255, $color->getGreen());
        $this->assertSame(0, $color->getBlue());
    }

    /**
     * @expectedException CM_Exception_Invalid
     */
    public function factoryByHexStringInvalid() {
        CM_Color_RGB::factoryByHexString('00ZZZZ');
    }

}
