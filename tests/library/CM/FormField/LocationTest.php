<?php

class CM_FormField_LocationTest extends CMTest_TestCase {

    public function testSetValueByRequest() {

        $location = CMTest_TH::createLocation();
        $locationCity = $location->get(CM_Model_Location::LEVEL_CITY);

        $mocka = new Mocka();
        $requestMockClass = $mocka->mockClass('CM_Request_Abstract');
        $requestMockClass->mockMethod('getLocation')
            ->at(0, function () {
                return null;
            })
            ->at(1, function () use ($location) {
                return $location->get(CM_Model_Location::LEVEL_COUNTRY);
            })
            ->at(2, function () use ($location) {
                return $location;
            })
            ->at(3, function () use ($locationCity) {
                return $locationCity;
            });
        $request = $requestMockClass->newInstance(['/foo/']);
        /** @var CM_Request_Abstract $request */

        $field = new CM_FormField_Location();
        $field->setValueByRequest($request);
        $this->assertNull($field->getValue());

        $field = new CM_FormField_Location(['levelMin' => CM_Model_Location::LEVEL_CITY]);
        $field->setValueByRequest($request);
        $this->assertNull($field->getValue());

        $field = new CM_FormField_Location(['levelMin' => CM_Model_Location::LEVEL_CITY, 'levelMax' => CM_Model_Location::LEVEL_CITY]);
        $field->setValueByRequest($request);
        $value = $field->getValue();
        /** @var CM_Model_Location $locationValue */
        $locationValue = $value[0];
        $this->assertSame($locationCity->getId(), $locationValue->getId());
        $this->assertSame($locationCity->getLevel(), $locationValue->getLevel());

        $field = new CM_FormField_Location(['levelMin' => CM_Model_Location::LEVEL_CITY, 'levelMax' => CM_Model_Location::LEVEL_CITY]);
        $field->setValueByRequest($request);
        $value = $field->getValue();
        /** @var CM_Model_Location $locationValue */
        $locationValue = $value[0];
        $this->assertSame($locationCity->getId(), $locationValue->getId());
        $this->assertSame($locationCity->getLevel(), $locationValue->getLevel());
    }
}
