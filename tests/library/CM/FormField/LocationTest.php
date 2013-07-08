<?php

class CM_FormField_UrlTest extends CMTest_TestCase {

	public static function setUpBeforeClass() {
		$switzerland = CM_Db_Db::insert(TBL_CM_LOCATIONCOUNTRY, array('abbreviation' => 'CH', 'name' => 'Switzerland'));
		$baselStadt = CM_Db_Db::insert(TBL_CM_LOCATIONSTATE, array('countryId' => $switzerland, 'name' => 'Basel-Stadt'));
		$basel = CM_Db_Db::insert(TBL_CM_LOCATIONCITY, array('stateId' => $baselStadt, 'countryId' => $switzerland, 'name' => 'Basel',
															 'lat'     => 47.569535, 'lon' => 7.574063));
		CM_Db_Db::insert(TBL_CM_LOCATIONZIP, array('cityId' => $basel, 'name' => '4057', 'lat' => 47.574155, 'lon' => 7.592993));
	}

	public function testSetValueByRequest() {
		CM_Config::get()->testIp = '0.0.0.1';
		$cityId = (int) CM_Db_Db::getRandId(TBL_CM_LOCATIONCITY, 'id');
		CM_Db_Db::insert(TBL_CM_LOCATIONCITYIP, array('ipStart' => 1, 'ipEnd' => 1, 'cityId' => $cityId));
		$countryId = (int) CM_Db_Db::getRandId(TBL_CM_LOCATIONCOUNTRY, 'id');
		CM_Db_Db::insert(TBL_CM_LOCATIONCOUNTRYIP, array('ipStart' => 1, 'ipEnd' => 1, 'countryId' => $countryId));

		$field = new CM_FormField_Location('foo', CM_Model_Location::LEVEL_CITY, CM_Model_Location::LEVEL_CITY);
		$request = new CM_Request_Get('/fuu/');
		$field->setValueByRequest($request);
		$value = $field->getValue();
		/** @var CM_Model_Location $location */
		$location = $value[0];
		$this->assertSame($location->getId(), $cityId);

		$field = new CM_FormField_Location('foo', CM_Model_Location::LEVEL_COUNTRY, CM_Model_Location::LEVEL_COUNTRY);
		$request = new CM_Request_Get('/fuu/');
		$field->setValueByRequest($request);
		$value = $field->getValue();
		/** @var CM_Model_Location $location */
		$location = $value[0];
		$this->assertSame($location->getId(), $countryId);
	}
}
