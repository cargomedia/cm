<?php

class CM_FormField_LocationTest extends CMTest_TestCase {

    /**
     * @expectedException CM_Exception_FormFieldValidation
     */
    public function testSetValueByRequest() {
        $request = new CM_Request_Get('/fuu/');
        $location = CMTest_TH::createLocation();
        $locationCity = $location->get(CM_Model_Location::LEVEL_CITY);

        $field = $this->getMock('CM_FormField_Location', array('_getRequestLocationByRequest'), array(['name' => 'foo']));
        $field->expects($this->any())->method('_getRequestLocationByRequest')->will($this->returnValue(null));
        /** @var CM_FormField_Location $field */
        $field->setValueByRequest($request);
        $this->assertNull($field->getValue());

        $field = $this->getMock('CM_FormField_Location', array('_getRequestLocationByRequest'), array(['name'     => 'foo',
                                                                                                       'levelMin' => CM_Model_Location::LEVEL_CITY,
                                                                                                       'levelMax' => CM_Model_Location::LEVEL_CITY]));
        $field->expects($this->any())->method('_getRequestLocationByRequest')->will($this->returnValue($location->get(CM_Model_Location::LEVEL_COUNTRY)));
        $field->setValueByRequest($request);
        $this->assertNull($field->getValue());

        $field = $this->getMock('CM_FormField_Location', array('_getRequestLocationByRequest'), array(['name'     => 'foo',
                                                                                                       'levelMin' => CM_Model_Location::LEVEL_CITY,
                                                                                                       'levelMax' => CM_Model_Location::LEVEL_CITY]));
        $field->expects($this->any())->method('_getRequestLocationByRequest')->will($this->returnValue($location));
        $field->setValueByRequest($request);
        $value = $field->getValue();
        /** @var CM_Model_Location $locationValue */
        $locationValue = $value[0];
        $this->assertSame($locationCity->getId(), $locationValue->getId());
        $this->assertSame($locationCity->getLevel(), $locationValue->getLevel());

        $field = $this->getMock('CM_FormField_Location', array('_getRequestLocationByRequest'), array(['name'     => 'foo',
                                                                                                       'levelMin' => CM_Model_Location::LEVEL_CITY,
                                                                                                       'levelMax' => CM_Model_Location::LEVEL_CITY]));
        $field->expects($this->any())->method('_getRequestLocationByRequest')->will($this->returnValue($locationCity));
        $field->setValueByRequest($request);
        $value = $field->getValue();
        /** @var CM_Model_Location $locationValue */
        $locationValue = $value[0];
        $this->assertSame($locationCity->getId(), $locationValue->getId());
        $this->assertSame($locationCity->getLevel(), $locationValue->getLevel());

        $environment = new CM_Frontend_Environment();
        $field->validate($environment, 'foo');
    }

    public function testParseUserInput() {
        $location = CMTest_TH::createLocation(CM_Model_Location::LEVEL_CITY);
        $field = new CM_FormField_Location();
        $parsedInput = $field->parseUserInput(CM_Model_Location::LEVEL_CITY . '.' . $location->getId());
        $this->assertInstanceOf('CM_Model_Location', $parsedInput);
    }
}
