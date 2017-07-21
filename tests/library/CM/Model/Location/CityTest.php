<?php

class CM_Model_Location_CityTest extends CMTest_TestCase {

    public function testFactoryFromMaxMindId() {
        $country = CM_Model_Location_Country::create('NeverLand', 'NVD');

        CM_Model_Location_City::create($country, null, 'City0', null, null, 56);
        $locationCity = CM_Model_Location_City::create($country, null, 'City', null, null, 123456);
        CM_Model_Location_City::create($country, null, 'City2', null, null, 3456);
        $this->assertInstanceOf(CM_Model_Location_City::class, $locationCity);

        $foundCity = CM_Model_Location_City::factoryFromMaxMindId(123456);
        $this->assertEquals($locationCity, $foundCity);
        $this->assertSame(null, CM_Model_Location_City::factoryFromMaxMindId(65432));
    }
}
