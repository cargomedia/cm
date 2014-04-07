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
