<?php

class CM_FormField_GeoPointTest extends CMTest_TestCase {

    public function testValidate() {
        $field = new CM_FormField_GeoPoint(['name' => 'foo']);
        $environment = new CM_Frontend_Environment();
        $response = $this->getMockForAbstractClass('CM_Response_Abstract', array(), '', false);

        $value = $field->validate($environment, array('latitude' => -30.2, 'longitude' => -122.2));
        $this->assertSame(-30.2, $value->getLatitude());
        $this->assertSame(-122.2, $value->getLongitude());

        $value = $field->validate($environment, array('latitude' => 30.2, 'longitude' => 122.2));
        $this->assertSame(30.2, $value->getLatitude());
        $this->assertSame(122.2, $value->getLongitude());

        $value = $field->validate($environment, array('latitude' => 0, 'longitude' => 0));
        $this->assertSame(0.0, $value->getLatitude());
        $this->assertSame(0.0, $value->getLongitude());

        try {
            $field->validate($environment, array('latitude' => 300, 'longitude' => 20));
            $this->fail('Out of range latitude passed validation');
        } catch (CM_Exception_FormFieldValidation $e) {
            $this->assertTrue(true);
        }

        try {
            $field->validate($environment, array('latitude' => -30.2, 'longitude' => 300));
            $this->fail('Out of range longitude passed validation');
        } catch (CM_Exception_FormFieldValidation $e) {
            $this->assertTrue(true);
        }

        try {
            $field->validate($environment, array('latitude' => 30));
            $this->fail('Missing longitude passed validation');
        } catch (CM_Exception_FormFieldValidation $e) {
            $this->assertTrue(true);
        }

        try {
            $field->validate($environment, array('latitude' => 30));
            $this->fail('Missing longitude passed validation');
        } catch (CM_Exception_FormFieldValidation $e) {
            $this->assertTrue(true);
        }

        try {
            $field->validate($environment, array('latitude' => 'foo', 'longitude' => 30));
            $this->fail('Non-numeric latitude passed validation');
        } catch (CM_Exception_FormFieldValidation $e) {
            $this->assertTrue(true);
        }

        try {
            $field->validate($environment, array('latitude' => 30, 'longitude' => 'foo'));
            $this->fail('Non-numeric longitude passed validation');
        } catch (CM_Exception_FormFieldValidation $e) {
            $this->assertTrue(true);
        }
    }
}
