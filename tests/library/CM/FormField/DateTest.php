<?php

class CM_FormField_DateTest extends CMTest_TestCase {

    public function testRenderWithEnvironmentTimezone() {
        $field = new CM_FormField_Date(['name' => 'foo']);
        $field->setValue(new DateTime('2015-03-02 01:00:00', new DateTimeZone('UTC')));

        $environment = new CM_Frontend_Environment(null, null, null, new DateTimeZone('America/New_York'));
        $render = new CM_Frontend_Render($environment);
        $doc = $this->_renderFormField($field, null, $render);

        $this->assertSame('selected', $doc->find('select.year option[value="2015"]')->getAttribute('selected'));
        $this->assertSame('selected', $doc->find('select.month option[value="3"]')->getAttribute('selected'));
        $this->assertSame('selected', $doc->find('select.day option[value="1"]')->getAttribute('selected'));
    }

    public function testValidateWithEnvironmentTimezone() {
        $formField = new CM_FormField_Date();

        $environment = new CM_Frontend_Environment(null, null, null, new DateTimeZone('Asia/Tokyo'));
        $value = $formField->validate($environment, ['year' => 2015, 'month' => 03, 'day' => 02]);
        $this->assertEquals(new DateTime('2015-03-02 00:00:00', new DateTimeZone('Asia/Tokyo')), $value);
    }
}
