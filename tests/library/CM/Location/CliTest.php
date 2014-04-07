<?php

class CM_Location_CliTest extends CMTest_TestCase {

    public function setUp() {
        foreach (array(
                     'cm_locationCityIp',
                     'cm_locationCountryIp',
                     'cm_locationZip',
                     'cm_locationCity',
                     'cm_locationState',
                     'cm_locationCountry',
                 ) as $locationTable) {
            CM_Db_Db::truncate($locationTable);
        }
    }

    public function testEmpty() {
        $this->_runTestCase(
            array(),
            array(),
            array(),
            array(),
            array(),
            array(),
            array(),
            array(),
            array(),
            array()
        );
    }

    protected function _runTestCase($countryDataMock, $regionDataMock, $locationDataMock, $ipDataMock, $countryDataExpected, $regionDataExpected, $cityDataExpected, $zipCodeDataExpected, $ipDataCountryExpected, $ipDataCityExpected) {
        $cmLocationCli = $this->getMock('CM_Location_Cli', array('_getCountryData', '_getRegionData', '_getLocationData', '_getIpData'));
        $cmLocationCli->expects($this->any())->method('_getCountryData')->will($this->returnValue($countryDataMock));
        $cmLocationCli->expects($this->any())->method('_getRegionData')->will($this->returnValue($regionDataMock));
        $cmLocationCli->expects($this->any())->method('_getLocationData')->will($this->returnValue($locationDataMock));
        $cmLocationCli->expects($this->any())->method('_getIpData')->will($this->returnValue($ipDataMock));
        /** @var CM_Location_Cli $cmLocationCli */
        $cmLocationCli->upgrade();
        $countryDataActual = CM_Db_Db::select('cm_locationCountry', '*')->fetchAll();
        $this->assertEquals($countryDataExpected, $countryDataActual);
        $regionDataActual = CM_Db_Db::select('cm_locationState', '*')->fetchAll();
        $this->assertEquals($regionDataExpected, $regionDataActual);
        $cityDataActual = CM_Db_Db::select('cm_locationCity', '*')->fetchAll();
        $this->assertEquals($cityDataExpected, $cityDataActual);
        $zipCodeDataActual = CM_Db_Db::select('cm_locationZip', '*')->fetchAll();
        $this->assertEquals($zipCodeDataExpected, $zipCodeDataActual);
        $ipDataCountryActual = CM_Db_Db::select('cm_locationCountryIp', '*')->fetchAll();
        $this->assertEquals($ipDataCountryExpected, $ipDataCountryActual);
        $ipDataCityActual = CM_Db_Db::select('cm_locationCityIp', '*')->fetchAll();
        $this->assertEquals($ipDataCityExpected, $ipDataCityActual);
    }
}
