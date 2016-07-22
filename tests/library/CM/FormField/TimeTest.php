<?php

class CM_FormField_TimeTest extends CMTest_TestCase {

    public function testValidate() {
        $formField = new CM_FormField_Time();

        $environment = new CM_Frontend_Environment();
        $environment->setTimeZone(new DateTimeZone('Asia/Tokyo'));
        $value = $formField->validate($environment, '12:30');
        $expected = new DateInterval('PT12H30M');
        $this->assertEquals($expected, $value);
    }

    public function testRenderDateInterval() {
        $value = new DateInterval('PT12H30M');
        $formField = new CM_FormField_Time();
        $formField->setValue($value);

        $environment = new CM_Frontend_Environment();
        $environment->setTimeZone(new DateTimeZone('Etc/GMT+2'));
        $render = new CM_Frontend_Render($environment);
        $renderAdapter = new CM_RenderAdapter_FormField($render, $formField);

        $actual = $renderAdapter->fetch(new CM_Params());
        $this->assertContains('value="10:30:00"', $actual);
    }

    public function testRenderDateTime() {
        $value = new DateTime('2016-05-31T12:30:00Z+00:00');
        $formField = new CM_FormField_Time();
        $formField->setValue($value);

        $environment = new CM_Frontend_Environment();
        $environment->setTimeZone(new DateTimeZone('Etc/GMT+2'));
        $render = new CM_Frontend_Render($environment);
        $renderAdapter = new CM_RenderAdapter_FormField($render, $formField);

        $actual = $renderAdapter->fetch(new CM_Params());
        $this->assertContains('value="10:30:00"', $actual);
    }
}
