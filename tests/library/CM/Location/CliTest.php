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
        $this->_import(
            array(),
            array(),
            array(),
            array()
        );
        $this->_verify(
            array(),
            array(),
            array(),
            array(),
            array(),
            array()
        );
    }

    public function testCountry() {
        $this->_import(
            array(
                array('France', 'FR'),
            ),
            array(),
            array(),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'FR', 'name' => 'France'),
            ),
            array(),
            array(),
            array(),
            array(),
            array()
        );
    }

    public function testRegion() {
        $this->_import(
            array(
                array('France', 'FR'),
            ),
            array(
                array('FR', 'A7', 'Haute-Normandie'),
            ),
            array(),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'FR', 'name' => 'France'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRA7', 'abbreviation' => null),
            ),
            array(),
            array(),
            array(),
            array()
        );
    }

    public function testCity() {
        $this->_import(
            array(
                array('France', 'FR'),
            ),
            array(
                array('FR', 'A7', 'Haute-Normandie'),
            ),
            array(
                array('50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'),
            ),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'FR', 'name' => 'France'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRA7', 'abbreviation' => null),
            ),
            array(
                array('id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'Le Havre', 'lat' => 49.5, 'lon' => 0.1333, '_maxmind' => 50221),
            ),
            array(),
            array(),
            array()
        );
    }

    public function testZipCode() {
        $this->_import(
            array(
                array('France', 'FR'),
            ),
            array(
                array('FR', 'A7', 'Haute-Normandie'),
            ),
            array(
                array('50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'),
                array('50221', 'FR', 'A7', 'Le Havre', '76620', '49.4938', '0.1077'),
            ),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'FR', 'name' => 'France'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRA7', 'abbreviation' => null),
            ),
            array(
                array('id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'Le Havre', 'lat' => 49.5, 'lon' => 0.1333, '_maxmind' => 50221),
            ),
            array(
                array('id' => 1, 'name' => '76620', 'cityId' => 1, 'lat' => 49.4938, 'lon' => 0.1077),
            ),
            array(),
            array()
        );
    }

    public function testIpBlockCountry() {
        $this->_import(
            array(
                array('France', 'FR'),
            ),
            array(),
            array(
                array('75', 'FR', '', '', '', '48.86', '2.35'),
            ),
            array(
                array('33555968', '33556223', '75'),
            )
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'FR', 'name' => 'France'),
            ),
            array(),
            array(),
            array(),
            array(
                array('countryId' => 1, 'ipStart' => 33555968, 'ipEnd' => 33556223),
            ),
            array()
        );
    }

    public function testIpBlockCity() {
        $this->_import(
            array(
                array('France', 'FR'),
            ),
            array(
                array('FR', 'A7', 'Haute-Normandie'),
            ),
            array(
                array('50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'),
            ),
            array(
                array('87097600', '87097855', '50221'),
            )
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'FR', 'name' => 'France'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRA7', 'abbreviation' => null),
            ),
            array(
                array('id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'Le Havre', 'lat' => 49.5, 'lon' => 0.1333, '_maxmind' => 50221),
            ),
            array(),
            array(),
            array(
                array('cityId' => 1, 'ipStart' => 87097600, 'ipEnd' => 87097855),
            )
        );
    }

    public function testImport() {
        $this->_import(
            array(
                array('France', 'FR'),
                array('United States', 'US'),
            ),
            array(
                array('FR', 'A7', 'Haute-Normandie'),
                array('US', 'CA', 'California'),
                array('US', 'HI', 'Hawaii'),
            ),
            array(
                array('75', 'FR', '', '', '', '48.86', '2.35'),
                array('50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'),
                array('50221', 'FR', 'A7', 'Le Havre', '76620', '49.4938', '0.1077'),
                array('223', 'US', '', '', '', '38', '-97'),
                array('11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'),
                array('608', 'US', 'CA', 'San Francisco', '94124', '37.7312', '-122.3826'),
                array('757', 'US', 'CA', 'San Francisco', '94105', '37.7898', '-122.3942'),
                array('11532', 'US', 'CA', 'Los Angeles', '', '34.0522', '-118.2437'),
            ),
            array(
                array('33555968', '33556223', '75'),
                array('87097600', '87097855', '50221'),
                array('266578176', '266578431', '223'),
                array('266586368', '266586623', '223'),
                array('68444672', '68444735', '11101'),
                array('68444800', '68444927', '11101'),
            )
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'FR', 'name' => 'France'),
                array('id' => 2, 'abbreviation' => 'US', 'name' => 'United States'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRA7', 'abbreviation' => null),
                array('id' => 2, 'countryId' => 2, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'),
                array('id' => 3, 'countryId' => 2, 'name' => 'Hawaii', '_maxmind' => 'USHI', 'abbreviation' => 'HI'),
            ),
            array(
                array('id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'Le Havre', 'lat' => 49.5, 'lon' => 0.1333, '_maxmind' => 50221),
                array('id' => 2, 'stateId' => 2, 'countryId' => 2, 'name' => 'Los Angeles', 'lat' => 34.0522, 'lon' => -118.244, '_maxmind' => 11532),
                array('id'       => 3, 'stateId' => 2, 'countryId' => 2, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419,
                      '_maxmind' => 11101),
            ),
            array(
                array('id' => 1, 'name' => '76620', 'cityId' => 1, 'lat' => 49.4938, 'lon' => 0.1077),
                array('id' => 2, 'name' => '94105', 'cityId' => 3, 'lat' => 37.7898, 'lon' => -122.394),
                array('id' => 3, 'name' => '94124', 'cityId' => 3, 'lat' => 37.7312, 'lon' => -122.383),
            ),
            array(
                array('countryId' => 1, 'ipStart' => 33555968, 'ipEnd' => 33556223),
                array('countryId' => 2, 'ipStart' => 266578176, 'ipEnd' => 266578431),
                array('countryId' => 2, 'ipStart' => 266586368, 'ipEnd' => 266586623),
            ),
            array(
                array('cityId' => 1, 'ipStart' => 87097600, 'ipEnd' => 87097855),
                array('cityId' => 3, 'ipStart' => 68444672, 'ipEnd' => 68444735),
                array('cityId' => 3, 'ipStart' => 68444800, 'ipEnd' => 68444927),
            )
        );
    }

    public function testAddCountry() {
        $this->_import(
            array(
                array('France', 'FR'),
            ),
            array(),
            array(),
            array()
        );
        $this->_import(
            array(
                array('France', 'FR'),
                array('United States', 'US'),
            ),
            array(),
            array(),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'FR', 'name' => 'France'),
                array('id' => 2, 'abbreviation' => 'US', 'name' => 'United States'),
            ),
            array(),
            array(),
            array(),
            array(),
            array()
        );
    }

    protected function _import($countryDataMock, $regionDataMock, $locationDataMock, $ipDataMock) {
        $cmLocationCli = $this->getMock('CM_Location_Cli', array('_getCountryData', '_getRegionData', '_getLocationData', '_getIpData'));
        $cmLocationCli->expects($this->any())->method('_getCountryData')->will($this->returnValue($countryDataMock));
        $cmLocationCli->expects($this->any())->method('_getRegionData')->will($this->returnValue($regionDataMock));
        $cmLocationCli->expects($this->any())->method('_getLocationData')->will($this->returnValue($locationDataMock));
        $cmLocationCli->expects($this->any())->method('_getIpData')->will($this->returnValue($ipDataMock));
        /** @var CM_Location_Cli $cmLocationCli */
        $cmLocationCli->upgrade();
    }

    protected function _verify($countryDataExpected, $regionDataExpected, $cityDataExpected, $zipCodeDataExpected, $ipDataCountryExpected, $ipDataCityExpected) {
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
