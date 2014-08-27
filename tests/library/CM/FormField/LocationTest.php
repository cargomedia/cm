<?php

class CM_FormField_LocationTest extends CMTest_TestCase {

    public function testSetValueByEnvironment() {
        $location = CMTest_TH::createLocation();
        $locationCity = $location->get(CM_Model_Location::LEVEL_CITY);

        $environment = new CM_Frontend_Environment();
        $environment->setLocation(null);
        $field = new CM_FormField_Location();
        $field->setValueByEnvironment($environment);
        $this->assertNull($field->getValue());

        $environment->setLocation($location->get(CM_Model_Location::LEVEL_COUNTRY));
        $field = new CM_FormField_Location(['levelMin' => CM_Model_Location::LEVEL_CITY]);
        $field->setValueByEnvironment($environment);
        $this->assertNull($field->getValue());

        $environment->setLocation($location);
        $field = new CM_FormField_Location(['levelMin' => CM_Model_Location::LEVEL_CITY, 'levelMax' => CM_Model_Location::LEVEL_CITY]);
        $field->setValueByEnvironment($environment);
        $value = $field->getValue();
        /** @var CM_Model_Location $locationValue */
        $locationValue = $value[0];
        $this->assertSame($locationCity->getId(), $locationValue->getId());
        $this->assertSame($locationCity->getLevel(), $locationValue->getLevel());

        $environment->setLocation($locationCity);
        $field = new CM_FormField_Location(['levelMin' => CM_Model_Location::LEVEL_CITY, 'levelMax' => CM_Model_Location::LEVEL_CITY]);
        $field->setValueByEnvironment($environment);
        $value = $field->getValue();
        /** @var CM_Model_Location $locationValue */
        $locationValue = $value[0];
        $this->assertSame($locationCity->getId(), $locationValue->getId());
        $this->assertSame($locationCity->getLevel(), $locationValue->getLevel());
    }

    public function testParseUserInput() {
        $location = CMTest_TH::createLocation(CM_Model_Location::LEVEL_CITY);
        $field = new CM_FormField_Location();
        $parsedInput = $field->parseUserInput(CM_Model_Location::LEVEL_CITY . '.' . $location->getId());
        $this->assertInstanceOf('CM_Model_Location', $parsedInput);
    }

    public function testValidate() {
        $environment = new CM_Frontend_Environment();
        $location = CMTest_TH::createLocation(CM_Model_Location::LEVEL_CITY);
        $field = new CM_FormField_Location();
        $field->validate($environment, $location);
    }
}
