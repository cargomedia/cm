<?php

class CM_FormField_GeoPointTest extends CMTest_TestCase {

    public function testValidate() {
        $field = new CM_FormField_GeoPoint(['name' => 'foo']);
        $environment = new CM_Frontend_Environment();
        $this->getMockForAbstractClass('CM_Response_Abstract', array(), '', false);

        $parsedInput = $field->parseUserInput(array('latitude' => -30.2, 'longitude' => -122.2));
        $field->validate($environment, $parsedInput);
        $this->assertSame(-30.2, $parsedInput->getLatitude());
        $this->assertSame(-122.2, $parsedInput->getLongitude());

        $parsedInput = $field->parseUserInput(array('latitude' => 30.2, 'longitude' => 122.2));
        $field->validate($environment, $parsedInput);
        $this->assertSame(30.2, $parsedInput->getLatitude());
        $this->assertSame(122.2, $parsedInput->getLongitude());
        $parsedInput = $field->parseUserInput(array('latitude' => 30.2, 'longitude' => 122.2));
        $field->validate($environment, $parsedInput);
        $this->assertSame(30.2, $parsedInput->getLatitude());
        $this->assertSame(122.2, $parsedInput->getLongitude());
    }
}
