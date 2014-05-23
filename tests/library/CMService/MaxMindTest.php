<?php

class CMService_MaxMindTest extends CMTest_TestCase {

    public function setUp() {
        CM_Db_Db::exec('ALTER TABLE cm_model_location_city_ip AUTO_INCREMENT = 1');
        CM_Db_Db::exec('ALTER TABLE cm_model_location_country_ip AUTO_INCREMENT = 1');
        CM_Db_Db::exec('ALTER TABLE cm_model_location_zip AUTO_INCREMENT = 1');
        CM_Db_Db::exec('ALTER TABLE cm_model_location_city AUTO_INCREMENT = 1');
        CM_Db_Db::exec('ALTER TABLE cm_model_location_state AUTO_INCREMENT = 1');
        CM_Db_Db::exec('ALTER TABLE cm_model_location_country AUTO_INCREMENT = 1');
    }

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testEmpty() {
        $this->_import(
            array(),
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
            array(
                array('75', 'FR', '', '', '', '48.86', '2.35'),
            ),
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

    public function testCountry_withoutLocation() {
        $this->_import(
            array(
                array('France', 'FR'),
            ),
            array(),
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

    public function testCountry_unknown() {
        $this->_import(
            array(),
            array(),
            array(
                array('75', 'FR', '', '', '', '48.86', '2.35'),
            ),
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

    public function testRegion() {
        $this->_import(
            array(
                array('France', 'FR'),
            ),
            array(
                array('FR', 'A7', 'Haute-Normandie'),
            ),
            array(
                array('75', 'FR', '', '', '', '48.86', '2.35'),
                array('436884', 'FR', 'A7', '', '', '49.4333', '1.0833'),
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

    public function testRegion_withoutLocation() {
        $this->_import(
            array(
                array('France', 'FR'),
            ),
            array(
                array('FR', 'A7', 'Haute-Normandie'),
            ),
            array(
                array('75', 'FR', '', '', '', '48.86', '2.35'),
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

    public function testRegion_unknown() {
        $this->_import(
            array(
                array('France', 'FR'),
            ),
            array(),
            array(
                array('75', 'FR', '', '', '', '48.86', '2.35'),
                array('436884', 'FR', 'A7', '', '', '49.4333', '1.0833'),
            ),
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

    public function testRegion_legacy() {
        $this->_import(
            array(
                array('France', 'FR'),
            ),
            array(),
            array(
                array('75', 'FR', '', '', '', '48.86', '2.35'),
                array('436884', 'FR', 'A7', '', '', '49.4333', '1.0833'),
            ),
            array(),
            array(
                'FR' => array(
                    'A7' => 'Haute-Normandie',
                ),
            )
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

    public function testRegion_keepMissing() {
        $this->_import(
            array(
                array('France', 'FR'),
            ),
            array(
                array('FR', 'A7', 'Haute-Normandie'),
            ),
            array(
                array('75', 'FR', '', '', '', '48.86', '2.35'),
                array('436884', 'FR', 'A7', '', '', '49.4333', '1.0833'),
                array('50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'),
                array('384603', 'FR', 'A7', 'Le Havre', '76620', '49.4938', '0.1077'),
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
            array(
                array('id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'Le Havre', 'lat' => 49.5, 'lon' => 0.1333, '_maxmind' => 50221),
            ),
            array(
                array('id' => 1, 'name' => '76620', 'cityId' => 1, 'lat' => 49.4938, 'lon' => 0.1077),
            ),
            array(),
            array()
        );
        $this->_import(
            array(
                array('France', 'FR'),
            ),
            array(),
            array(
                array('75', 'FR', '', '', '', '48.86', '2.35'),
                array('436884', 'FR', 'A7', '', '', '49.4333', '1.0833'),
                array('50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'),
                array('384603', 'FR', 'A7', 'Le Havre', '76620', '49.4938', '0.1077'),
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

    public function testRegion_addLegacy() {
        $this->_import(
            array(
                array('France', 'FR'),
            ),
            array(),
            array(
                array('75', 'FR', '', '', '', '48.86', '2.35'),
                array('436884', 'FR', 'A7', '', '', '49.4333', '1.0833'),
                array('50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'),
                array('384603', 'FR', 'A7', 'Le Havre', '76620', '49.4938', '0.1077'),
            ),
            array(),
            array(
                'FR' => array(
                    'A7' => 'Haute-Normandie',
                ),
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
            array(
                array('id' => 1, 'name' => '76620', 'cityId' => 1, 'lat' => 49.4938, 'lon' => 0.1077),
            ),
            array(),
            array()
        );
    }

    public function testRegion_countryWithoutLocation() {
        $this->_import(
            array(
                array('France', 'FR'),
            ),
            array(
                array('FR', 'A7', 'Haute-Normandie'),
            ),
            array(
                array('436884', 'FR', 'A7', '', '', '49.4333', '1.0833'),
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
                array('75', 'FR', '', '', '', '48.86', '2.35'),
                array('436884', 'FR', 'A7', '', '', '49.4333', '1.0833'),
                array('50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'),
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
            array(
                array('id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'Le Havre', 'lat' => 49.5, 'lon' => 0.1333, '_maxmind' => 50221),
            ),
            array(),
            array(),
            array()
        );
    }

    public function testCity_withoutRegion() {
        $this->_import(
            array(
                array('France', 'FR'),
            ),
            array(),
            array(
                array('75', 'FR', '', '', '', '48.86', '2.35'),
                array('50221', 'FR', '', 'Le Havre', '', '49.5', '0.1333'),
            ),
            array(),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'FR', 'name' => 'France'),
            ),
            array(),
            array(
                array('id' => 1, 'stateId' => null, 'countryId' => 1, 'name' => 'Le Havre', 'lat' => 49.5, 'lon' => 0.1333, '_maxmind' => 50221),
            ),
            array(),
            array(),
            array()
        );
    }

    public function testCity_unknownRegion() {
        $this->_import(
            array(
                array('France', 'FR'),
            ),
            array(),
            array(
                array('75', 'FR', '', '', '', '48.86', '2.35'),
                array('436884', 'FR', 'A7', '', '', '49.4333', '1.0833'),
                array('50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'),
            ),
            array(),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'FR', 'name' => 'France'),
            ),
            array(),
            array(
                array('id' => 1, 'stateId' => null, 'countryId' => 1, 'name' => 'Le Havre', 'lat' => 49.5, 'lon' => 0.1333, '_maxmind' => 50221),
            ),
            array(),
            array(),
            array()
        );
    }

    public function testCity_legacyRegion() {
        $this->_import(
            array(
                array('France', 'FR'),
            ),
            array(),
            array(
                array('75', 'FR', '', '', '', '48.86', '2.35'),
                array('436884', 'FR', 'A7', '', '', '49.4333', '1.0833'),
                array('50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'),
            ),
            array(),
            array(
                'FR' => array(
                    'A7' => 'Haute-Normandie',
                ),
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
                array('75', 'FR', '', '', '', '48.86', '2.35'),
                array('436884', 'FR', 'A7', '', '', '49.4333', '1.0833'),
                array('50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'),
                array('384603', 'FR', 'A7', 'Le Havre', '76620', '49.4938', '0.1077'),
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

    public function testZipCode_unknownRegion() {
        $this->_import(
            array(
                array('France', 'FR'),
            ),
            array(),
            array(
                array('75', 'FR', '', '', '', '48.86', '2.35'),
                array('50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'),
                array('384603', 'FR', 'A7', 'Le Havre', '76620', '49.4938', '0.1077'),
                array('385175', 'FR', 'B8', 'Marseille', '13000', '43.3', '5.4'),
            ),
            array(),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'FR', 'name' => 'France'),
            ),
            array(),
            array(
                array('id' => 1, 'stateId' => null, 'countryId' => 1, 'name' => 'Le Havre', 'lat' => 49.5, 'lon' => 0.1333, '_maxmind' => 50221),
                array('id' => 2, 'stateId' => null, 'countryId' => 1, 'name' => 'Marseille', 'lat' => 43.3, 'lon' => 5.4, '_maxmind' => 385175),
            ),
            array(
                array('id' => 1, 'name' => '76620', 'cityId' => 1, 'lat' => 49.4938, 'lon' => 0.1077),
                array('id' => 2, 'name' => '13000', 'cityId' => 2, 'lat' => 43.3, 'lon' => 5.4),
            ),
            array(),
            array()
        );
    }

    public function testZipCode_legacyRegion() {
        $this->_import(
            array(
                array('France', 'FR'),
            ),
            array(),
            array(
                array('75', 'FR', '', '', '', '48.86', '2.35'),
                array('50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'),
                array('384603', 'FR', 'A7', 'Le Havre', '76620', '49.4938', '0.1077'),
                array('385175', 'FR', 'B8', 'Marseille', '13000', '43.3', '5.4'),
            ),
            array(),
            array(
                'FR' => array(
                    'A7' => 'Haute-Normandie',
                    'B8' => 'Provence-Alpes-Cote d\'Azur',
                ),
            )
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'FR', 'name' => 'France'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRA7', 'abbreviation' => null),
                array('id' => 2, 'countryId' => 1, 'name' => 'Provence-Alpes-Cote d\'Azur', '_maxmind' => 'FRB8', 'abbreviation' => null),
            ),
            array(
                array('id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'Le Havre', 'lat' => 49.5, 'lon' => 0.1333, '_maxmind' => 50221),
                array('id' => 2, 'stateId' => 2, 'countryId' => 1, 'name' => 'Marseille', 'lat' => 43.3, 'lon' => 5.4, '_maxmind' => 385175),
            ),
            array(
                array('id' => 1, 'name' => '76620', 'cityId' => 1, 'lat' => 49.4938, 'lon' => 0.1077),
                array('id' => 2, 'name' => '13000', 'cityId' => 2, 'lat' => 43.3, 'lon' => 5.4),
            ),
            array(),
            array()
        );
    }

    public function testZipCode_withoutCity() {
        $this->_import(
            array(
                array('France', 'FR'),
            ),
            array(
                array('FR', 'A7', 'Haute-Normandie'),
            ),
            array(
                array('75', 'FR', '', '', '', '48.86', '2.35'),
                array('436884', 'FR', 'A7', '', '', '49.4333', '1.0833'),
                array('384603', 'FR', 'A7', 'Le Havre', '76620', '49.4938', '0.1077'),
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
            array(
                array('id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'Le Havre', 'lat' => 49.4938, 'lon' => 0.1077, '_maxmind' => 384603),
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
            ),
            array()
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
                array('75', 'FR', '', '', '', '48.86', '2.35'),
                array('436884', 'FR', 'A7', '', '', '49.4333', '1.0833'),
                array('50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'),
            ),
            array(
                array('87097600', '87097855', '50221'),
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
                array('436884', 'FR', 'A7', '', '', '49.4333', '1.0833'),
                array('50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'),
                array('384603', 'FR', 'A7', 'Le Havre', '76620', '49.4938', '0.1077'),
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
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
            ),
            array()
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
                array('United States', 'US'),
            ),
            array(),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
            ),
            array(),
            array()
        );
        $this->_import(
            array(
                array('France', 'FR'),
                array('United States', 'US'),
            ),
            array(),
            array(
                array('75', 'FR', '', '', '', '48.86', '2.35'),
                array('223', 'US', '', '', '', '38', '-97'),
            ),
            array(),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'US', 'name' => 'United States'),
                array('id' => 2, 'abbreviation' => 'FR', 'name' => 'France'),
            ),
            array(),
            array(),
            array(),
            array(),
            array()
        );
    }

    public function testRemoveCountry() {
        $this->_import(
            array(
                array('France', 'FR'),
                array('United States', 'US'),
            ),
            array(
                array('FR', 'A7', 'Haute-Normandie'),
            ),
            array(
                array('75', 'FR', '', '', '', '48.86', '2.35'),
                array('436884', 'FR', 'A7', '', '', '49.4333', '1.0833'),
                array('50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'),
                array('49739', 'FR', '', 'Marseille', '', '43.2854', '5.3761'),
                array('223', 'US', '', '', '', '38', '-97'),
            ),
            array(
                array('33555968', '33556223', '75'),
                array('87097600', '87097855', '50221'),
                array('33818880', '33819135', '49739'),
            ),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'FR', 'name' => 'France'),
                array('id' => 2, 'abbreviation' => 'US', 'name' => 'United States'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRA7', 'abbreviation' => null),
            ),
            array(
                array('id' => 1, 'stateId' => null, 'countryId' => 1, 'name' => 'Marseille', 'lat' => 43.2854, 'lon' => 5.3761, '_maxmind' => 49739),
                array('id' => 2, 'stateId' => 1, 'countryId' => 1, 'name' => 'Le Havre', 'lat' => 49.5, 'lon' => 0.1333, '_maxmind' => 50221),
            ),
            array(),
            array(
                array('countryId' => 1, 'ipStart' => 33555968, 'ipEnd' => 33556223),
            ),
            array(
                array('cityId' => 2, 'ipStart' => 87097600, 'ipEnd' => 87097855),
                array('cityId' => 1, 'ipStart' => 33818880, 'ipEnd' => 33819135),
            )
        );
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
            ),
            array(),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'FR', 'name' => 'France'),
                array('id' => 2, 'abbreviation' => 'US', 'name' => 'United States'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRA7', 'abbreviation' => null),
            ),
            array(
                array('id' => 1, 'stateId' => null, 'countryId' => 1, 'name' => 'Marseille', 'lat' => 43.2854, 'lon' => 5.3761, '_maxmind' => 49739),
                array('id' => 2, 'stateId' => 1, 'countryId' => 1, 'name' => 'Le Havre', 'lat' => 49.5, 'lon' => 0.1333, '_maxmind' => 50221),
            ),
            array(),
            array(),
            array()
        );
    }

    public function testUpdateCountryName() {
        $this->_import(
            array(
                array('France', 'FR'),
            ),
            array(),
            array(
                array('75', 'FR', '', '', '', '48.86', '2.35'),
            ),
            array(),
            array()
        );
        $this->_import(
            array(
                array('République Française', 'FR'),
            ),
            array(),
            array(
                array('75', 'FR', '', '', '', '48.86', '2.35'),
            ),
            array(),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'FR', 'name' => 'République Française'),
            ),
            array(),
            array(),
            array(),
            array(),
            array()
        );
    }

    public function testAddRegion() {
        $this->_import(
            array(
                array('France', 'FR'),
                array('United States', 'US'),
            ),
            array(
                array('US', 'CA', 'California'),
            ),
            array(
                array('75', 'FR', '', '', '', '48.86', '2.35'),
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
            ),
            array(),
            array()
        );
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
                array('436884', 'FR', 'A7', '', '', '49.4333', '1.0833'),
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('14550', 'US', 'HI', '', '', '21.3629', '-157.8727'),
            ),
            array(),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'FR', 'name' => 'France'),
                array('id' => 2, 'abbreviation' => 'US', 'name' => 'United States'),
            ),
            array(
                array('id' => 1, 'countryId' => 2, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'),
                array('id' => 2, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRA7', 'abbreviation' => null),
                array('id' => 3, 'countryId' => 2, 'name' => 'Hawaii', '_maxmind' => 'USHI', 'abbreviation' => 'HI'),
            ),
            array(),
            array(),
            array(),
            array()
        );
    }

    public function testAddRegion_duplicate() {
        $this->_import(
            array(
                array('France', 'FR'),
                array('United States', 'US'),
            ),
            array(
                array('FR', 'A7', 'Haute-Normandie'),
                array('US', 'CA', 'California'),
            ),
            array(
                array('75', 'FR', '', '', '', '48.86', '2.35'),
                array('436884', 'FR', 'A7', '', '', '49.4333', '1.0833'),
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'),
            ),
            array(),
            array()
        );
        $this->_import(
            array(
                array('France', 'FR'),
                array('United States', 'US'),
            ),
            array(
                array('FR', 'A7', 'Haute-Normandie'),
                array('FR', 'A8', 'Haute-Normandie'),
                array('US', 'CA', 'California'),
                array('US', 'CB', 'California'),
            ),
            array(
                array('75', 'FR', '', '', '', '48.86', '2.35'),
                array('436884', 'FR', 'A7', '', '', '49.4333', '1.0833'),
                array('436884', 'FR', 'A8', '', '', '49.4333', '1.0833'),
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'),
                array('2221', 'US', 'CB', '', '', '34.0522', '-118.243'),
            ),
            array(),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'FR', 'name' => 'France'),
                array('id' => 2, 'abbreviation' => 'US', 'name' => 'United States'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRA7', 'abbreviation' => null),
                array('id' => 2, 'countryId' => 2, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'),
                array('id' => 3, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRA8', 'abbreviation' => null),
                array('id' => 4, 'countryId' => 2, 'name' => 'California', '_maxmind' => 'USCB', 'abbreviation' => 'CB'),
            ),
            array(
                array('id'       => 1, 'stateId' => 2, 'countryId' => 2, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419,
                      '_maxmind' => 11101),
            ),
            array(),
            array(),
            array()
        );
    }

    public function testRemoveRegion() {
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
                array('436884', 'FR', 'A7', '', '', '49.4333', '1.0833'),
                array('50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'),
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('14550', 'US', 'HI', '', '', '21.3629', '-157.8727'),
            ),
            array(),
            array()
        );
        $this->_import(
            array(
                array('France', 'FR'),
                array('United States', 'US'),
            ),
            array(
                array('US', 'CA', 'California'),
            ),
            array(
                array('75', 'FR', '', '', '', '48.86', '2.35'),
                array('50221', 'FR', '', 'Le Havre', '', '49.5', '0.1333'),
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
            ),
            array(),
            array()
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
                array('id' => 1, 'stateId' => null, 'countryId' => 1, 'name' => 'Le Havre', 'lat' => 49.5, 'lon' => 0.1333, '_maxmind' => 50221),
            ),
            array(),
            array(),
            array()
        );
    }

    public function testRemoveRegion_codeInUse() {
        $this->_import(
            array(
                array('France', 'FR'),
                array('United States', 'US'),
                array('Viet Nam', 'VN'),
            ),
            array(
                array('FR', 'A7', 'Haute-Normandie'),
                array('FR', '99', 'Basse-Normandie'),
                array('US', 'CA', 'California'),
                array('US', 'HI', 'Hawaii'),
                array('VN', '44', 'Dac Lac'),
                array('VN', '51', 'Ha Noi'),
            ),
            array(
                array('75', 'FR', '', '', '', '48.86', '2.35'),
                array('436884', 'FR', 'A7', '', '', '49.4333', '1.0833'),
                array('50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'),
                array('436884', 'FR', '99', '', '', '49.1972', '-0.3268'),
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('5718', 'US', 'CA', 'Las Vegas', '', '36.175', '-115.1372'),
                array('23653', 'US', 'CA', 'Long Beach', '', '33.767', '-118.1892'),
                array('11532', 'US', 'CA', 'Los Angeles', '', '34.0522', '-118.2437'),
                array('11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'),
                array('14550', 'US', 'HI', '', '', '21.3629', '-157.8727'),
                array('231', 'VN', '', '', '', '16', '106'),
                array('46410', 'VN', '44', '', '', '21.033', '105.85'),
                array('46418', 'VN', '44', 'Hanoi', '', '21.033', '105.85'),
                array('412930', 'VN', '51', '', '', '21.033', '105.85'),
            ),
            array(),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'FR', 'name' => 'France'),
                array('id' => 2, 'abbreviation' => 'US', 'name' => 'United States'),
                array('id' => 3, 'abbreviation' => 'VN', 'name' => 'Viet Nam'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'Basse-Normandie', '_maxmind' => 'FR99', 'abbreviation' => null),
                array('id' => 2, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRA7', 'abbreviation' => null),
                array('id' => 3, 'countryId' => 2, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'),
                array('id' => 4, 'countryId' => 2, 'name' => 'Hawaii', '_maxmind' => 'USHI', 'abbreviation' => 'HI'),
                array('id' => 5, 'countryId' => 3, 'name' => 'Dac Lac', '_maxmind' => 'VN44', 'abbreviation' => null),
                array('id' => 6, 'countryId' => 3, 'name' => 'Ha Noi', '_maxmind' => 'VN51', 'abbreviation' => null),
            ),
            array(
                array('id' => 1, 'stateId' => 2, 'countryId' => 1, 'name' => 'Le Havre', 'lat' => 49.5, 'lon' => 0.1333, '_maxmind' => 50221),
                array('id' => 2, 'stateId' => 3, 'countryId' => 2, 'name' => 'Las Vegas', 'lat' => 36.175, 'lon' => -115.137, '_maxmind' => 5718),
                array('id' => 3, 'stateId' => 3, 'countryId' => 2, 'name' => 'Long Beach', 'lat' => 33.767, 'lon' => -118.189, '_maxmind' => 23653),
                array('id' => 4, 'stateId' => 3, 'countryId' => 2, 'name' => 'Los Angeles', 'lat' => 34.0522, 'lon' => -118.244, '_maxmind' => 11532),
                array('id'       => 5, 'stateId' => 3, 'countryId' => 2, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419,
                      '_maxmind' => 11101),
                array('id' => 6, 'stateId' => 5, 'countryId' => 3, 'name' => 'Hanoi', 'lat' => 21.033, 'lon' => 105.85, '_maxmind' => 46418),
            ),
            array(),
            array(),
            array()
        );
        $this->_import(
            array(
                array('France', 'FR'),
                array('United States', 'US'),
                array('Viet Nam', 'VN'),
            ),
            array(
                array('FR', 'A7', 'Basse-Normandie'),
                array('US', 'CA', 'Hawaii'),
                array('US', 'NV', 'Nevada'),
                array('VN', '44', 'Ha Noi'),
            ),
            array(
                array('75', 'FR', '', '', '', '48.86', '2.35'),
                array('436884', 'FR', 'A7', '', '', '49.1972', '-0.3268'),
                array('50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'),
                array('223', 'US', '', '', '', '38', '-97'),
                array('14550', 'US', 'CA', '', '', '21.3629', '-157.8727'),
                array('8029', 'US', 'NV', '', '', '36.175', '-115.1372'),
                array('23653', 'US', 'NV', 'Very Long Beach', '', '33.767', '-118.1892'),
                array('11532', 'US', '', 'Los Angeles', '', '34.0522', '-118.2437'),
                array('11101', 'US', 'NV', 'San Francisco', '', '37.7749', '-122.4194'),
                array('231', 'VN', '', '', '', '16', '106'),
                array('46418', 'VN', '44', 'Hanoi', '', '21.033', '105.85'),
                array('412930', 'VN', '44', '', '', '21.033', '105.85'),
            ),
            array(),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'FR', 'name' => 'France'),
                array('id' => 2, 'abbreviation' => 'US', 'name' => 'United States'),
                array('id' => 3, 'abbreviation' => 'VN', 'name' => 'Viet Nam'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'Basse-Normandie', '_maxmind' => 'FRA7', 'abbreviation' => null),
                array('id' => 4, 'countryId' => 2, 'name' => 'Hawaii', '_maxmind' => 'USCA', 'abbreviation' => 'CA'),
                array('id' => 6, 'countryId' => 3, 'name' => 'Ha Noi', '_maxmind' => 'VN44', 'abbreviation' => null),
                array('id' => 7, 'countryId' => 2, 'name' => 'Nevada', '_maxmind' => 'USNV', 'abbreviation' => 'NV'),
            ),
            array(
                array('id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'Le Havre', 'lat' => 49.5, 'lon' => 0.1333, '_maxmind' => 50221),
                array('id' => 2, 'stateId' => 4, 'countryId' => 2, 'name' => 'Las Vegas', 'lat' => 36.175, 'lon' => -115.137, '_maxmind' => 5718),
                array('id'       => 3, 'stateId' => 7, 'countryId' => 2, 'name' => 'Very Long Beach', 'lat' => 33.767, 'lon' => -118.189,
                      '_maxmind' => 23653),
                array('id'       => 4, 'stateId' => null, 'countryId' => 2, 'name' => 'Los Angeles', 'lat' => 34.0522, 'lon' => -118.244,
                      '_maxmind' => 11532),
                array('id'       => 5, 'stateId' => 7, 'countryId' => 2, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419,
                      '_maxmind' => 11101),
                array('id' => 6, 'stateId' => 6, 'countryId' => 3, 'name' => 'Hanoi', 'lat' => 21.033, 'lon' => 105.85, '_maxmind' => 46418),
            ),
            array(),
            array(),
            array()
        );
    }

    public function testUpdateRegionCode() {
        $this->_import(
            array(
                array('France', 'FR'),
                array('United States', 'US'),
            ),
            array(
                array('FR', 'A7', 'Haute-Normandie'),
                array('US', 'CA', 'California'),
            ),
            array(
                array('75', 'FR', '', '', '', '48.86', '2.35'),
                array('436884', 'FR', 'A7', '', '', '49.4333', '1.0833'),
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('11532', 'US', 'CA', 'Los Angeles', '', '34.0522', '-118.2437'),
            ),
            array(),
            array()
        );
        $this->_import(
            array(
                array('France', 'FR'),
                array('United States', 'US'),
            ),
            array(
                array('FR', 'AA', 'Haute-Normandie'),
                array('US', 'CF', 'California'),
            ),
            array(
                array('75', 'FR', '', '', '', '48.86', '2.35'),
                array('436884', 'FR', 'AA', '', '', '49.4333', '1.0833'),
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CF', '', '', '34.0522', '-118.243'),
                array('11532', 'US', 'CF', 'Los Angeles', '', '34.0522', '-118.2437'),
            ),
            array(),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'FR', 'name' => 'France'),
                array('id' => 2, 'abbreviation' => 'US', 'name' => 'United States'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRAA', 'abbreviation' => null),
                array('id' => 2, 'countryId' => 2, 'name' => 'California', '_maxmind' => 'USCF', 'abbreviation' => 'CF'),
            ),
            array(
                array('id' => 1, 'stateId' => 2, 'countryId' => 2, 'name' => 'Los Angeles', 'lat' => 34.0522, 'lon' => -118.244, '_maxmind' => 11532),
            ),
            array(),
            array(),
            array()
        );
    }

    public function testUpdateRegionName() {
        $this->_import(
            array(
                array('France', 'FR'),
            ),
            array(
                array('FR', 'A7', 'Haute-Normandie'),
            ),
            array(
                array('75', 'FR', '', '', '', '48.86', '2.35'),
                array('436884', 'FR', 'A7', '', '', '49.4333', '1.0833'),
            ),
            array(),
            array()
        );
        $this->_import(
            array(
                array('France', 'FR'),
            ),
            array(
                array('FR', 'A7', 'Normandie'),
            ),
            array(
                array('75', 'FR', '', '', '', '48.86', '2.35'),
                array('436884', 'FR', 'A7', '', '', '49.4333', '1.0833'),
            ),
            array(),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'FR', 'name' => 'France'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'Normandie', '_maxmind' => 'FRA7', 'abbreviation' => null),
            ),
            array(),
            array(),
            array(),
            array()
        );
    }

    public function testUpdateRegion_numericMaxMindCodeUS() {
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'CA', 'California'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
            ),
            array(),
            array()
        );
        CM_Db_Db::update('cm_model_location_state', array('_maxmind' => 'US06'));
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'US', 'name' => 'United States'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'US06', 'abbreviation' => 'CA'),
            ),
            array(),
            array(),
            array(),
            array()
        );
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'CA', 'California'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
            ),
            array(),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'US', 'name' => 'United States'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'US06', 'abbreviation' => 'CA'),
            ),
            array(),
            array(),
            array(),
            array()
        );
    }

    public function testUpdateRegionName_numericMaxMindCodeUS() {
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'CA', 'California'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
            ),
            array(),
            array()
        );
        CM_Db_Db::update('cm_model_location_state', array('_maxmind' => 'US06'));
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'US', 'name' => 'United States'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'US06', 'abbreviation' => 'CA'),
            ),
            array(),
            array(),
            array(),
            array()
        );
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'CA', 'State of California'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
            ),
            array(),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'US', 'name' => 'United States'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'State of California', '_maxmind' => 'US06', 'abbreviation' => 'CA'),
            ),
            array(),
            array(),
            array(),
            array()
        );
    }

    public function testAddCity() {
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'CA', 'California'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'),
            ),
            array(),
            array()
        );
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'CA', 'California'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'),
                array('11532', 'US', 'CA', 'Los Angeles', '', '34.0522', '-118.2437'),
            ),
            array(),
            array()
        );
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'CA', 'California'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'),
                array('11532', 'US', 'CA', 'Los Angeles', '', '34.0522', '-118.2437'),
                array('23653', 'US', 'CA', 'Long Beach', '', '33.767', '-118.1892'),
            ),
            array(),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'US', 'name' => 'United States'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'),
            ),
            array(
                array('id'       => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419,
                      '_maxmind' => 11101),
                array('id' => 2, 'stateId' => 1, 'countryId' => 1, 'name' => 'Los Angeles', 'lat' => 34.0522, 'lon' => -118.244, '_maxmind' => 11532),
                array('id' => 3, 'stateId' => 1, 'countryId' => 1, 'name' => 'Long Beach', 'lat' => 33.767, 'lon' => -118.189, '_maxmind' => 23653),
            ),
            array(),
            array(),
            array()
        );
    }

    public function testRemoveCity() {
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'CA', 'California'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'),
                array('11532', 'US', 'CA', 'Los Angeles', '', '34.0522', '-118.2437'),
                array('671', 'US', 'CA', 'Los Angeles', '90015', '34.0396', '-118.2661'),
            ),
            array(
                array('69089280', '69090303', '11532'),
            ),
            array()
        );
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'CA', 'California'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'),
            ),
            array(),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'US', 'name' => 'United States'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'),
            ),
            array(
                array('id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'Los Angeles', 'lat' => 34.0522, 'lon' => -118.244, '_maxmind' => 11532),
                array('id'       => 2, 'stateId' => 1, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419,
                      '_maxmind' => 11101),
            ),
            array(
                array('id' => 1, 'name' => '90015', 'cityId' => 1, 'lat' => 34.0396, 'lon' => -118.266),
            ),
            array(),
            array()
        );
    }

    public function testUpdateCityCode() {
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'CA', 'California'),
                array('US', 'NV', 'Nevada'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('11532', 'US', 'CA', 'Los Angeles', '', '34.0522', '-118.2437'),
                array('8029', 'US', 'NV', '', '', '36.175', '-115.1372'),
                array('5718', 'US', 'NV', 'Las Vegas', '', '36.175', '-115.1372'),
            ),
            array(
                array('69089280', '69090303', '11532'),
                array('81910016', '81910271', '5718'),
            ),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'US', 'name' => 'United States'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'),
                array('id' => 2, 'countryId' => 1, 'name' => 'Nevada', '_maxmind' => 'USNV', 'abbreviation' => 'NV'),
            ),
            array(
                array('id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'Los Angeles', 'lat' => 34.0522, 'lon' => -118.244, '_maxmind' => 11532),
                array('id' => 2, 'stateId' => 2, 'countryId' => 1, 'name' => 'Las Vegas', 'lat' => 36.175, 'lon' => -115.137, '_maxmind' => 5718),
            ),
            array(),
            array(),
            array(
                array('cityId' => 1, 'ipStart' => 69089280, 'ipEnd' => 69090303),
                array('cityId' => 2, 'ipStart' => 81910016, 'ipEnd' => 81910271),
            )
        );
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'CA', 'California'),
                array('US', 'NV', 'Nevada'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('11111', 'US', 'CA', 'Los Angeles', '', '34.0522', '-118.2437'),
                array('8029', 'US', 'NV', '', '', '36.175', '-115.1372'),
                array('5555', 'US', 'NV', 'Las Vegas', '', '36.175', '-115.1372'),
            ),
            array(
                array('69089280', '69090303', '11111'),
                array('70988544', '70988799', '11111'),
                array('81910016', '81910271', '5555'),
                array('202915072', '202915327', '5555'),
            ),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'US', 'name' => 'United States'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'),
                array('id' => 2, 'countryId' => 1, 'name' => 'Nevada', '_maxmind' => 'USNV', 'abbreviation' => 'NV'),
            ),
            array(
                array('id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'Los Angeles', 'lat' => 34.0522, 'lon' => -118.244, '_maxmind' => 11111),
                array('id' => 2, 'stateId' => 2, 'countryId' => 1, 'name' => 'Las Vegas', 'lat' => 36.175, 'lon' => -115.137, '_maxmind' => 5555),
            ),
            array(),
            array(),
            array(
                array('cityId' => 1, 'ipStart' => 69089280, 'ipEnd' => 69090303),
                array('cityId' => 1, 'ipStart' => 70988544, 'ipEnd' => 70988799),
                array('cityId' => 2, 'ipStart' => 81910016, 'ipEnd' => 81910271),
                array('cityId' => 2, 'ipStart' => 202915072, 'ipEnd' => 202915327),
            )
        );
    }

    public function testUpdateCityCode_circular() {
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'CA', 'California'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'),
                array('608', 'US', 'CA', 'San Francisco', '94124', '37.7312', '-122.3826'),
                array('11532', 'US', 'CA', 'Los Angeles', '', '34.0522', '-118.2437'),
                array('23653', 'US', 'CA', 'Long Beach', '', '33.767', '-118.1892'),
            ),
            array(
                array('68444672', '68444735', '11101'),
                array('69089280', '69090303', '11532'),
                array('71797504', '71797759', '23653'),
            ),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'US', 'name' => 'United States'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'),
            ),
            array(
                array('id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'Long Beach', 'lat' => 33.767, 'lon' => -118.189, '_maxmind' => 23653),
                array('id' => 2, 'stateId' => 1, 'countryId' => 1, 'name' => 'Los Angeles', 'lat' => 34.0522, 'lon' => -118.244, '_maxmind' => 11532),
                array('id'       => 3, 'stateId' => 1, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419,
                      '_maxmind' => 11101),
            ),
            array(
                array('id' => 1, 'name' => '94124', 'cityId' => 3, 'lat' => 37.7312, 'lon' => -122.383),
            ),
            array(),
            array(
                array('cityId' => 3, 'ipStart' => 68444672, 'ipEnd' => 68444735),
                array('cityId' => 2, 'ipStart' => 69089280, 'ipEnd' => 69090303),
                array('cityId' => 1, 'ipStart' => 71797504, 'ipEnd' => 71797759),
            )
        );
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'CA', 'California'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('23653', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'),
                array('608', 'US', 'CA', 'San Francisco', '94124', '37.7312', '-122.3826'),
                array('11101', 'US', 'CA', 'Los Angeles', '', '34.0522', '-118.2437'),
                array('11532', 'US', 'CA', 'Long Beach', '', '33.767', '-118.1892'),
            ),
            array(
                array('68444672', '68444735', '23653'),
                array('68444800', '68444927', '23653'),
                array('69089280', '69090303', '11101'),
                array('70988544', '70988799', '11101'),
                array('71797504', '71797759', '11532'),
                array('201805824', '201806079', '11532'),
            ),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'US', 'name' => 'United States'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'),
            ),
            array(
                array('id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'Long Beach', 'lat' => 33.767, 'lon' => -118.189, '_maxmind' => 11532),
                array('id' => 2, 'stateId' => 1, 'countryId' => 1, 'name' => 'Los Angeles', 'lat' => 34.0522, 'lon' => -118.244, '_maxmind' => 11101),
                array('id'       => 3, 'stateId' => 1, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419,
                      '_maxmind' => 23653),
            ),
            array(
                array('id' => 1, 'name' => '94124', 'cityId' => 3, 'lat' => 37.7312, 'lon' => -122.383),
            ),
            array(),
            array(
                array('cityId' => 3, 'ipStart' => 68444672, 'ipEnd' => 68444735),
                array('cityId' => 3, 'ipStart' => 68444800, 'ipEnd' => 68444927),
                array('cityId' => 2, 'ipStart' => 69089280, 'ipEnd' => 69090303),
                array('cityId' => 2, 'ipStart' => 70988544, 'ipEnd' => 70988799),
                array('cityId' => 1, 'ipStart' => 71797504, 'ipEnd' => 71797759),
                array('cityId' => 1, 'ipStart' => 201805824, 'ipEnd' => 201806079),
            )
        );
    }

    public function testUpdateCityCode_swapAcrossRegions() {
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'CA', 'California'),
                array('US', 'NV', 'Nevada'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('11532', 'US', 'CA', 'Los Angeles', '', '34.0522', '-118.2437'),
                array('8029', 'US', 'NV', '', '', '36.175', '-115.1372'),
                array('5718', 'US', 'NV', 'Las Vegas', '', '36.175', '-115.1372'),
            ),
            array(
                array('69089280', '69090303', '11532'),
                array('81910016', '81910271', '5718'),
            ),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'US', 'name' => 'United States'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'),
                array('id' => 2, 'countryId' => 1, 'name' => 'Nevada', '_maxmind' => 'USNV', 'abbreviation' => 'NV'),
            ),
            array(
                array('id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'Los Angeles', 'lat' => 34.0522, 'lon' => -118.244, '_maxmind' => 11532),
                array('id' => 2, 'stateId' => 2, 'countryId' => 1, 'name' => 'Las Vegas', 'lat' => 36.175, 'lon' => -115.137, '_maxmind' => 5718),
            ),
            array(),
            array(),
            array(
                array('cityId' => 1, 'ipStart' => 69089280, 'ipEnd' => 69090303),
                array('cityId' => 2, 'ipStart' => 81910016, 'ipEnd' => 81910271),
            )
        );
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'CA', 'California'),
                array('US', 'NV', 'Nevada'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('5718', 'US', 'CA', 'Los Angeles', '', '34.0522', '-118.2437'),
                array('8029', 'US', 'NV', '', '', '36.175', '-115.1372'),
                array('11532', 'US', 'NV', 'Las Vegas', '', '36.175', '-115.1372'),
            ),
            array(
                array('69089280', '69090303', '5718'),
                array('70988544', '70988799', '5718'),
                array('81910016', '81910271', '11532'),
                array('202915072', '202915327', '11532'),
            ),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'US', 'name' => 'United States'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'),
                array('id' => 2, 'countryId' => 1, 'name' => 'Nevada', '_maxmind' => 'USNV', 'abbreviation' => 'NV'),
            ),
            array(
                array('id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'Los Angeles', 'lat' => 34.0522, 'lon' => -118.244, '_maxmind' => 5718),
                array('id' => 2, 'stateId' => 2, 'countryId' => 1, 'name' => 'Las Vegas', 'lat' => 36.175, 'lon' => -115.137, '_maxmind' => 11532),
            ),
            array(),
            array(),
            array(
                array('cityId' => 1, 'ipStart' => 69089280, 'ipEnd' => 69090303),
                array('cityId' => 1, 'ipStart' => 70988544, 'ipEnd' => 70988799),
                array('cityId' => 2, 'ipStart' => 81910016, 'ipEnd' => 81910271),
                array('cityId' => 2, 'ipStart' => 202915072, 'ipEnd' => 202915327),
            )
        );
    }

    public function testUpdateCityCode_unsetRegion() {
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'CA', 'California'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'),
            ),
            array(
                array('68444672', '68444735', '11101'),
                array('68444800', '68444927', '11101'),
            ),
            array()
        );
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'CA', 'California'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('11102', 'US', '', 'San Francisco', '', '37.7749', '-122.4194'),
            ),
            array(
                array('68444672', '68444735', '11102'),
                array('68444800', '68444927', '11102'),
            ),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'US', 'name' => 'United States'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'),
            ),
            array(
                array('id'       => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419,
                      '_maxmind' => 11101),
                array('id'       => 2, 'stateId' => null, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419,
                      '_maxmind' => 11102),
            ),
            array(),
            array(),
            array(
                array('cityId' => 2, 'ipStart' => 68444672, 'ipEnd' => 68444735),
                array('cityId' => 2, 'ipStart' => 68444800, 'ipEnd' => 68444927),
            )
        );
    }

    public function testUpdateCityCode_deletedRegion() {
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'CA', 'California'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'),
            ),
            array(
                array('68444672', '68444735', '11101'),
                array('68444800', '68444927', '11101'),
            ),
            array()
        );
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('11102', 'US', '', 'San Francisco', '', '37.7749', '-122.4194'),
            ),
            array(
                array('68444672', '68444735', '11102'),
                array('68444800', '68444927', '11102'),
            ),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'US', 'name' => 'United States'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'),
            ),
            array(
                array('id'       => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419,
                      '_maxmind' => 11101),
                array('id'       => 2, 'stateId' => null, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419,
                      '_maxmind' => 11102),
            ),
            array(),
            array(),
            array(
                array('cityId' => 2, 'ipStart' => 68444672, 'ipEnd' => 68444735),
                array('cityId' => 2, 'ipStart' => 68444800, 'ipEnd' => 68444927),
            )
        );
    }

    public function testUpdateCityName() {
        $this->_import(
            array(
                array('France', 'FR'),
            ),
            array(
                array('FR', 'A7', 'Haute-Normandie'),
            ),
            array(
                array('75', 'FR', '', '', '', '48.86', '2.35'),
                array('436884', 'FR', 'A7', '', '', '49.4333', '1.0833'),
                array('50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'),
            ),
            array(),
            array()
        );
        $this->_import(
            array(
                array('France', 'FR'),
            ),
            array(
                array('FR', 'A7', 'Haute-Normandie'),
            ),
            array(
                array('75', 'FR', '', '', '', '48.86', '2.35'),
                array('436884', 'FR', 'A7', '', '', '49.4333', '1.0833'),
                array('50221', 'FR', 'A7', 'Marseille', '', '49.5', '0.1333'),
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
            array(
                array('id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'Marseille', 'lat' => 49.5, 'lon' => 0.1333, '_maxmind' => 50221),
            ),
            array(),
            array(),
            array()
        );
    }

    public function testUpdateCityName_deletedRegion() {
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'CA', 'California'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'),
            ),
            array(
                array('68444672', '68444735', '11101'),
                array('68444800', '68444927', '11101'),
            ),
            array()
        );
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('11101', 'US', '', 'San Pedro', '', '37.7749', '-122.4194'),
            ),
            array(
                array('68444672', '68444735', '11101'),
                array('68444800', '68444927', '11101'),
            ),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'US', 'name' => 'United States'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'),
            ),
            array(
                array('id'       => 1, 'stateId' => null, 'countryId' => 1, 'name' => 'San Pedro', 'lat' => 37.7749, 'lon' => -122.419,
                      '_maxmind' => 11101),
            ),
            array(),
            array(),
            array(
                array('cityId' => 1, 'ipStart' => 68444672, 'ipEnd' => 68444735),
                array('cityId' => 1, 'ipStart' => 68444800, 'ipEnd' => 68444927),
            )
        );
    }

    public function testUpdateCityRegion() {
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'CA', 'California'),
                array('US', 'HI', 'Hawaii'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'),
                array('14550', 'US', 'HI', '', '', '21.3629', '-157.8727'),
            ),
            array(
                array('68444672', '68444735', '11101'),
                array('68444800', '68444927', '11101'),
            ),
            array()
        );
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'CA', 'California'),
                array('US', 'HI', 'Hawaii'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('14550', 'US', 'HI', '', '', '21.3629', '-157.8727'),
                array('11101', 'US', 'HI', 'San Francisco', '', '37.7749', '-122.4194'),
            ),
            array(
                array('68444672', '68444735', '11101'),
                array('68444800', '68444927', '11101'),
            ),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'US', 'name' => 'United States'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'),
                array('id' => 2, 'countryId' => 1, 'name' => 'Hawaii', '_maxmind' => 'USHI', 'abbreviation' => 'HI'),
            ),
            array(
                array('id'       => 1, 'stateId' => 2, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419,
                      '_maxmind' => 11101),
            ),
            array(),
            array(),
            array(
                array('cityId' => 1, 'ipStart' => 68444672, 'ipEnd' => 68444735),
                array('cityId' => 1, 'ipStart' => 68444800, 'ipEnd' => 68444927),
            )
        );
    }

    public function testUpdateCityRegion_set() {
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'CA', 'California'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('11101', 'US', '', 'San Francisco', '', '37.7749', '-122.4194'),
            ),
            array(
                array('68444672', '68444735', '11101'),
                array('68444800', '68444927', '11101'),
            ),
            array()
        );
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'CA', 'California'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'),
            ),
            array(
                array('68444672', '68444735', '11101'),
                array('68444800', '68444927', '11101'),
            ),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'US', 'name' => 'United States'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'),
            ),
            array(
                array('id'       => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419,
                      '_maxmind' => 11101),
            ),
            array(),
            array(),
            array(
                array('cityId' => 1, 'ipStart' => 68444672, 'ipEnd' => 68444735),
                array('cityId' => 1, 'ipStart' => 68444800, 'ipEnd' => 68444927),
            )
        );
    }

    public function testUpdateCityRegion_unset() {
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'CA', 'California'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'),
            ),
            array(
                array('68444672', '68444735', '11101'),
                array('68444800', '68444927', '11101'),
            ),
            array()
        );
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'CA', 'California'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('11101', 'US', '', 'San Francisco', '', '37.7749', '-122.4194'),
            ),
            array(
                array('68444672', '68444735', '11101'),
                array('68444800', '68444927', '11101'),
            ),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'US', 'name' => 'United States'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'),
            ),
            array(
                array('id'       => 1, 'stateId' => null, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419,
                      '_maxmind' => 11101),
            ),
            array(),
            array(),
            array(
                array('cityId' => 1, 'ipStart' => 68444672, 'ipEnd' => 68444735),
                array('cityId' => 1, 'ipStart' => 68444800, 'ipEnd' => 68444927),
            )
        );
    }

    public function testUpdateCityRegion_newRegion() {
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'CA', 'California'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'),
            ),
            array(
                array('68444672', '68444735', '11101'),
                array('68444800', '68444927', '11101'),
            ),
            array()
        );
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'CA', 'California'),
                array('US', 'HI', 'Hawaii'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('14550', 'US', 'HI', '', '', '21.3629', '-157.8727'),
                array('11101', 'US', 'HI', 'San Francisco', '', '37.7749', '-122.4194'),
            ),
            array(
                array('68444672', '68444735', '11101'),
                array('68444800', '68444927', '11101'),
            ),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'US', 'name' => 'United States'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'),
                array('id' => 2, 'countryId' => 1, 'name' => 'Hawaii', '_maxmind' => 'USHI', 'abbreviation' => 'HI'),
            ),
            array(
                array('id'       => 1, 'stateId' => 2, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419,
                      '_maxmind' => 11101),
            ),
            array(),
            array(),
            array(
                array('cityId' => 1, 'ipStart' => 68444672, 'ipEnd' => 68444735),
                array('cityId' => 1, 'ipStart' => 68444800, 'ipEnd' => 68444927),
            )
        );
    }

    public function testUpdateCityRegion_deletedRegion() {
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'CA', 'California'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'),
            ),
            array(
                array('68444672', '68444735', '11101'),
                array('68444800', '68444927', '11101'),
            ),
            array()
        );
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'HI', 'Hawaii'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('14550', 'US', 'HI', '', '', '21.3629', '-157.8727'),
                array('11101', 'US', 'HI', 'San Francisco', '', '37.7749', '-122.4194'),
            ),
            array(
                array('68444672', '68444735', '11101'),
                array('68444800', '68444927', '11101'),
            ),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'US', 'name' => 'United States'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'),
                array('id' => 2, 'countryId' => 1, 'name' => 'Hawaii', '_maxmind' => 'USHI', 'abbreviation' => 'HI'),
            ),
            array(
                array('id'       => 1, 'stateId' => 2, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419,
                      '_maxmind' => 11101),
            ),
            array(),
            array(),
            array(
                array('cityId' => 1, 'ipStart' => 68444672, 'ipEnd' => 68444735),
                array('cityId' => 1, 'ipStart' => 68444800, 'ipEnd' => 68444927),
            )
        );
    }

    public function testUpdateCityRegion_regionUpdatedCode() {
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'CA', 'California'),
                array('US', 'HI', 'Hawaii'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'),
                array('14550', 'US', 'HI', '', '', '21.3629', '-157.8727'),
            ),
            array(
                array('68444672', '68444735', '11101'),
                array('68444800', '68444927', '11101'),
            ),
            array()
        );
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'CA', 'California'),
                array('US', 'HJ', 'Hawaii'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('14550', 'US', 'HJ', '', '', '21.3629', '-157.8727'),
                array('11101', 'US', 'HJ', 'San Francisco', '', '37.7749', '-122.4194'),
            ),
            array(
                array('68444672', '68444735', '11101'),
                array('68444800', '68444927', '11101'),
            ),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'US', 'name' => 'United States'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'),
                array('id' => 2, 'countryId' => 1, 'name' => 'Hawaii', '_maxmind' => 'USHJ', 'abbreviation' => 'HJ'),
            ),
            array(
                array('id'       => 1, 'stateId' => 2, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419,
                      '_maxmind' => 11101),
            ),
            array(),
            array(),
            array(
                array('cityId' => 1, 'ipStart' => 68444672, 'ipEnd' => 68444735),
                array('cityId' => 1, 'ipStart' => 68444800, 'ipEnd' => 68444927),
            )
        );
    }

    public function testUpdateCityRegion_oldRegionUpdatedCode() {
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'CA', 'California'),
                array('US', 'HI', 'Hawaii'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'),
                array('14550', 'US', 'HI', '', '', '21.3629', '-157.8727'),
            ),
            array(
                array('68444672', '68444735', '11101'),
                array('68444800', '68444927', '11101'),
            ),
            array()
        );
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'CB', 'California'),
                array('US', 'HI', 'Hawaii'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CB', '', '', '34.0522', '-118.243'),
                array('14550', 'US', 'HI', '', '', '21.3629', '-157.8727'),
                array('11101', 'US', 'HI', 'San Francisco', '', '37.7749', '-122.4194'),
            ),
            array(
                array('68444672', '68444735', '11101'),
                array('68444800', '68444927', '11101'),
            ),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'US', 'name' => 'United States'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCB', 'abbreviation' => 'CB'),
                array('id' => 2, 'countryId' => 1, 'name' => 'Hawaii', '_maxmind' => 'USHI', 'abbreviation' => 'HI'),
            ),
            array(
                array('id'       => 1, 'stateId' => 2, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419,
                      '_maxmind' => 11101),
            ),
            array(),
            array(),
            array(
                array('cityId' => 1, 'ipStart' => 68444672, 'ipEnd' => 68444735),
                array('cityId' => 1, 'ipStart' => 68444800, 'ipEnd' => 68444927),
            )
        );
    }

    public function testUpdateCityRegion_regionsUpdatedCode() {
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'CA', 'California'),
                array('US', 'HI', 'Hawaii'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'),
                array('14550', 'US', 'HI', '', '', '21.3629', '-157.8727'),
            ),
            array(
                array('68444672', '68444735', '11101'),
                array('68444800', '68444927', '11101'),
            ),
            array()
        );
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'CB', 'California'),
                array('US', 'HJ', 'Hawaii'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CB', '', '', '34.0522', '-118.243'),
                array('14550', 'US', 'HJ', '', '', '21.3629', '-157.8727'),
                array('11101', 'US', 'HJ', 'San Francisco', '', '37.7749', '-122.4194'),
            ),
            array(
                array('68444672', '68444735', '11101'),
                array('68444800', '68444927', '11101'),
            ),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'US', 'name' => 'United States'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCB', 'abbreviation' => 'CB'),
                array('id' => 2, 'countryId' => 1, 'name' => 'Hawaii', '_maxmind' => 'USHJ', 'abbreviation' => 'HJ'),
            ),
            array(
                array('id'       => 1, 'stateId' => 2, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419,
                      '_maxmind' => 11101),
            ),
            array(),
            array(),
            array(
                array('cityId' => 1, 'ipStart' => 68444672, 'ipEnd' => 68444735),
                array('cityId' => 1, 'ipStart' => 68444800, 'ipEnd' => 68444927),
            )
        );
    }

    public function testUpdateCityRegion_regionsSwappedCode() {
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'CA', 'California'),
                array('US', 'HI', 'Hawaii'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'),
                array('14550', 'US', 'HI', '', '', '21.3629', '-157.8727'),
            ),
            array(
                array('68444672', '68444735', '11101'),
                array('68444800', '68444927', '11101'),
            ),
            array()
        );
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'HI', 'California'),
                array('US', 'CA', 'Hawaii'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'HI', '', '', '34.0522', '-118.243'),
                array('14550', 'US', 'CA', '', '', '21.3629', '-157.8727'),
                array('11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'),
            ),
            array(
                array('68444672', '68444735', '11101'),
                array('68444800', '68444927', '11101'),
            ),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'US', 'name' => 'United States'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USHI', 'abbreviation' => 'HI'),
                array('id' => 2, 'countryId' => 1, 'name' => 'Hawaii', '_maxmind' => 'USCA', 'abbreviation' => 'CA'),
            ),
            array(
                array('id'       => 1, 'stateId' => 2, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419,
                      '_maxmind' => 11101),
            ),
            array(),
            array(),
            array(
                array('cityId' => 1, 'ipStart' => 68444672, 'ipEnd' => 68444735),
                array('cityId' => 1, 'ipStart' => 68444800, 'ipEnd' => 68444927),
            )
        );
    }

    public function testUpdateCityRegion_replaceDuplicateCity() {
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'CA', 'California'),
                array('US', 'HI', 'Hawaii'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'),
                array('5718', 'US', 'CA', 'Los Angeles', '', '34.0522', '-118.2437'),
                array('14550', 'US', 'HI', '', '', '21.3629', '-157.8727'),
                array('11102', 'US', 'HI', 'San Francisco', '', '37.7749', '-122.4194'),
                array('5719', 'US', 'HI', 'Los Angeles', '', '34.0522', '-118.2437'),
            ),
            array(
                array('68444672', '68444735', '11101'),
                array('68444800', '68444927', '11101'),
                array('68445000', '68445100', '11102'),
                array('69089280', '69090303', '5718'),
                array('70988544', '70988799', '5718'),
                array('70989000', '70989100', '5719'),
            ),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'US', 'name' => 'United States'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'),
                array('id' => 2, 'countryId' => 1, 'name' => 'Hawaii', '_maxmind' => 'USHI', 'abbreviation' => 'HI'),
            ),
            array(
                array('id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'Los Angeles', 'lat' => 34.0522, 'lon' => -118.244, '_maxmind' => 5718),
                array('id'       => 2, 'stateId' => 1, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419,
                      '_maxmind' => 11101),
                array('id' => 3, 'stateId' => 2, 'countryId' => 1, 'name' => 'Los Angeles', 'lat' => 34.0522, 'lon' => -118.244, '_maxmind' => 5719),
                array('id'       => 4, 'stateId' => 2, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419,
                      '_maxmind' => 11102),
            ),
            array(),
            array(),
            array(
                array('cityId' => 2, 'ipStart' => 68444672, 'ipEnd' => 68444735),
                array('cityId' => 2, 'ipStart' => 68444800, 'ipEnd' => 68444927),
                array('cityId' => 4, 'ipStart' => 68445000, 'ipEnd' => 68445100),
                array('cityId' => 1, 'ipStart' => 69089280, 'ipEnd' => 69090303),
                array('cityId' => 1, 'ipStart' => 70988544, 'ipEnd' => 70988799),
                array('cityId' => 3, 'ipStart' => 70989000, 'ipEnd' => 70989100),
            )
        );
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'CA', 'California'),
                array('US', 'HI', 'Hawaii'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('5719', 'US', 'CA', 'Los Angeles', '', '34.0522', '-118.2437'),
                array('14550', 'US', 'HI', '', '', '21.3629', '-157.8727'),
                array('11101', 'US', 'HI', 'San Francisco', '', '37.7749', '-122.4194'),
            ),
            array(
                array('68444672', '68444735', '11101'),
                array('68444800', '68444927', '11101'),
                array('70989000', '70989100', '5719'),
            ),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'US', 'name' => 'United States'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'),
                array('id' => 2, 'countryId' => 1, 'name' => 'Hawaii', '_maxmind' => 'USHI', 'abbreviation' => 'HI'),
            ),
            array(
                array('id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'Los Angeles', 'lat' => 34.0522, 'lon' => -118.244, '_maxmind' => 5719),
                array('id'       => 2, 'stateId' => 1, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419,
                      '_maxmind' => 11101),
                array('id' => 3, 'stateId' => 2, 'countryId' => 1, 'name' => 'Los Angeles', 'lat' => 34.0522, 'lon' => -118.244, '_maxmind' => 5719),
                array('id'       => 4, 'stateId' => 2, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419,
                      '_maxmind' => 11101),
            ),
            array(),
            array(),
            array(
                array('cityId' => 4, 'ipStart' => 68444672, 'ipEnd' => 68444735),
                array('cityId' => 4, 'ipStart' => 68444800, 'ipEnd' => 68444927),
                array('cityId' => 1, 'ipStart' => 70989000, 'ipEnd' => 70989100),
            )
        );
    }

    public function testUpdateCityRegion_replaceDuplicateCityInUnknownRegion() {
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'CA', 'California'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('11102', 'US', '', 'San Francisco', '', '37.7749', '-122.4194'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'),
            ),
            array(
                array('68444672', '68444735', '11101'),
                array('68444800', '68444927', '11101'),
                array('68445000', '68445100', '11102'),
            ),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'US', 'name' => 'United States'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'),
            ),
            array(
                array('id'       => 1, 'stateId' => null, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419,
                      '_maxmind' => 11102),
                array('id'       => 2, 'stateId' => 1, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419,
                      '_maxmind' => 11101),
            ),
            array(),
            array(),
            array(
                array('cityId' => 2, 'ipStart' => 68444672, 'ipEnd' => 68444735),
                array('cityId' => 2, 'ipStart' => 68444800, 'ipEnd' => 68444927),
                array('cityId' => 1, 'ipStart' => 68445000, 'ipEnd' => 68445100),
            )
        );
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'CA', 'California'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('11101', 'US', '', 'San Francisco', '', '37.7749', '-122.4194'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
            ),
            array(
                array('68444672', '68444735', '11101'),
                array('68444800', '68444927', '11101'),
            ),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'US', 'name' => 'United States'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'),
            ),
            array(
                array('id'       => 1, 'stateId' => null, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419,
                      '_maxmind' => 11101),
                array('id'       => 2, 'stateId' => 1, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419,
                      '_maxmind' => 11101),
            ),
            array(),
            array(),
            array(
                array('cityId' => 1, 'ipStart' => 68444672, 'ipEnd' => 68444735),
                array('cityId' => 1, 'ipStart' => 68444800, 'ipEnd' => 68444927),
            )
        );
    }

    public function testUpdateCityRegion_replaceDuplicateCityFromUnknownRegion() {
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'CA', 'California'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('11102', 'US', '', 'San Francisco', '', '37.7749', '-122.4194'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'),
            ),
            array(
                array('68444672', '68444735', '11101'),
                array('68444800', '68444927', '11101'),
                array('68445000', '68445100', '11102'),
            ),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'US', 'name' => 'United States'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'),
            ),
            array(
                array('id'       => 1, 'stateId' => null, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419,
                      '_maxmind' => 11102),
                array('id'       => 2, 'stateId' => 1, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419,
                      '_maxmind' => 11101),
            ),
            array(),
            array(),
            array(
                array('cityId' => 2, 'ipStart' => 68444672, 'ipEnd' => 68444735),
                array('cityId' => 2, 'ipStart' => 68444800, 'ipEnd' => 68444927),
                array('cityId' => 1, 'ipStart' => 68445000, 'ipEnd' => 68445100),
            )
        );
        $this->_import(
            array(
                array('United States', 'US'),
            ),
            array(
                array('US', 'CA', 'California'),
            ),
            array(
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('11102', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'),
            ),
            array(
                array('68444672', '68444735', '11102'),
                array('68444800', '68444927', '11102'),
            ),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'US', 'name' => 'United States'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'),
            ),
            array(
                array('id'       => 1, 'stateId' => null, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419,
                      '_maxmind' => 11102),
                array('id'       => 2, 'stateId' => 1, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419,
                      '_maxmind' => 11102),
            ),
            array(),
            array(),
            array(
                array('cityId' => 2, 'ipStart' => 68444672, 'ipEnd' => 68444735),
                array('cityId' => 2, 'ipStart' => 68444800, 'ipEnd' => 68444927),
            )
        );
    }

    public function testUpdateCityRegion_duplicateRegion() {
        $this->_import(
            array(
                array('France', 'FR'),
                array('United States', 'US'),
            ),
            array(
                array('FR', 'A7', 'Haute-Normandie'),
                array('US', 'CA', 'California'),
            ),
            array(
                array('75', 'FR', '', '', '', '48.86', '2.35'),
                array('436884', 'FR', 'A7', '', '', '49.4333', '1.0833'),
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'),
            ),
            array(),
            array()
        );
        $this->_import(
            array(
                array('France', 'FR'),
                array('United States', 'US'),
            ),
            array(
                array('FR', 'A7', 'Haute-Normandie'),
                array('FR', 'A8', 'Haute-Normandie'),
                array('US', 'CA', 'California'),
                array('US', 'CB', 'California'),
            ),
            array(
                array('75', 'FR', '', '', '', '48.86', '2.35'),
                array('436884', 'FR', 'A7', '', '', '49.4333', '1.0833'),
                array('436884', 'FR', 'A8', '', '', '49.4333', '1.0833'),
                array('223', 'US', '', '', '', '38', '-97'),
                array('2221', 'US', 'CA', '', '', '34.0522', '-118.243'),
                array('2221', 'US', 'CB', '', '', '34.0522', '-118.243'),
                array('11101', 'US', 'CB', 'San Francisco', '', '37.7749', '-122.4194'),
            ),
            array(),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'FR', 'name' => 'France'),
                array('id' => 2, 'abbreviation' => 'US', 'name' => 'United States'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRA7', 'abbreviation' => null),
                array('id' => 2, 'countryId' => 2, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'),
                array('id' => 3, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRA8', 'abbreviation' => null),
                array('id' => 4, 'countryId' => 2, 'name' => 'California', '_maxmind' => 'USCB', 'abbreviation' => 'CB'),
            ),
            array(
                array('id'       => 1, 'stateId' => 4, 'countryId' => 2, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419,
                      '_maxmind' => 11101),
            ),
            array(),
            array(),
            array()
        );
    }

    public function testAddZipCode() {
        $this->_import(
            array(
                array('France', 'FR'),
            ),
            array(
                array('FR', 'A7', 'Haute-Normandie'),
            ),
            array(
                array('75', 'FR', '', '', '', '48.86', '2.35'),
                array('436884', 'FR', 'A7', '', '', '49.4333', '1.0833'),
                array('50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'),
            ),
            array(),
            array()
        );
        $this->_import(
            array(
                array('France', 'FR'),
            ),
            array(
                array('FR', 'A7', 'Haute-Normandie'),
            ),
            array(
                array('75', 'FR', '', '', '', '48.86', '2.35'),
                array('436884', 'FR', 'A7', '', '', '49.4333', '1.0833'),
                array('50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'),
                array('384603', 'FR', 'A7', 'Le Havre', '76620', '49.4938', '0.1077'),
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
            array(
                array('id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'Le Havre', 'lat' => 49.5, 'lon' => 0.1333, '_maxmind' => 50221),
            ),
            array(
                array('id' => 1, 'name' => '76620', 'cityId' => 1, 'lat' => 49.4938, 'lon' => 0.1077),
            ),
            array(),
            array()
        );
        $this->_import(
            array(
                array('France', 'FR'),
            ),
            array(
                array('FR', 'A7', 'Haute-Normandie'),
            ),
            array(
                array('75', 'FR', '', '', '', '48.86', '2.35'),
                array('436884', 'FR', 'A7', '', '', '49.4333', '1.0833'),
                array('50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'),
                array('384564', 'FR', 'A7', 'Le Havre', '76600', '49.4938', '0.1077'),
                array('385389', 'FR', 'A7', 'Le Havre', '76610', '49.5213', '0.1581'),
                array('384603', 'FR', 'A7', 'Le Havre', '76620', '49.4938', '0.1077'),
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
            array(
                array('id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'Le Havre', 'lat' => 49.5, 'lon' => 0.1333, '_maxmind' => 50221),
            ),
            array(
                array('id' => 1, 'name' => '76620', 'cityId' => 1, 'lat' => 49.4938, 'lon' => 0.1077),
                array('id' => 2, 'name' => '76600', 'cityId' => 1, 'lat' => 49.4938, 'lon' => 0.1077),
                array('id' => 3, 'name' => '76610', 'cityId' => 1, 'lat' => 49.5213, 'lon' => 0.1581),
            ),
            array(),
            array()
        );
    }

    public function testRemoveZipCode() {
        $this->_import(
            array(
                array('France', 'FR'),
            ),
            array(
                array('FR', 'A7', 'Haute-Normandie'),
            ),
            array(
                array('75', 'FR', '', '', '', '48.86', '2.35'),
                array('436884', 'FR', 'A7', '', '', '49.4333', '1.0833'),
                array('50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'),
                array('384564', 'FR', 'A7', 'Le Havre', '76600', '49.4938', '0.1077'),
                array('385389', 'FR', 'A7', 'Le Havre', '76610', '49.5213', '0.1581'),
                array('384603', 'FR', 'A7', 'Le Havre', '76620', '49.4938', '0.1077'),
                array('484564', 'FR', 'A7', 'Le Havre', '76630', '49.4938', '0.1077'),
                array('485389', 'FR', 'A7', 'Le Havre', '76640', '49.5213', '0.1581'),
                array('484603', 'FR', 'A7', 'Le Havre', '76650', '49.4938', '0.1077'),
            ),
            array(),
            array()
        );
        $this->_import(
            array(
                array('France', 'FR'),
            ),
            array(
                array('FR', 'A7', 'Haute-Normandie'),
            ),
            array(
                array('75', 'FR', '', '', '', '48.86', '2.35'),
                array('436884', 'FR', 'A7', '', '', '49.4333', '1.0833'),
                array('50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'),
                array('384564', 'FR', 'A7', 'Le Havre', '76600', '49.4938', '0.1077'),
                array('385389', 'FR', 'A7', 'Le Havre', '76610', '49.5213', '0.1581'),
                array('384603', 'FR', 'A7', 'Le Havre', '76620', '49.4938', '0.1077'),
                array('484564', 'FR', 'A7', 'Le Havre', '76630', '49.4938', '0.1077'),
                array('485389', 'FR', 'A7', 'Le Havre', '76640', '49.5213', '0.1581'),
            ),
            array(),
            array()
        );
        $this->_import(
            array(
                array('France', 'FR'),
            ),
            array(
                array('FR', 'A7', 'Haute-Normandie'),
            ),
            array(
                array('75', 'FR', '', '', '', '48.86', '2.35'),
                array('436884', 'FR', 'A7', '', '', '49.4333', '1.0833'),
                array('50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'),
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
            array(
                array('id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'Le Havre', 'lat' => 49.5, 'lon' => 0.1333, '_maxmind' => 50221),
            ),
            array(
                array('id' => 1, 'name' => '76600', 'cityId' => 1, 'lat' => 49.4938, 'lon' => 0.1077),
                array('id' => 2, 'name' => '76610', 'cityId' => 1, 'lat' => 49.5213, 'lon' => 0.1581),
                array('id' => 3, 'name' => '76620', 'cityId' => 1, 'lat' => 49.4938, 'lon' => 0.1077),
                array('id' => 4, 'name' => '76630', 'cityId' => 1, 'lat' => 49.4938, 'lon' => 0.1077),
                array('id' => 5, 'name' => '76640', 'cityId' => 1, 'lat' => 49.5213, 'lon' => 0.1581),
                array('id' => 6, 'name' => '76650', 'cityId' => 1, 'lat' => 49.4938, 'lon' => 0.1077),
            ),
            array(),
            array()
        );
    }

    /**
     * @expectedException CM_Exception
     * @expectedExceptionMessage Unknown country code
     */
    public function testExceptionAddRegionInUnknownCountry() {
        $this->_import(
            array(),
            array(
                array('FR', 'A7', 'Haute-Normandie'),
            ),
            array(),
            array(),
            array()
        );
    }

    /**
     * @expectedException CM_Exception
     * @expectedExceptionMessage Unknown country code
     */
    public function testExceptionUpdateRegionCodeInUnknownCountry() {
        $this->_import(
            array(
                array('France', 'FR'),
            ),
            array(
                array('FR', 'A7', 'Haute-Normandie'),
            ),
            array(),
            array(),
            array()
        );
        $this->_import(
            array(),
            array(
                array('FR', 'AA', 'Haute-Normandie'),
            ),
            array(),
            array(),
            array()
        );
    }

    /**
     * @expectedException CM_Exception
     * @expectedExceptionMessage Unknown country code
     */
    public function testExceptionUpdateRegionNameInUnknownCountry() {
        $this->_import(
            array(
                array('France', 'FR'),
            ),
            array(
                array('FR', 'A7', 'Haute-Normandie'),
            ),
            array(),
            array(),
            array()
        );
        $this->_import(
            array(),
            array(
                array('FR', 'A7', 'Normandie'),
            ),
            array(),
            array(),
            array()
        );
    }

    public function testNormalizeCountryName() {
        $this->_import(
            array(
                array('Korea, Democratic People\'s Republic of', 'KP'),
                array('Korea, Republic of', 'KR'),
                array('Virgin Islands, British', 'VG'),
                array('Virgin Islands, U.S.', 'VI'),
                array('Saint Martin (French part)', 'MF'),
                array('Congo, The Democratic Republic of the', 'CD'),
            ),
            array(),
            array(),
            array(),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'VG', 'name' => 'British Virgin Islands'),
                array('id' => 2, 'abbreviation' => 'CD', 'name' => 'Congo'),
                array('id' => 3, 'abbreviation' => 'KR', 'name' => 'Korea'),
                array('id' => 4, 'abbreviation' => 'KP', 'name' => 'North Korea'),
                array('id' => 5, 'abbreviation' => 'MF', 'name' => 'Saint Martin'),
                array('id' => 6, 'abbreviation' => 'VI', 'name' => 'Virgin Islands'),
            ),
            array(),
            array(),
            array(),
            array(),
            array()
        );
    }

    public function testNormalizeRegionName() {
        $this->_import(
            array(
                array('United Kingdom', 'GB'),
                array('United States', 'US'),
            ),
            array(
                array('GB', 'H9', 'London, City of'),
                array('US', 'AE', 'Armed Forces Europe, Middle East, & Canada'),
            ),
            array(),
            array(),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'GB', 'name' => 'United Kingdom'),
                array('id' => 2, 'abbreviation' => 'US', 'name' => 'United States'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'London', '_maxmind' => 'GBH9', 'abbreviation' => null),
                array('id'           => 2, 'countryId' => 2, 'name' => 'Armed Forces Europe, Middle East, & Canada', '_maxmind' => 'USAE',
                      'abbreviation' => 'AE'),
            ),
            array(),
            array(),
            array(),
            array()
        );
    }

    public function testNormalizeCityName() {
        $this->_import(
            array(
                array('Lithuania', 'LT'),
                array('Taiwan, Province of China', 'TW'),
            ),
            array(
                array('LT', '61', 'Siauliu Apskritis'),
                array('TW', '4', 'T\'ai-wan'),
            ),
            array(
                array('295514', 'LT', '61', '(( Mantjurgiai ))', '', '49.5', '0.1333'),
                array('298374', 'TW', '4', 'Erhchiehtsun (1)', '', '24.7833', '121.6667'),
                array('298375', 'TW', '4', 'Erhchiehtsun (1)', '12345', '24.7833', '121.6667'),
                array('300421', 'TW', '4', 'Chihtan (2)', '12345', '24.9333', '121.65'),
            ),
            array(),
            array()
        );
        $this->_verify(
            array(
                array('id' => 1, 'abbreviation' => 'LT', 'name' => 'Lithuania'),
                array('id' => 2, 'abbreviation' => 'TW', 'name' => 'Taiwan'),
            ),
            array(
                array('id' => 1, 'countryId' => 1, 'name' => 'Siauliu Apskritis', '_maxmind' => 'LT61', 'abbreviation' => null),
                array('id' => 2, 'countryId' => 2, 'name' => 'T\'ai-wan', '_maxmind' => 'TW4', 'abbreviation' => null),
            ),
            array(),
            array(),
            array(),
            array()
        );
    }

    protected function _import($countryDataMock, $regionDataMock, $locationDataMock, $ipDataMock, $regionListLegacyMock) {
        $maxMind = $this->getMock('CMService_MaxMind',
            array('_getCountryData', '_getRegionData', '_getLocationData', '_getIpData', '_getRegionListLegacy'));
        $maxMind->expects($this->any())->method('_getCountryData')->will($this->returnValue($countryDataMock));
        $maxMind->expects($this->any())->method('_getRegionData')->will($this->returnValue($regionDataMock));
        $maxMind->expects($this->any())->method('_getLocationData')->will($this->returnValue($locationDataMock));
        $maxMind->expects($this->any())->method('_getIpData')->will($this->returnValue($ipDataMock));
        $maxMind->expects($this->any())->method('_getRegionListLegacy')->will($this->returnValue($regionListLegacyMock));
        /** @var CMService_MaxMind $maxMind */
        $maxMind->upgrade();
    }

    protected function _verify($countryDataExpected, $regionDataExpected, $cityDataExpected, $zipCodeDataExpected, $ipDataCountryExpected, $ipDataCityExpected) {
        $countryDataActual = CM_Db_Db::select('cm_model_location_country', '*')->fetchAll();
        $this->assertEquals($countryDataExpected, $countryDataActual);
        $regionDataActual = CM_Db_Db::select('cm_model_location_state', '*')->fetchAll();
        $this->assertEquals($regionDataExpected, $regionDataActual);
        $cityDataActual = CM_Db_Db::select('cm_model_location_city', '*')->fetchAll();
        $this->assertEquals($cityDataExpected, $cityDataActual);
        $zipCodeDataActual = CM_Db_Db::select('cm_model_location_zip', '*')->fetchAll();
        $this->assertEquals($zipCodeDataExpected, $zipCodeDataActual);
        $ipDataCountryActual = CM_Db_Db::select('cm_model_location_country_ip', '*')->fetchAll();
        $this->assertEquals($ipDataCountryExpected, $ipDataCountryActual);
        $ipDataCityActual = CM_Db_Db::select('cm_model_location_city_ip', '*')->fetchAll();
        $this->assertEquals($ipDataCityExpected, $ipDataCityActual);
    }
}
