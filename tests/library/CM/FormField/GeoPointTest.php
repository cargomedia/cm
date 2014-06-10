<?php

class CM_FormField_GeoPointTest extends CMTest_TestCase {

    public function testValidate() {
        $field = new CM_FormField_GeoPoint(['name' => 'foo']);
        $environment = new CM_Frontend_Environment();
        $this->getMockForAbstractClass('CM_Response_Abstract', array(), '', false);

        $value = $field->validate($environment, new CM_Geo_Point(-30.2, -122.2));
        $this->assertSame(-30.2, $value->getLatitude());
        $this->assertSame(-122.2, $value->getLongitude());

        $value = $field->validate($environment, new CM_Geo_Point(30.2, 122.2));
        $this->assertSame(30.2, $value->getLatitude());
        $this->assertSame(122.2, $value->getLongitude());

        $value = $field->validate($environment, new CM_Geo_Point(0, 0));
        $this->assertSame(0.0, $value->getLatitude());
        $this->assertSame(0.0, $value->getLongitude());
    }
}
