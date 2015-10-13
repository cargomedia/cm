<?php

class CMService_MaxMindTest extends CMTest_TestCase {

    /** @var CM_OutputStream_Abstract */
    protected $_errorStream;

    /** @var CM_OutputStream_File */
    protected $_outputStream;

    public function setUp() {
        CM_Db_Db::exec('ALTER TABLE cm_model_location_ip AUTO_INCREMENT = 1');
        CM_Db_Db::exec('ALTER TABLE cm_model_location_zip AUTO_INCREMENT = 1');
        CM_Db_Db::exec('ALTER TABLE cm_model_location_city AUTO_INCREMENT = 1');
        CM_Db_Db::exec('ALTER TABLE cm_model_location_state AUTO_INCREMENT = 1');
        CM_Db_Db::exec('ALTER TABLE cm_model_location_country AUTO_INCREMENT = 1');
        $this->_errorStream = new CM_OutputStream_Null();
        $file = new CM_File('/CM_OutputStream_File-' . uniqid(), CM_Service_Manager::getInstance()->getFilesystems()->getTmp());
        $file->truncate();
        $this->_outputStream = new CM_OutputStream_File($file);
    }

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testEmpty() {
        $this->_import(
            [],
            [],
            [],
            [],
            []
        );
        $this->_verify(
            [],
            [],
            [],
            [],
            []
        );
    }

    public function testCountry() {
        $this->_import(
            [
                ['France', 'FR'],
            ],
            [],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
            ],
            [],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'FR', 'name' => 'France'],
            ],
            [],
            [],
            [],
            []
        );
    }

    public function testCountry_withoutLocation() {
        $this->_import(
            [
                ['France', 'FR'],
            ],
            [],
            [],
            [],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'FR', 'name' => 'France'],
            ],
            [],
            [],
            [],
            []
        );
    }

    public function testCountry_unknown() {
        $this->_import(
            [],
            [],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
            ],
            [],
            []
        );
        $this->_verify(
            [],
            [],
            [],
            [],
            []
        );
    }

    public function testRegion() {
        $this->_import(
            [
                ['France', 'FR'],
            ],
            [
                ['FR', 'A7', 'Haute-Normandie'],
            ],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['436884', 'FR', 'A7', '', '', '49.4333', '1.0833'],
            ],
            [
                ['1412935424', '1412935551', '436884'],
                ['1412935616', '1412935679', '436884'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'FR', 'name' => 'France'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRA7', 'abbreviation' => null],
            ],
            [],
            [],
            [
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_STATE, 'ipStart' => 1412935424, 'ipEnd' => 1412935551],
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_STATE, 'ipStart' => 1412935616, 'ipEnd' => 1412935679],
            ]
        );
    }

    public function testRegion_withoutLocation() {
        $this->_import(
            [
                ['France', 'FR'],
            ],
            [
                ['FR', 'A7', 'Haute-Normandie'],
            ],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
            ],
            [
                ['1412935424', '1412935551', '436884'],
                ['1412935616', '1412935679', '436884'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'FR', 'name' => 'France'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRA7', 'abbreviation' => null],
            ],
            [],
            [],
            []
        );
    }

    public function testRegion_unknown() {
        $this->_import(
            [
                ['France', 'FR'],
            ],
            [],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['436884', 'FR', 'A7', '', '', '49.4333', '1.0833'],
            ],
            [
                ['1412935424', '1412935551', '436884'],
                ['1412935616', '1412935679', '436884'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'FR', 'name' => 'France'],
            ],
            [],
            [],
            [],
            []
        );
    }

    public function testRegion_legacy() {
        $this->_import(
            [
                ['France', 'FR'],
            ],
            [],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['436884', 'FR', 'A7', '', '', '49.4333', '1.0833'],
            ],
            [
                ['1412935424', '1412935551', '436884'],
                ['1412935616', '1412935679', '436884'],
            ],
            [
                'FR' => [
                    'A7' => 'Haute-Normandie',
                ],
            ]
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'FR', 'name' => 'France'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRA7', 'abbreviation' => null],
            ],
            [],
            [],
            [
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_STATE, 'ipStart' => 1412935424, 'ipEnd' => 1412935551],
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_STATE, 'ipStart' => 1412935616, 'ipEnd' => 1412935679],
            ]
        );
    }

    public function testRegion_keepMissing() {
        $this->_import(
            [
                ['France', 'FR'],
            ],
            [
                ['FR', 'A7', 'Haute-Normandie'],
            ],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['436884', 'FR', 'A7', '', '', '49.4333', '1.0833'],
                ['50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'],
                ['384603', 'FR', 'A7', 'Le Havre', '76620', '49.4938', '0.1077'],
            ],
            [],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'FR', 'name' => 'France'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRA7', 'abbreviation' => null],
            ],
            [
                ['id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'Le Havre', 'lat' => 49.5, 'lon' => 0.1333, '_maxmind' => 50221],
            ],
            [
                ['id' => 1, 'name' => '76620', 'cityId' => 1, 'lat' => 49.4938, 'lon' => 0.1077],
            ],
            []
        );
        $this->_import(
            [
                ['France', 'FR'],
            ],
            [],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['436884', 'FR', 'A7', '', '', '49.4333', '1.0833'],
                ['50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'],
                ['384603', 'FR', 'A7', 'Le Havre', '76620', '49.4938', '0.1077'],
            ],
            [
                ['1412935424', '1412935551', '436884'],
                ['1412935616', '1412935679', '436884'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'FR', 'name' => 'France'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRA7', 'abbreviation' => null],
            ],
            [
                ['id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'Le Havre', 'lat' => 49.5, 'lon' => 0.1333, '_maxmind' => 50221],
            ],
            [
                ['id' => 1, 'name' => '76620', 'cityId' => 1, 'lat' => 49.4938, 'lon' => 0.1077],
            ],
            [
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_STATE, 'ipStart' => 1412935424, 'ipEnd' => 1412935551],
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_STATE, 'ipStart' => 1412935616, 'ipEnd' => 1412935679],
            ]
        );
    }

    public function testRegion_addLegacy() {
        $this->_import(
            [
                ['France', 'FR'],
            ],
            [],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['384603', 'FR', 'A7', 'Le Havre', '76620', '49.4938', '0.1077'],
                ['50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'],
                ['436884', 'FR', 'A7', '', '', '49.4333', '1.0833'],
            ],
            [
                ['1412935424', '1412935551', '436884'],
                ['1412935616', '1412935679', '436884'],
            ],
            [
                'FR' => [
                    'A7' => 'Haute-Normandie',
                ],
            ]
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'FR', 'name' => 'France'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRA7', 'abbreviation' => null],
            ],
            [
                ['id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'Le Havre', 'lat' => 49.5, 'lon' => 0.1333, '_maxmind' => 50221],
            ],
            [
                ['id' => 1, 'name' => '76620', 'cityId' => 1, 'lat' => 49.4938, 'lon' => 0.1077],
            ],
            [
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_STATE, 'ipStart' => 1412935424, 'ipEnd' => 1412935551],
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_STATE, 'ipStart' => 1412935616, 'ipEnd' => 1412935679],
            ]
        );
    }

    public function testRegion_countryWithoutLocation() {
        $this->_import(
            [
                ['France', 'FR'],
            ],
            [
                ['FR', 'A7', 'Haute-Normandie'],
            ],
            [
                ['436884', 'FR', 'A7', '', '', '49.4333', '1.0833'],
            ],
            [
                ['1412935424', '1412935551', '436884'],
                ['1412935616', '1412935679', '436884'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'FR', 'name' => 'France'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRA7', 'abbreviation' => null],
            ],
            [],
            [],
            [
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_STATE, 'ipStart' => 1412935424, 'ipEnd' => 1412935551],
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_STATE, 'ipStart' => 1412935616, 'ipEnd' => 1412935679],
            ]
        );
    }

    public function testCity() {
        $this->_import(
            [
                ['France', 'FR'],
            ],
            [
                ['FR', 'A7', 'Haute-Normandie'],
            ],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['436884', 'FR', 'A7', '', '', '49.4333', '1.0833'],
                ['50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'],
            ],
            [],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'FR', 'name' => 'France'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRA7', 'abbreviation' => null],
            ],
            [
                ['id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'Le Havre', 'lat' => 49.5, 'lon' => 0.1333, '_maxmind' => 50221],
            ],
            [],
            []
        );
    }

    public function testCity_withoutRegion() {
        $this->_import(
            [
                ['France', 'FR'],
            ],
            [],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['50221', 'FR', '', 'Le Havre', '', '49.5', '0.1333'],
            ],
            [],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'FR', 'name' => 'France'],
            ],
            [],
            [
                ['id' => 1, 'stateId' => null, 'countryId' => 1, 'name' => 'Le Havre', 'lat' => 49.5, 'lon' => 0.1333, '_maxmind' => 50221],
            ],
            [],
            []
        );
    }

    public function testCity_unknownRegion() {
        $this->_import(
            [
                ['France', 'FR'],
            ],
            [],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['436884', 'FR', 'A7', '', '', '49.4333', '1.0833'],
                ['50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'],
            ],
            [],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'FR', 'name' => 'France'],
            ],
            [],
            [
                ['id' => 1, 'stateId' => null, 'countryId' => 1, 'name' => 'Le Havre', 'lat' => 49.5, 'lon' => 0.1333, '_maxmind' => 50221],
            ],
            [],
            []
        );
    }

    public function testCity_legacyRegion() {
        $this->_import(
            [
                ['France', 'FR'],
            ],
            [],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['436884', 'FR', 'A7', '', '', '49.4333', '1.0833'],
                ['50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'],
            ],
            [],
            [
                'FR' => [
                    'A7' => 'Haute-Normandie',
                ],
            ]
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'FR', 'name' => 'France'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRA7', 'abbreviation' => null],
            ],
            [
                ['id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'Le Havre', 'lat' => 49.5, 'lon' => 0.1333, '_maxmind' => 50221],
            ],
            [],
            []
        );
    }

    public function testZipCode() {
        $this->_import(
            [
                ['France', 'FR'],
            ],
            [
                ['FR', 'A7', 'Haute-Normandie'],
            ],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['436884', 'FR', 'A7', '', '', '49.4333', '1.0833'],
                ['50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'],
                ['384603', 'FR', 'A7', 'Le Havre', '76620', '49.4938', '0.1077'],
            ],
            [],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'FR', 'name' => 'France'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRA7', 'abbreviation' => null],
            ],
            [
                ['id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'Le Havre', 'lat' => 49.5, 'lon' => 0.1333, '_maxmind' => 50221],
            ],
            [
                ['id' => 1, 'name' => '76620', 'cityId' => 1, 'lat' => 49.4938, 'lon' => 0.1077],
            ],
            []
        );
    }

    public function testZipCode_unknownRegion() {
        $this->_import(
            [
                ['France', 'FR'],
            ],
            [],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'],
                ['384603', 'FR', 'A7', 'Le Havre', '76620', '49.4938', '0.1077'],
                ['385175', 'FR', 'B8', 'Marseille', '13000', '43.3', '5.4'],
            ],
            [],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'FR', 'name' => 'France'],
            ],
            [],
            [
                ['id' => 1, 'stateId' => null, 'countryId' => 1, 'name' => 'Le Havre', 'lat' => 49.5, 'lon' => 0.1333, '_maxmind' => 50221],
                ['id' => 2, 'stateId' => null, 'countryId' => 1, 'name' => 'Marseille', 'lat' => 43.3, 'lon' => 5.4, '_maxmind' => 385175],
            ],
            [
                ['id' => 1, 'name' => '76620', 'cityId' => 1, 'lat' => 49.4938, 'lon' => 0.1077],
                ['id' => 2, 'name' => '13000', 'cityId' => 2, 'lat' => 43.3, 'lon' => 5.4],
            ],
            []
        );
    }

    public function testZipCode_legacyRegion() {
        $this->_import(
            [
                ['France', 'FR'],
            ],
            [],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'],
                ['384603', 'FR', 'A7', 'Le Havre', '76620', '49.4938', '0.1077'],
                ['385175', 'FR', 'B8', 'Marseille', '13000', '43.3', '5.4'],
            ],
            [],
            [
                'FR' => [
                    'A7' => 'Haute-Normandie',
                    'B8' => 'Provence-Alpes-Cote d\'Azur',
                ],
            ]
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'FR', 'name' => 'France'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRA7', 'abbreviation' => null],
                ['id' => 2, 'countryId' => 1, 'name' => 'Provence-Alpes-Cote d\'Azur', '_maxmind' => 'FRB8', 'abbreviation' => null],
            ],
            [
                ['id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'Le Havre', 'lat' => 49.5, 'lon' => 0.1333, '_maxmind' => 50221],
                ['id' => 2, 'stateId' => 2, 'countryId' => 1, 'name' => 'Marseille', 'lat' => 43.3, 'lon' => 5.4, '_maxmind' => 385175],
            ],
            [
                ['id' => 1, 'name' => '76620', 'cityId' => 1, 'lat' => 49.4938, 'lon' => 0.1077],
                ['id' => 2, 'name' => '13000', 'cityId' => 2, 'lat' => 43.3, 'lon' => 5.4],
            ],
            []
        );
    }

    public function testZipCode_withoutCity() {
        $this->_import(
            [
                ['France', 'FR'],
            ],
            [
                ['FR', 'A7', 'Haute-Normandie'],
            ],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['436884', 'FR', 'A7', '', '', '49.4333', '1.0833'],
                ['384603', 'FR', 'A7', 'Le Havre', '76620', '49.4938', '0.1077'],
            ],
            [],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'FR', 'name' => 'France'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRA7', 'abbreviation' => null],
            ],
            [
                ['id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'Le Havre', 'lat' => 49.4938, 'lon' => 0.1077, '_maxmind' => 384603],
            ],
            [
                ['id' => 1, 'name' => '76620', 'cityId' => 1, 'lat' => 49.4938, 'lon' => 0.1077],
            ],
            []
        );
    }

    public function testIpBlockCountry() {
        $this->_import(
            [
                ['France', 'FR'],
            ],
            [],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
            ],
            [
                ['33555968', '33556223', '75'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'FR', 'name' => 'France'],
            ],
            [],
            [],
            [],
            [
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_COUNTRY, 'ipStart' => 33555968, 'ipEnd' => 33556223],
            ]
        );
    }

    public function testIpBlockCity() {
        $this->_import(
            [
                ['France', 'FR'],
            ],
            [
                ['FR', 'A7', 'Haute-Normandie'],
            ],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['436884', 'FR', 'A7', '', '', '49.4333', '1.0833'],
                ['50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'],
            ],
            [
                ['87097600', '87097855', '50221'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'FR', 'name' => 'France'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRA7', 'abbreviation' => null],
            ],
            [
                ['id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'Le Havre', 'lat' => 49.5, 'lon' => 0.1333, '_maxmind' => 50221],
            ],
            [],
            [
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 87097600, 'ipEnd' => 87097855],
            ]
        );
    }

    public function testImport() {
        $this->_import(
            [
                ['France', 'FR'],
                ['United States', 'US'],
            ],
            [
                ['FR', 'A7', 'Haute-Normandie'],
                ['US', 'CA', 'California'],
                ['US', 'HI', 'Hawaii'],
            ],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['436884', 'FR', 'A7', '', '', '49.4333', '1.0833'],
                ['50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'],
                ['384603', 'FR', 'A7', 'Le Havre', '76620', '49.4938', '0.1077'],
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'],
                ['608', 'US', 'CA', 'San Francisco', '94124', '37.7312', '-122.3826'],
                ['757', 'US', 'CA', 'San Francisco', '94105', '37.7898', '-122.3942'],
                ['11532', 'US', 'CA', 'Los Angeles', '', '34.0522', '-118.2437'],
            ],
            [
                ['33555968', '33556223', '75'],
                ['1412935424', '1412935551', '436884'],
                ['1412935616', '1412935679', '436884'],
                ['87097600', '87097855', '50221'],
                ['522357760', '522358015', '384603'],
                ['1304630016', '1304630271', '384603'],
                ['266578176', '266578431', '223'],
                ['266586368', '266586623', '223'],
                ['68866048', '68866303', '2221'],
                ['135422208', '135422463', '2221'],
                ['68444672', '68444735', '11101'],
                ['68444800', '68444927', '11101'],
                ['201948163', '201948415', '999999'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'FR', 'name' => 'France'],
                ['id' => 2, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRA7', 'abbreviation' => null],
                ['id' => 2, 'countryId' => 2, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'],
                ['id' => 3, 'countryId' => 2, 'name' => 'Hawaii', '_maxmind' => 'USHI', 'abbreviation' => 'HI'],
            ],
            [
                ['id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'Le Havre', 'lat' => 49.5, 'lon' => 0.1333, '_maxmind' => 50221],
                ['id' => 2, 'stateId' => 2, 'countryId' => 2, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419, '_maxmind' => 11101],
                ['id' => 3, 'stateId' => 2, 'countryId' => 2, 'name' => 'Los Angeles', 'lat' => 34.0522, 'lon' => -118.244, '_maxmind' => 11532],
            ],
            [
                ['id' => 1, 'name' => '76620', 'cityId' => 1, 'lat' => 49.4938, 'lon' => 0.1077],
                ['id' => 2, 'name' => '94124', 'cityId' => 2, 'lat' => 37.7312, 'lon' => -122.383],
                ['id' => 3, 'name' => '94105', 'cityId' => 2, 'lat' => 37.7898, 'lon' => -122.394],
            ],
            [
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_COUNTRY, 'ipStart' => 33555968, 'ipEnd' => 33556223],
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_STATE, 'ipStart' => 1412935424, 'ipEnd' => 1412935551],
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_STATE, 'ipStart' => 1412935616, 'ipEnd' => 1412935679],
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 87097600, 'ipEnd' => 87097855],
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_ZIP, 'ipStart' => 522357760, 'ipEnd' => 522358015],
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_ZIP, 'ipStart' => 1304630016, 'ipEnd' => 1304630271],
                ['id' => 2, 'level' => CM_Model_Location::LEVEL_COUNTRY, 'ipStart' => 266578176, 'ipEnd' => 266578431],
                ['id' => 2, 'level' => CM_Model_Location::LEVEL_COUNTRY, 'ipStart' => 266586368, 'ipEnd' => 266586623],
                ['id' => 2, 'level' => CM_Model_Location::LEVEL_STATE, 'ipStart' => 68866048, 'ipEnd' => 68866303],
                ['id' => 2, 'level' => CM_Model_Location::LEVEL_STATE, 'ipStart' => 135422208, 'ipEnd' => 135422463],
                ['id' => 2, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68444672, 'ipEnd' => 68444735],
                ['id' => 2, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68444800, 'ipEnd' => 68444927],
            ]
        );
    }

    public function testAddCountry() {
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [],
            [
                ['223', 'US', '', '', '', '38', '-97'],
            ],
            [],
            []
        );
        $this->_import(
            [
                ['France', 'FR'],
                ['United States', 'US'],
            ],
            [],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['223', 'US', '', '', '', '38', '-97'],
            ],
            [],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'US', 'name' => 'United States'],
                ['id' => 2, 'abbreviation' => 'FR', 'name' => 'France'],
            ],
            [],
            [],
            [],
            []
        );
    }

    public function testRemoveCountry() {
        $this->_import(
            [
                ['France', 'FR'],
                ['United States', 'US'],
            ],
            [
                ['FR', 'A7', 'Haute-Normandie'],
            ],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['436884', 'FR', 'A7', '', '', '49.4333', '1.0833'],
                ['50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'],
                ['49739', 'FR', '', 'Marseille', '', '43.2854', '5.3761'],
                ['223', 'US', '', '', '', '38', '-97'],
            ],
            [
                ['33555968', '33556223', '75'],
                ['87097600', '87097855', '50221'],
                ['33818880', '33819135', '49739'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'FR', 'name' => 'France'],
                ['id' => 2, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRA7', 'abbreviation' => null],
            ],
            [
                ['id' => 1, 'stateId' => null, 'countryId' => 1, 'name' => 'Marseille', 'lat' => 43.2854, 'lon' => 5.3761, '_maxmind' => 49739],
                ['id' => 2, 'stateId' => 1, 'countryId' => 1, 'name' => 'Le Havre', 'lat' => 49.5, 'lon' => 0.1333, '_maxmind' => 50221],
            ],
            [],
            [
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_COUNTRY, 'ipStart' => 33555968, 'ipEnd' => 33556223],
                ['id' => 2, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 87097600, 'ipEnd' => 87097855],
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 33818880, 'ipEnd' => 33819135],
            ]
        );
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [],
            [
                ['223', 'US', '', '', '', '38', '-97'],
            ],
            [],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'FR', 'name' => 'France'],
                ['id' => 2, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRA7', 'abbreviation' => null],
            ],
            [
                ['id' => 1, 'stateId' => null, 'countryId' => 1, 'name' => 'Marseille', 'lat' => 43.2854, 'lon' => 5.3761, '_maxmind' => 49739],
                ['id' => 2, 'stateId' => 1, 'countryId' => 1, 'name' => 'Le Havre', 'lat' => 49.5, 'lon' => 0.1333, '_maxmind' => 50221],
            ],
            [],
            []
        );
    }

    public function testUpdateCountryName() {
        $this->_import(
            [
                ['France', 'FR'],
            ],
            [],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
            ],
            [],
            []
        );
        $this->_import(
            [
                ['République Française', 'FR'],
            ],
            [],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
            ],
            [],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'FR', 'name' => 'République Française'],
            ],
            [],
            [],
            [],
            []
        );
    }

    public function testAddRegion() {
        $this->_import(
            [
                ['France', 'FR'],
                ['United States', 'US'],
            ],
            [
                ['US', 'CA', 'California'],
            ],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
            ],
            [],
            []
        );
        $this->_import(
            [
                ['France', 'FR'],
                ['United States', 'US'],
            ],
            [
                ['FR', 'A7', 'Haute-Normandie'],
                ['US', 'CA', 'California'],
                ['US', 'HI', 'Hawaii'],
            ],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['436884', 'FR', 'A7', '', '', '49.4333', '1.0833'],
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['14550', 'US', 'HI', '', '', '21.3629', '-157.8727'],
            ],
            [
                ['68866048', '68866303', '2221'],
                ['135422208', '135422463', '2221'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'FR', 'name' => 'France'],
                ['id' => 2, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [
                ['id' => 1, 'countryId' => 2, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'],
                ['id' => 2, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRA7', 'abbreviation' => null],
                ['id' => 3, 'countryId' => 2, 'name' => 'Hawaii', '_maxmind' => 'USHI', 'abbreviation' => 'HI'],
            ],
            [],
            [],
            [
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_STATE, 'ipStart' => 68866048, 'ipEnd' => 68866303],
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_STATE, 'ipStart' => 135422208, 'ipEnd' => 135422463],
            ]
        );
    }

    public function testAddRegion_duplicate() {
        $this->_import(
            [
                ['France', 'FR'],
                ['United States', 'US'],
            ],
            [
                ['FR', 'A7', 'Haute-Normandie'],
                ['US', 'CA', 'California'],
            ],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['436884', 'FR', 'A7', '', '', '49.4333', '1.0833'],
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'],
            ],
            [],
            []
        );
        $this->_import(
            [
                ['France', 'FR'],
                ['United States', 'US'],
            ],
            [
                ['FR', 'A7', 'Haute-Normandie'],
                ['FR', 'A8', 'Haute-Normandie'],
                ['US', 'CA', 'California'],
                ['US', 'CB', 'California'],
            ],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['436884', 'FR', 'A7', '', '', '49.4333', '1.0833'],
                ['436884', 'FR', 'A8', '', '', '49.4333', '1.0833'],
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'],
                ['2221', 'US', 'CB', '', '', '34.0522', '-118.243'],
            ],
            [
                ['1412935424', '1412935551', '436884'],
                ['1412935616', '1412935679', '436884'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'FR', 'name' => 'France'],
                ['id' => 2, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRA7', 'abbreviation' => null],
                ['id' => 2, 'countryId' => 2, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'],
                ['id' => 3, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRA8', 'abbreviation' => null],
                ['id' => 4, 'countryId' => 2, 'name' => 'California', '_maxmind' => 'USCB', 'abbreviation' => 'CB'],
            ],
            [
                ['id' => 1, 'stateId' => 2, 'countryId' => 2, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419, '_maxmind' => 11101],
            ],
            [],
            [
                ['id' => 3, 'level' => CM_Model_Location::LEVEL_STATE, 'ipStart' => 1412935424, 'ipEnd' => 1412935551],
                ['id' => 3, 'level' => CM_Model_Location::LEVEL_STATE, 'ipStart' => 1412935616, 'ipEnd' => 1412935679],
            ]
        );
    }

    public function testRemoveRegion() {
        $this->_import(
            [
                ['France', 'FR'],
                ['United States', 'US'],
            ],
            [
                ['FR', 'A7', 'Haute-Normandie'],
                ['US', 'CA', 'California'],
                ['US', 'HI', 'Hawaii'],
            ],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['436884', 'FR', 'A7', '', '', '49.4333', '1.0833'],
                ['50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'],
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['14550', 'US', 'HI', '', '', '21.3629', '-157.8727'],
            ],
            [],
            []
        );
        $this->_import(
            [
                ['France', 'FR'],
                ['United States', 'US'],
            ],
            [
                ['US', 'CA', 'California'],
            ],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['50221', 'FR', '', 'Le Havre', '', '49.5', '0.1333'],
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
            ],
            [],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'FR', 'name' => 'France'],
                ['id' => 2, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRA7', 'abbreviation' => null],
                ['id' => 2, 'countryId' => 2, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'],
                ['id' => 3, 'countryId' => 2, 'name' => 'Hawaii', '_maxmind' => 'USHI', 'abbreviation' => 'HI'],
            ],
            [
                ['id' => 1, 'stateId' => null, 'countryId' => 1, 'name' => 'Le Havre', 'lat' => 49.5, 'lon' => 0.1333, '_maxmind' => 50221],
            ],
            [],
            []
        );
    }

    public function testRemoveRegion_codeInUse() {
        $this->_import(
            [
                ['France', 'FR'],
                ['United States', 'US'],
                ['Viet Nam', 'VN'],
            ],
            [
                ['FR', 'A7', 'Haute-Normandie'],
                ['FR', '99', 'Basse-Normandie'],
                ['US', 'CA', 'California'],
                ['US', 'HI', 'Hawaii'],
                ['VN', '44', 'Dac Lac'],
                ['VN', '51', 'Ha Noi'],
            ],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['436884', 'FR', 'A7', '', '', '49.4333', '1.0833'],
                ['50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'],
                ['436884', 'FR', '99', '', '', '49.1972', '-0.3268'],
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['5718', 'US', 'CA', 'Las Vegas', '', '36.175', '-115.1372'],
                ['23653', 'US', 'CA', 'Long Beach', '', '33.767', '-118.1892'],
                ['11532', 'US', 'CA', 'Los Angeles', '', '34.0522', '-118.2437'],
                ['11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'],
                ['14550', 'US', 'HI', '', '', '21.3629', '-157.8727'],
                ['231', 'VN', '', '', '', '16', '106'],
                ['46410', 'VN', '44', '', '', '21.033', '105.85'],
                ['46418', 'VN', '44', 'Hanoi', '', '21.033', '105.85'],
                ['412930', 'VN', '51', '', '', '21.033', '105.85'],
            ],
            [],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'FR', 'name' => 'France'],
                ['id' => 2, 'abbreviation' => 'US', 'name' => 'United States'],
                ['id' => 3, 'abbreviation' => 'VN', 'name' => 'Viet Nam'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRA7', 'abbreviation' => null],
                ['id' => 2, 'countryId' => 1, 'name' => 'Basse-Normandie', '_maxmind' => 'FR99', 'abbreviation' => null],
                ['id' => 3, 'countryId' => 2, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'],
                ['id' => 4, 'countryId' => 2, 'name' => 'Hawaii', '_maxmind' => 'USHI', 'abbreviation' => 'HI'],
                ['id' => 5, 'countryId' => 3, 'name' => 'Dac Lac', '_maxmind' => 'VN44', 'abbreviation' => null],
                ['id' => 6, 'countryId' => 3, 'name' => 'Ha Noi', '_maxmind' => 'VN51', 'abbreviation' => null],
            ],
            [
                ['id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'Le Havre', 'lat' => 49.5, 'lon' => 0.1333, '_maxmind' => 50221],
                ['id' => 2, 'stateId' => 3, 'countryId' => 2, 'name' => 'Las Vegas', 'lat' => 36.175, 'lon' => -115.137, '_maxmind' => 5718],
                ['id' => 3, 'stateId' => 3, 'countryId' => 2, 'name' => 'Long Beach', 'lat' => 33.767, 'lon' => -118.189, '_maxmind' => 23653],
                ['id' => 4, 'stateId' => 3, 'countryId' => 2, 'name' => 'Los Angeles', 'lat' => 34.0522, 'lon' => -118.244, '_maxmind' => 11532],
                ['id' => 5, 'stateId' => 3, 'countryId' => 2, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419, '_maxmind' => 11101],
                ['id' => 6, 'stateId' => 5, 'countryId' => 3, 'name' => 'Hanoi', 'lat' => 21.033, 'lon' => 105.85, '_maxmind' => 46418],
            ],
            [],
            []
        );
        $this->_import(
            [
                ['France', 'FR'],
                ['United States', 'US'],
                ['Viet Nam', 'VN'],
            ],
            [
                ['FR', 'A7', 'Basse-Normandie'],
                ['US', 'CA', 'Hawaii'],
                ['US', 'NV', 'Nevada'],
                ['VN', '44', 'Ha Noi'],
            ],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['436884', 'FR', 'A7', '', '', '49.1972', '-0.3268'],
                ['50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'],
                ['223', 'US', '', '', '', '38', '-97'],
                ['14550', 'US', 'CA', '', '', '21.3629', '-157.8727'],
                ['8029', 'US', 'NV', '', '', '36.175', '-115.1372'],
                ['23653', 'US', 'NV', 'Very Long Beach', '', '33.767', '-118.1892'],
                ['11532', 'US', '', 'Los Angeles', '', '34.0522', '-118.2437'],
                ['11101', 'US', 'NV', 'San Francisco', '', '37.7749', '-122.4194'],
                ['231', 'VN', '', '', '', '16', '106'],
                ['46418', 'VN', '44', 'Hanoi', '', '21.033', '105.85'],
                ['412930', 'VN', '44', '', '', '21.033', '105.85'],
            ],
            [
                ['1412935424', '1412935551', '412930'],
                ['1412935616', '1412935679', '412930'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'FR', 'name' => 'France'],
                ['id' => 2, 'abbreviation' => 'US', 'name' => 'United States'],
                ['id' => 3, 'abbreviation' => 'VN', 'name' => 'Viet Nam'],
            ],
            [
                ['id' => 2, 'countryId' => 1, 'name' => 'Basse-Normandie', '_maxmind' => 'FRA7', 'abbreviation' => null],
                ['id' => 4, 'countryId' => 2, 'name' => 'Hawaii', '_maxmind' => 'USCA', 'abbreviation' => 'CA'],
                ['id' => 6, 'countryId' => 3, 'name' => 'Ha Noi', '_maxmind' => 'VN44', 'abbreviation' => null],
                ['id' => 7, 'countryId' => 2, 'name' => 'Nevada', '_maxmind' => 'USNV', 'abbreviation' => 'NV'],
            ],
            [
                ['id' => 1, 'stateId' => 2, 'countryId' => 1, 'name' => 'Le Havre', 'lat' => 49.5, 'lon' => 0.1333, '_maxmind' => 50221],
                ['id' => 2, 'stateId' => 4, 'countryId' => 2, 'name' => 'Las Vegas', 'lat' => 36.175, 'lon' => -115.137, '_maxmind' => 5718],
                ['id' => 3, 'stateId' => 7, 'countryId' => 2, 'name' => 'Very Long Beach', 'lat' => 33.767, 'lon' => -118.189, '_maxmind' => 23653],
                ['id' => 4, 'stateId' => null, 'countryId' => 2, 'name' => 'Los Angeles', 'lat' => 34.0522, 'lon' => -118.244, '_maxmind' => 11532],
                ['id' => 5, 'stateId' => 7, 'countryId' => 2, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419, '_maxmind' => 11101],
                ['id' => 6, 'stateId' => 6, 'countryId' => 3, 'name' => 'Hanoi', 'lat' => 21.033, 'lon' => 105.85, '_maxmind' => 46418],
            ],
            [],
            [
                ['id' => 6, 'level' => CM_Model_Location::LEVEL_STATE, 'ipStart' => 1412935424, 'ipEnd' => 1412935551],
                ['id' => 6, 'level' => CM_Model_Location::LEVEL_STATE, 'ipStart' => 1412935616, 'ipEnd' => 1412935679],
            ]
        );
    }

    public function testUpdateRegionCode() {
        $this->_import(
            [
                ['France', 'FR'],
                ['United States', 'US'],
            ],
            [
                ['FR', 'A7', 'Haute-Normandie'],
                ['US', 'CA', 'California'],
            ],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['436884', 'FR', 'A7', '', '', '49.4333', '1.0833'],
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['11532', 'US', 'CA', 'Los Angeles', '', '34.0522', '-118.2437'],
            ],
            [],
            []
        );
        $this->_import(
            [
                ['France', 'FR'],
                ['United States', 'US'],
            ],
            [
                ['FR', 'AA', 'Haute-Normandie'],
                ['US', 'CF', 'California'],
            ],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['436884', 'FR', 'AA', '', '', '49.4333', '1.0833'],
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CF', '', '', '34.0522', '-118.243'],
                ['11532', 'US', 'CF', 'Los Angeles', '', '34.0522', '-118.2437'],
            ],
            [
                ['1412935424', '1412935551', '436884'],
                ['1412935616', '1412935679', '436884'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'FR', 'name' => 'France'],
                ['id' => 2, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRAA', 'abbreviation' => null],
                ['id' => 2, 'countryId' => 2, 'name' => 'California', '_maxmind' => 'USCF', 'abbreviation' => 'CF'],
            ],
            [
                ['id' => 1, 'stateId' => 2, 'countryId' => 2, 'name' => 'Los Angeles', 'lat' => 34.0522, 'lon' => -118.244, '_maxmind' => 11532],
            ],
            [],
            [
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_STATE, 'ipStart' => 1412935424, 'ipEnd' => 1412935551],
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_STATE, 'ipStart' => 1412935616, 'ipEnd' => 1412935679],
            ]
        );
    }

    public function testUpdateRegionName() {
        $this->_import(
            [
                ['France', 'FR'],
            ],
            [
                ['FR', 'A7', 'Haute-Normandie'],
            ],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['436884', 'FR', 'A7', '', '', '49.4333', '1.0833'],
            ],
            [],
            []
        );
        $this->_import(
            [
                ['France', 'FR'],
            ],
            [
                ['FR', 'A7', 'Normandie'],
            ],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['436884', 'FR', 'A7', '', '', '49.4333', '1.0833'],
            ],
            [
                ['1412935424', '1412935551', '436884'],
                ['1412935616', '1412935679', '436884'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'FR', 'name' => 'France'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'Normandie', '_maxmind' => 'FRA7', 'abbreviation' => null],
            ],
            [],
            [],
            [
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_STATE, 'ipStart' => 1412935424, 'ipEnd' => 1412935551],
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_STATE, 'ipStart' => 1412935616, 'ipEnd' => 1412935679],
            ]
        );
    }

    public function testUpdateRegion_numericMaxMindCodeUS() {
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'CA', 'California'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
            ],
            [],
            []
        );
        CM_Db_Db::update('cm_model_location_state', ['_maxmind' => 'US06']);
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'US06', 'abbreviation' => 'CA'],
            ],
            [],
            [],
            []
        );
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'CA', 'California'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
            ],
            [
                ['68866048', '68866303', '2221'],
                ['135422208', '135422463', '2221'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'US06', 'abbreviation' => 'CA'],
            ],
            [],
            [],
            [
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_STATE, 'ipStart' => 68866048, 'ipEnd' => 68866303],
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_STATE, 'ipStart' => 135422208, 'ipEnd' => 135422463],
            ]
        );
    }

    public function testUpdateRegionName_numericMaxMindCodeUS() {
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'CA', 'California'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
            ],
            [],
            []
        );
        CM_Db_Db::update('cm_model_location_state', ['_maxmind' => 'US06']);
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'US06', 'abbreviation' => 'CA'],
            ],
            [],
            [],
            []
        );
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'CA', 'State of California'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
            ],
            [
                ['68866048', '68866303', '2221'],
                ['135422208', '135422463', '2221'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'State of California', '_maxmind' => 'US06', 'abbreviation' => 'CA'],
            ],
            [],
            [],
            [
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_STATE, 'ipStart' => 68866048, 'ipEnd' => 68866303],
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_STATE, 'ipStart' => 135422208, 'ipEnd' => 135422463],
            ]
        );
    }

    public function testAddCity() {
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'CA', 'California'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'],
            ],
            [],
            []
        );
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'CA', 'California'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'],
                ['11532', 'US', 'CA', 'Los Angeles', '', '34.0522', '-118.2437'],
            ],
            [],
            []
        );
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'CA', 'California'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'],
                ['11532', 'US', 'CA', 'Los Angeles', '', '34.0522', '-118.2437'],
                ['23653', 'US', 'CA', 'Long Beach', '', '33.767', '-118.1892'],
            ],
            [],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'],
            ],
            [
                ['id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419, '_maxmind' => 11101],
                ['id' => 2, 'stateId' => 1, 'countryId' => 1, 'name' => 'Los Angeles', 'lat' => 34.0522, 'lon' => -118.244, '_maxmind' => 11532],
                ['id' => 3, 'stateId' => 1, 'countryId' => 1, 'name' => 'Long Beach', 'lat' => 33.767, 'lon' => -118.189, '_maxmind' => 23653],
            ],
            [],
            []
        );
    }

    public function testRemoveCity() {
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'CA', 'California'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'],
                ['11532', 'US', 'CA', 'Los Angeles', '', '34.0522', '-118.2437'],
                ['671', 'US', 'CA', 'Los Angeles', '90015', '34.0396', '-118.2661'],
            ],
            [
                ['69089280', '69090303', '11532'],
            ],
            []
        );
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'CA', 'California'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'],
            ],
            [],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'],
            ],
            [
                ['id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419, '_maxmind' => 11101],
                ['id' => 2, 'stateId' => 1, 'countryId' => 1, 'name' => 'Los Angeles', 'lat' => 34.0522, 'lon' => -118.244, '_maxmind' => 11532],
            ],
            [
                ['id' => 1, 'name' => '90015', 'cityId' => 2, 'lat' => 34.0396, 'lon' => -118.266],
            ],
            []
        );
    }

    public function testUpdateCityCode() {
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'CA', 'California'],
                ['US', 'NV', 'Nevada'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['11532', 'US', 'CA', 'Los Angeles', '', '34.0522', '-118.2437'],
                ['8029', 'US', 'NV', '', '', '36.175', '-115.1372'],
                ['5718', 'US', 'NV', 'Las Vegas', '', '36.175', '-115.1372'],
            ],
            [
                ['69089280', '69090303', '11532'],
                ['81910016', '81910271', '5718'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'],
                ['id' => 2, 'countryId' => 1, 'name' => 'Nevada', '_maxmind' => 'USNV', 'abbreviation' => 'NV'],
            ],
            [
                ['id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'Los Angeles', 'lat' => 34.0522, 'lon' => -118.244, '_maxmind' => 11532],
                ['id' => 2, 'stateId' => 2, 'countryId' => 1, 'name' => 'Las Vegas', 'lat' => 36.175, 'lon' => -115.137, '_maxmind' => 5718],
            ],
            [],
            [
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 69089280, 'ipEnd' => 69090303],
                ['id' => 2, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 81910016, 'ipEnd' => 81910271],
            ]
        );
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'CA', 'California'],
                ['US', 'NV', 'Nevada'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['11111', 'US', 'CA', 'Los Angeles', '', '34.0522', '-118.2437'],
                ['8029', 'US', 'NV', '', '', '36.175', '-115.1372'],
                ['5555', 'US', 'NV', 'Las Vegas', '', '36.175', '-115.1372'],
            ],
            [
                ['69089280', '69090303', '11111'],
                ['70988544', '70988799', '11111'],
                ['81910016', '81910271', '5555'],
                ['202915072', '202915327', '5555'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'],
                ['id' => 2, 'countryId' => 1, 'name' => 'Nevada', '_maxmind' => 'USNV', 'abbreviation' => 'NV'],
            ],
            [
                ['id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'Los Angeles', 'lat' => 34.0522, 'lon' => -118.244, '_maxmind' => 11111],
                ['id' => 2, 'stateId' => 2, 'countryId' => 1, 'name' => 'Las Vegas', 'lat' => 36.175, 'lon' => -115.137, '_maxmind' => 5555],
            ],
            [],
            [
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 69089280, 'ipEnd' => 69090303],
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 70988544, 'ipEnd' => 70988799],
                ['id' => 2, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 81910016, 'ipEnd' => 81910271],
                ['id' => 2, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 202915072, 'ipEnd' => 202915327],
            ]
        );
    }

    public function testUpdateCityCode_circular() {
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'CA', 'California'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'],
                ['608', 'US', 'CA', 'San Francisco', '94124', '37.7312', '-122.3826'],
                ['11532', 'US', 'CA', 'Los Angeles', '', '34.0522', '-118.2437'],
                ['23653', 'US', 'CA', 'Long Beach', '', '33.767', '-118.1892'],
            ],
            [
                ['68444672', '68444735', '11101'],
                ['69089280', '69090303', '11532'],
                ['71797504', '71797759', '23653'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'],
            ],
            [
                ['id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419, '_maxmind' => 11101],
                ['id' => 2, 'stateId' => 1, 'countryId' => 1, 'name' => 'Los Angeles', 'lat' => 34.0522, 'lon' => -118.244, '_maxmind' => 11532],
                ['id' => 3, 'stateId' => 1, 'countryId' => 1, 'name' => 'Long Beach', 'lat' => 33.767, 'lon' => -118.189, '_maxmind' => 23653],
            ],
            [
                ['id' => 1, 'name' => '94124', 'cityId' => 1, 'lat' => 37.7312, 'lon' => -122.383],
            ],
            [
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68444672, 'ipEnd' => 68444735],
                ['id' => 2, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 69089280, 'ipEnd' => 69090303],
                ['id' => 3, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 71797504, 'ipEnd' => 71797759],
            ]
        );
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'CA', 'California'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['23653', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'],
                ['608', 'US', 'CA', 'San Francisco', '94124', '37.7312', '-122.3826'],
                ['11101', 'US', 'CA', 'Los Angeles', '', '34.0522', '-118.2437'],
                ['11532', 'US', 'CA', 'Long Beach', '', '33.767', '-118.1892'],
            ],
            [
                ['68444672', '68444735', '23653'],
                ['68444800', '68444927', '23653'],
                ['69089280', '69090303', '11101'],
                ['70988544', '70988799', '11101'],
                ['71797504', '71797759', '11532'],
                ['201805824', '201806079', '11532'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'],
            ],
            [
                ['id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419, '_maxmind' => 23653],
                ['id' => 2, 'stateId' => 1, 'countryId' => 1, 'name' => 'Los Angeles', 'lat' => 34.0522, 'lon' => -118.244, '_maxmind' => 11101],
                ['id' => 3, 'stateId' => 1, 'countryId' => 1, 'name' => 'Long Beach', 'lat' => 33.767, 'lon' => -118.189, '_maxmind' => 11532],
            ],
            [
                ['id' => 1, 'name' => '94124', 'cityId' => 1, 'lat' => 37.7312, 'lon' => -122.383],
            ],
            [
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68444672, 'ipEnd' => 68444735],
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68444800, 'ipEnd' => 68444927],
                ['id' => 2, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 69089280, 'ipEnd' => 69090303],
                ['id' => 2, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 70988544, 'ipEnd' => 70988799],
                ['id' => 3, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 71797504, 'ipEnd' => 71797759],
                ['id' => 3, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 201805824, 'ipEnd' => 201806079],
            ]
        );
    }

    public function testUpdateCityCode_swapAcrossRegions() {
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'CA', 'California'],
                ['US', 'NV', 'Nevada'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['11532', 'US', 'CA', 'Los Angeles', '', '34.0522', '-118.2437'],
                ['8029', 'US', 'NV', '', '', '36.175', '-115.1372'],
                ['5718', 'US', 'NV', 'Las Vegas', '', '36.175', '-115.1372'],
            ],
            [
                ['69089280', '69090303', '11532'],
                ['81910016', '81910271', '5718'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'],
                ['id' => 2, 'countryId' => 1, 'name' => 'Nevada', '_maxmind' => 'USNV', 'abbreviation' => 'NV'],
            ],
            [
                ['id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'Los Angeles', 'lat' => 34.0522, 'lon' => -118.244, '_maxmind' => 11532],
                ['id' => 2, 'stateId' => 2, 'countryId' => 1, 'name' => 'Las Vegas', 'lat' => 36.175, 'lon' => -115.137, '_maxmind' => 5718],
            ],
            [],
            [
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 69089280, 'ipEnd' => 69090303],
                ['id' => 2, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 81910016, 'ipEnd' => 81910271],
            ]
        );
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'CA', 'California'],
                ['US', 'NV', 'Nevada'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['5718', 'US', 'CA', 'Los Angeles', '', '34.0522', '-118.2437'],
                ['8029', 'US', 'NV', '', '', '36.175', '-115.1372'],
                ['11532', 'US', 'NV', 'Las Vegas', '', '36.175', '-115.1372'],
            ],
            [
                ['69089280', '69090303', '5718'],
                ['70988544', '70988799', '5718'],
                ['81910016', '81910271', '11532'],
                ['202915072', '202915327', '11532'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'],
                ['id' => 2, 'countryId' => 1, 'name' => 'Nevada', '_maxmind' => 'USNV', 'abbreviation' => 'NV'],
            ],
            [
                ['id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'Los Angeles', 'lat' => 34.0522, 'lon' => -118.244, '_maxmind' => 5718],
                ['id' => 2, 'stateId' => 2, 'countryId' => 1, 'name' => 'Las Vegas', 'lat' => 36.175, 'lon' => -115.137, '_maxmind' => 11532],
            ],
            [],
            [
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 69089280, 'ipEnd' => 69090303],
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 70988544, 'ipEnd' => 70988799],
                ['id' => 2, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 81910016, 'ipEnd' => 81910271],
                ['id' => 2, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 202915072, 'ipEnd' => 202915327],
            ]
        );
    }

    public function testUpdateCityCode_unsetRegion() {
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'CA', 'California'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'],
            ],
            [
                ['68444672', '68444735', '11101'],
                ['68444800', '68444927', '11101'],
            ],
            []
        );
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'CA', 'California'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['11102', 'US', '', 'San Francisco', '', '37.7749', '-122.4194'],
            ],
            [
                ['68444672', '68444735', '11102'],
                ['68444800', '68444927', '11102'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'],
            ],
            [
                ['id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419, '_maxmind' => 11101],
                ['id' => 2, 'stateId' => null, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419, '_maxmind' => 11102],
            ],
            [],
            [
                ['id' => 2, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68444672, 'ipEnd' => 68444735],
                ['id' => 2, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68444800, 'ipEnd' => 68444927],
            ]
        );
    }

    public function testUpdateCityCode_deletedRegion() {
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'CA', 'California'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'],
            ],
            [
                ['68444672', '68444735', '11101'],
                ['68444800', '68444927', '11101'],
            ],
            []
        );
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['11102', 'US', '', 'San Francisco', '', '37.7749', '-122.4194'],
            ],
            [
                ['68444672', '68444735', '11102'],
                ['68444800', '68444927', '11102'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'],
            ],
            [
                ['id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419, '_maxmind' => 11101],
                ['id' => 2, 'stateId' => null, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419, '_maxmind' => 11102],
            ],
            [],
            [
                ['id' => 2, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68444672, 'ipEnd' => 68444735],
                ['id' => 2, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68444800, 'ipEnd' => 68444927],
            ]
        );
    }

    public function testUpdateCityName() {
        $this->_import(
            [
                ['France', 'FR'],
            ],
            [
                ['FR', 'A7', 'Haute-Normandie'],
            ],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['436884', 'FR', 'A7', '', '', '49.4333', '1.0833'],
                ['50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'],
            ],
            [],
            []
        );
        $this->_import(
            [
                ['France', 'FR'],
            ],
            [
                ['FR', 'A7', 'Haute-Normandie'],
            ],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['436884', 'FR', 'A7', '', '', '49.4333', '1.0833'],
                ['50221', 'FR', 'A7', 'Marseille', '', '49.5', '0.1333'],
            ],
            [],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'FR', 'name' => 'France'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRA7', 'abbreviation' => null],
            ],
            [
                ['id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'Marseille', 'lat' => 49.5, 'lon' => 0.1333, '_maxmind' => 50221],
            ],
            [],
            []
        );
    }

    public function testUpdateCityName_deletedRegion() {
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'CA', 'California'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'],
            ],
            [
                ['68444672', '68444735', '11101'],
                ['68444800', '68444927', '11101'],
            ],
            []
        );
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['11101', 'US', '', 'San Pedro', '', '37.7749', '-122.4194'],
            ],
            [
                ['68444672', '68444735', '11101'],
                ['68444800', '68444927', '11101'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'],
            ],
            [
                ['id' => 1, 'stateId' => null, 'countryId' => 1, 'name' => 'San Pedro', 'lat' => 37.7749, 'lon' => -122.419, '_maxmind' => 11101],
            ],
            [],
            [
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68444672, 'ipEnd' => 68444735],
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68444800, 'ipEnd' => 68444927],
            ]
        );
    }

    public function testUpdateCityRegion() {
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'CA', 'California'],
                ['US', 'HI', 'Hawaii'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'],
                ['14550', 'US', 'HI', '', '', '21.3629', '-157.8727'],
            ],
            [
                ['68444672', '68444735', '11101'],
                ['68444800', '68444927', '11101'],
            ],
            []
        );
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'CA', 'California'],
                ['US', 'HI', 'Hawaii'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['14550', 'US', 'HI', '', '', '21.3629', '-157.8727'],
                ['11101', 'US', 'HI', 'San Francisco', '', '37.7749', '-122.4194'],
            ],
            [
                ['68444672', '68444735', '11101'],
                ['68444800', '68444927', '11101'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'],
                ['id' => 2, 'countryId' => 1, 'name' => 'Hawaii', '_maxmind' => 'USHI', 'abbreviation' => 'HI'],
            ],
            [
                ['id' => 1, 'stateId' => 2, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419, '_maxmind' => 11101],
            ],
            [],
            [
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68444672, 'ipEnd' => 68444735],
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68444800, 'ipEnd' => 68444927],
            ]
        );
    }

    public function testUpdateCityRegion_set() {
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'CA', 'California'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['11101', 'US', '', 'San Francisco', '', '37.7749', '-122.4194'],
            ],
            [
                ['68444672', '68444735', '11101'],
                ['68444800', '68444927', '11101'],
            ],
            []
        );
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'CA', 'California'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'],
            ],
            [
                ['68444672', '68444735', '11101'],
                ['68444800', '68444927', '11101'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'],
            ],
            [
                ['id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419, '_maxmind' => 11101],
            ],
            [],
            [
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68444672, 'ipEnd' => 68444735],
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68444800, 'ipEnd' => 68444927],
            ]
        );
    }

    public function testUpdateCityRegion_unset() {
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'CA', 'California'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'],
            ],
            [
                ['68444672', '68444735', '11101'],
                ['68444800', '68444927', '11101'],
            ],
            []
        );
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'CA', 'California'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['11101', 'US', '', 'San Francisco', '', '37.7749', '-122.4194'],
            ],
            [
                ['68444672', '68444735', '11101'],
                ['68444800', '68444927', '11101'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'],
            ],
            [
                ['id' => 1, 'stateId' => null, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419, '_maxmind' => 11101],
            ],
            [],
            [
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68444672, 'ipEnd' => 68444735],
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68444800, 'ipEnd' => 68444927],
            ]
        );
    }

    public function testUpdateCityRegion_newRegion() {
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'CA', 'California'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'],
            ],
            [
                ['68444672', '68444735', '11101'],
                ['68444800', '68444927', '11101'],
            ],
            []
        );
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'CA', 'California'],
                ['US', 'HI', 'Hawaii'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['14550', 'US', 'HI', '', '', '21.3629', '-157.8727'],
                ['11101', 'US', 'HI', 'San Francisco', '', '37.7749', '-122.4194'],
            ],
            [
                ['68444672', '68444735', '11101'],
                ['68444800', '68444927', '11101'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'],
                ['id' => 2, 'countryId' => 1, 'name' => 'Hawaii', '_maxmind' => 'USHI', 'abbreviation' => 'HI'],
            ],
            [
                ['id' => 1, 'stateId' => 2, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419, '_maxmind' => 11101],
            ],
            [],
            [
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68444672, 'ipEnd' => 68444735],
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68444800, 'ipEnd' => 68444927],
            ]
        );
    }

    public function testUpdateCityRegion_deletedRegion() {
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'CA', 'California'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'],
            ],
            [
                ['68444672', '68444735', '11101'],
                ['68444800', '68444927', '11101'],
            ],
            []
        );
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'HI', 'Hawaii'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['14550', 'US', 'HI', '', '', '21.3629', '-157.8727'],
                ['11101', 'US', 'HI', 'San Francisco', '', '37.7749', '-122.4194'],
            ],
            [
                ['68444672', '68444735', '11101'],
                ['68444800', '68444927', '11101'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'],
                ['id' => 2, 'countryId' => 1, 'name' => 'Hawaii', '_maxmind' => 'USHI', 'abbreviation' => 'HI'],
            ],
            [
                ['id' => 1, 'stateId' => 2, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419, '_maxmind' => 11101],
            ],
            [],
            [
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68444672, 'ipEnd' => 68444735],
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68444800, 'ipEnd' => 68444927],
            ]
        );
    }

    public function testUpdateCityRegion_regionUpdatedCode() {
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'CA', 'California'],
                ['US', 'HI', 'Hawaii'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'],
                ['14550', 'US', 'HI', '', '', '21.3629', '-157.8727'],
            ],
            [
                ['68444672', '68444735', '11101'],
                ['68444800', '68444927', '11101'],
            ],
            []
        );
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'CA', 'California'],
                ['US', 'HJ', 'Hawaii'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['14550', 'US', 'HJ', '', '', '21.3629', '-157.8727'],
                ['11101', 'US', 'HJ', 'San Francisco', '', '37.7749', '-122.4194'],
            ],
            [
                ['68444672', '68444735', '11101'],
                ['68444800', '68444927', '11101'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'],
                ['id' => 2, 'countryId' => 1, 'name' => 'Hawaii', '_maxmind' => 'USHJ', 'abbreviation' => 'HJ'],
            ],
            [
                ['id' => 1, 'stateId' => 2, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419, '_maxmind' => 11101],
            ],
            [],
            [
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68444672, 'ipEnd' => 68444735],
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68444800, 'ipEnd' => 68444927],
            ]
        );
    }

    public function testUpdateCityRegion_oldRegionUpdatedCode() {
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'CA', 'California'],
                ['US', 'HI', 'Hawaii'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'],
                ['14550', 'US', 'HI', '', '', '21.3629', '-157.8727'],
            ],
            [
                ['68444672', '68444735', '11101'],
                ['68444800', '68444927', '11101'],
            ],
            []
        );
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'CB', 'California'],
                ['US', 'HI', 'Hawaii'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CB', '', '', '34.0522', '-118.243'],
                ['14550', 'US', 'HI', '', '', '21.3629', '-157.8727'],
                ['11101', 'US', 'HI', 'San Francisco', '', '37.7749', '-122.4194'],
            ],
            [
                ['68444672', '68444735', '11101'],
                ['68444800', '68444927', '11101'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCB', 'abbreviation' => 'CB'],
                ['id' => 2, 'countryId' => 1, 'name' => 'Hawaii', '_maxmind' => 'USHI', 'abbreviation' => 'HI'],
            ],
            [
                ['id' => 1, 'stateId' => 2, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419, '_maxmind' => 11101],
            ],
            [],
            [
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68444672, 'ipEnd' => 68444735],
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68444800, 'ipEnd' => 68444927],
            ]
        );
    }

    public function testUpdateCityRegion_regionsUpdatedCode() {
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'CA', 'California'],
                ['US', 'HI', 'Hawaii'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'],
                ['14550', 'US', 'HI', '', '', '21.3629', '-157.8727'],
            ],
            [
                ['68444672', '68444735', '11101'],
                ['68444800', '68444927', '11101'],
            ],
            []
        );
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'CB', 'California'],
                ['US', 'HJ', 'Hawaii'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CB', '', '', '34.0522', '-118.243'],
                ['14550', 'US', 'HJ', '', '', '21.3629', '-157.8727'],
                ['11101', 'US', 'HJ', 'San Francisco', '', '37.7749', '-122.4194'],
            ],
            [
                ['68444672', '68444735', '11101'],
                ['68444800', '68444927', '11101'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCB', 'abbreviation' => 'CB'],
                ['id' => 2, 'countryId' => 1, 'name' => 'Hawaii', '_maxmind' => 'USHJ', 'abbreviation' => 'HJ'],
            ],
            [
                ['id' => 1, 'stateId' => 2, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419, '_maxmind' => 11101],
            ],
            [],
            [
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68444672, 'ipEnd' => 68444735],
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68444800, 'ipEnd' => 68444927],
            ]
        );
    }

    public function testUpdateCityRegion_regionsSwappedCode() {
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'CA', 'California'],
                ['US', 'HI', 'Hawaii'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'],
                ['14550', 'US', 'HI', '', '', '21.3629', '-157.8727'],
            ],
            [
                ['68444672', '68444735', '11101'],
                ['68444800', '68444927', '11101'],
            ],
            []
        );
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'HI', 'California'],
                ['US', 'CA', 'Hawaii'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'HI', '', '', '34.0522', '-118.243'],
                ['14550', 'US', 'CA', '', '', '21.3629', '-157.8727'],
                ['11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'],
            ],
            [
                ['68444672', '68444735', '11101'],
                ['68444800', '68444927', '11101'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USHI', 'abbreviation' => 'HI'],
                ['id' => 2, 'countryId' => 1, 'name' => 'Hawaii', '_maxmind' => 'USCA', 'abbreviation' => 'CA'],
            ],
            [
                ['id' => 1, 'stateId' => 2, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419, '_maxmind' => 11101],
            ],
            [],
            [
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68444672, 'ipEnd' => 68444735],
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68444800, 'ipEnd' => 68444927],
            ]
        );
    }

    public function testUpdateCityRegion_replaceDuplicateCity() {
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'CA', 'California'],
                ['US', 'HI', 'Hawaii'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'],
                ['5718', 'US', 'CA', 'Los Angeles', '', '34.0522', '-118.2437'],
                ['14550', 'US', 'HI', '', '', '21.3629', '-157.8727'],
                ['11102', 'US', 'HI', 'San Francisco', '', '37.7749', '-122.4194'],
                ['5719', 'US', 'HI', 'Los Angeles', '', '34.0522', '-118.2437'],
            ],
            [
                ['68444672', '68444735', '11101'],
                ['68444800', '68444927', '11101'],
                ['68445000', '68445100', '11102'],
                ['69089280', '69090303', '5718'],
                ['70988544', '70988799', '5718'],
                ['70989000', '70989100', '5719'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'],
                ['id' => 2, 'countryId' => 1, 'name' => 'Hawaii', '_maxmind' => 'USHI', 'abbreviation' => 'HI'],
            ],
            [
                ['id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419, '_maxmind' => 11101],
                ['id' => 2, 'stateId' => 1, 'countryId' => 1, 'name' => 'Los Angeles', 'lat' => 34.0522, 'lon' => -118.244, '_maxmind' => 5718],
                ['id' => 3, 'stateId' => 2, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419, '_maxmind' => 11102],
                ['id' => 4, 'stateId' => 2, 'countryId' => 1, 'name' => 'Los Angeles', 'lat' => 34.0522, 'lon' => -118.244, '_maxmind' => 5719],
            ],
            [],
            [
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68444672, 'ipEnd' => 68444735],
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68444800, 'ipEnd' => 68444927],
                ['id' => 3, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68445000, 'ipEnd' => 68445100],
                ['id' => 2, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 69089280, 'ipEnd' => 69090303],
                ['id' => 2, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 70988544, 'ipEnd' => 70988799],
                ['id' => 4, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 70989000, 'ipEnd' => 70989100],
            ]
        );
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'CA', 'California'],
                ['US', 'HI', 'Hawaii'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['5719', 'US', 'CA', 'Los Angeles', '', '34.0522', '-118.2437'],
                ['14550', 'US', 'HI', '', '', '21.3629', '-157.8727'],
                ['11101', 'US', 'HI', 'San Francisco', '', '37.7749', '-122.4194'],
            ],
            [
                ['68444672', '68444735', '11101'],
                ['68444800', '68444927', '11101'],
                ['70989000', '70989100', '5719'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'],
                ['id' => 2, 'countryId' => 1, 'name' => 'Hawaii', '_maxmind' => 'USHI', 'abbreviation' => 'HI'],
            ],
            [
                ['id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419, '_maxmind' => 11101],
                ['id' => 2, 'stateId' => 1, 'countryId' => 1, 'name' => 'Los Angeles', 'lat' => 34.0522, 'lon' => -118.244, '_maxmind' => 5719],
                ['id' => 3, 'stateId' => 2, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419, '_maxmind' => 11101],
                ['id' => 4, 'stateId' => 2, 'countryId' => 1, 'name' => 'Los Angeles', 'lat' => 34.0522, 'lon' => -118.244, '_maxmind' => 5719],
            ],
            [],
            [
                ['id' => 3, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68444672, 'ipEnd' => 68444735],
                ['id' => 3, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68444800, 'ipEnd' => 68444927],
                ['id' => 2, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 70989000, 'ipEnd' => 70989100],
            ]
        );
    }

    public function testUpdateCityRegion_replaceDuplicateCityInUnknownRegion() {
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'CA', 'California'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['11102', 'US', '', 'San Francisco', '', '37.7749', '-122.4194'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'],
            ],
            [
                ['68444672', '68444735', '11101'],
                ['68444800', '68444927', '11101'],
                ['68445000', '68445100', '11102'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'],
            ],
            [
                ['id' => 1, 'stateId' => null, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419, '_maxmind' => 11102],
                ['id' => 2, 'stateId' => 1, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419, '_maxmind' => 11101],
            ],
            [],
            [
                ['id' => 2, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68444672, 'ipEnd' => 68444735],
                ['id' => 2, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68444800, 'ipEnd' => 68444927],
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68445000, 'ipEnd' => 68445100],
            ]
        );
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'CA', 'California'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['11101', 'US', '', 'San Francisco', '', '37.7749', '-122.4194'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
            ],
            [
                ['68444672', '68444735', '11101'],
                ['68444800', '68444927', '11101'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'],
            ],
            [
                ['id' => 1, 'stateId' => null, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419, '_maxmind' => 11101],
                ['id' => 2, 'stateId' => 1, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419, '_maxmind' => 11101],
            ],
            [],
            [
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68444672, 'ipEnd' => 68444735],
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68444800, 'ipEnd' => 68444927],
            ]
        );
    }

    public function testUpdateCityRegion_replaceDuplicateCityFromUnknownRegion() {
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'CA', 'California'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['11102', 'US', '', 'San Francisco', '', '37.7749', '-122.4194'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'],
            ],
            [
                ['68444672', '68444735', '11101'],
                ['68444800', '68444927', '11101'],
                ['68445000', '68445100', '11102'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'],
            ],
            [
                ['id' => 1, 'stateId' => null, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419, '_maxmind' => 11102],
                ['id' => 2, 'stateId' => 1, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419, '_maxmind' => 11101],
            ],
            [],
            [
                ['id' => 2, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68444672, 'ipEnd' => 68444735],
                ['id' => 2, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68444800, 'ipEnd' => 68444927],
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68445000, 'ipEnd' => 68445100],
            ]
        );
        $this->_import(
            [
                ['United States', 'US'],
            ],
            [
                ['US', 'CA', 'California'],
            ],
            [
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['11102', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'],
            ],
            [
                ['68444672', '68444735', '11102'],
                ['68444800', '68444927', '11102'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'],
            ],
            [
                ['id' => 1, 'stateId' => null, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419, '_maxmind' => 11102],
                ['id' => 2, 'stateId' => 1, 'countryId' => 1, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419, '_maxmind' => 11102],
            ],
            [],
            [
                ['id' => 2, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68444672, 'ipEnd' => 68444735],
                ['id' => 2, 'level' => CM_Model_Location::LEVEL_CITY, 'ipStart' => 68444800, 'ipEnd' => 68444927],
            ]
        );
    }

    public function testUpdateCityRegion_duplicateRegion() {
        $this->_import(
            [
                ['France', 'FR'],
                ['United States', 'US'],
            ],
            [
                ['FR', 'A7', 'Haute-Normandie'],
                ['US', 'CA', 'California'],
            ],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['436884', 'FR', 'A7', '', '', '49.4333', '1.0833'],
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['11101', 'US', 'CA', 'San Francisco', '', '37.7749', '-122.4194'],
            ],
            [],
            []
        );
        $this->_import(
            [
                ['France', 'FR'],
                ['United States', 'US'],
            ],
            [
                ['FR', 'A7', 'Haute-Normandie'],
                ['FR', 'A8', 'Haute-Normandie'],
                ['US', 'CA', 'California'],
                ['US', 'CB', 'California'],
            ],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['436884', 'FR', 'A7', '', '', '49.4333', '1.0833'],
                ['436884', 'FR', 'A8', '', '', '49.4333', '1.0833'],
                ['223', 'US', '', '', '', '38', '-97'],
                ['2221', 'US', 'CA', '', '', '34.0522', '-118.243'],
                ['2221', 'US', 'CB', '', '', '34.0522', '-118.243'],
                ['11101', 'US', 'CB', 'San Francisco', '', '37.7749', '-122.4194'],
            ],
            [],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'FR', 'name' => 'France'],
                ['id' => 2, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRA7', 'abbreviation' => null],
                ['id' => 2, 'countryId' => 2, 'name' => 'California', '_maxmind' => 'USCA', 'abbreviation' => 'CA'],
                ['id' => 3, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRA8', 'abbreviation' => null],
                ['id' => 4, 'countryId' => 2, 'name' => 'California', '_maxmind' => 'USCB', 'abbreviation' => 'CB'],
            ],
            [
                ['id' => 1, 'stateId' => 4, 'countryId' => 2, 'name' => 'San Francisco', 'lat' => 37.7749, 'lon' => -122.419, '_maxmind' => 11101],
            ],
            [],
            []
        );
    }

    public function testAddZipCode() {
        $this->_import(
            [
                ['France', 'FR'],
            ],
            [
                ['FR', 'A7', 'Haute-Normandie'],
            ],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['436884', 'FR', 'A7', '', '', '49.4333', '1.0833'],
                ['50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'],
            ],
            [],
            []
        );
        $this->_import(
            [
                ['France', 'FR'],
            ],
            [
                ['FR', 'A7', 'Haute-Normandie'],
            ],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['436884', 'FR', 'A7', '', '', '49.4333', '1.0833'],
                ['50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'],
                ['384603', 'FR', 'A7', 'Le Havre', '76620', '49.4938', '0.1077'],
            ],
            [],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'FR', 'name' => 'France'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRA7', 'abbreviation' => null],
            ],
            [
                ['id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'Le Havre', 'lat' => 49.5, 'lon' => 0.1333, '_maxmind' => 50221],
            ],
            [
                ['id' => 1, 'name' => '76620', 'cityId' => 1, 'lat' => 49.4938, 'lon' => 0.1077],
            ],
            []
        );
        $this->_import(
            [
                ['France', 'FR'],
            ],
            [
                ['FR', 'A7', 'Haute-Normandie'],
            ],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['436884', 'FR', 'A7', '', '', '49.4333', '1.0833'],
                ['50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'],
                ['384564', 'FR', 'A7', 'Le Havre', '76600', '49.4938', '0.1077'],
                ['385389', 'FR', 'A7', 'Le Havre', '76610', '49.5213', '0.1581'],
                ['384603', 'FR', 'A7', 'Le Havre', '76620', '49.4938', '0.1077'],
            ],
            [
                ['522357760', '522358015', '384603'],
                ['1304630016', '1304630271', '384603'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'FR', 'name' => 'France'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRA7', 'abbreviation' => null],
            ],
            [
                ['id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'Le Havre', 'lat' => 49.5, 'lon' => 0.1333, '_maxmind' => 50221],
            ],
            [
                ['id' => 1, 'name' => '76620', 'cityId' => 1, 'lat' => 49.4938, 'lon' => 0.1077],
                ['id' => 2, 'name' => '76600', 'cityId' => 1, 'lat' => 49.4938, 'lon' => 0.1077],
                ['id' => 3, 'name' => '76610', 'cityId' => 1, 'lat' => 49.5213, 'lon' => 0.1581],
            ],
            [
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_ZIP, 'ipStart' => 522357760, 'ipEnd' => 522358015],
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_ZIP, 'ipStart' => 1304630016, 'ipEnd' => 1304630271],
            ]
        );
    }

    public function testRemoveZipCode() {
        $this->_import(
            [
                ['France', 'FR'],
            ],
            [
                ['FR', 'A7', 'Haute-Normandie'],
            ],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['436884', 'FR', 'A7', '', '', '49.4333', '1.0833'],
                ['50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'],
                ['384564', 'FR', 'A7', 'Le Havre', '76600', '49.4938', '0.1077'],
                ['385389', 'FR', 'A7', 'Le Havre', '76610', '49.5213', '0.1581'],
                ['384603', 'FR', 'A7', 'Le Havre', '76620', '49.4938', '0.1077'],
                ['484564', 'FR', 'A7', 'Le Havre', '76630', '49.4938', '0.1077'],
                ['485389', 'FR', 'A7', 'Le Havre', '76640', '49.5213', '0.1581'],
                ['484603', 'FR', 'A7', 'Le Havre', '76650', '49.4938', '0.1077'],
            ],
            [],
            []
        );
        $this->_import(
            [
                ['France', 'FR'],
            ],
            [
                ['FR', 'A7', 'Haute-Normandie'],
            ],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['436884', 'FR', 'A7', '', '', '49.4333', '1.0833'],
                ['50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'],
                ['384564', 'FR', 'A7', 'Le Havre', '76600', '49.4938', '0.1077'],
                ['385389', 'FR', 'A7', 'Le Havre', '76610', '49.5213', '0.1581'],
                ['384603', 'FR', 'A7', 'Le Havre', '76620', '49.4938', '0.1077'],
                ['484564', 'FR', 'A7', 'Le Havre', '76630', '49.4938', '0.1077'],
                ['485389', 'FR', 'A7', 'Le Havre', '76640', '49.5213', '0.1581'],
            ],
            [],
            []
        );
        $this->_import(
            [
                ['France', 'FR'],
            ],
            [
                ['FR', 'A7', 'Haute-Normandie'],
            ],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['436884', 'FR', 'A7', '', '', '49.4333', '1.0833'],
                ['50221', 'FR', 'A7', 'Le Havre', '', '49.5', '0.1333'],
            ],
            [],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'FR', 'name' => 'France'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'Haute-Normandie', '_maxmind' => 'FRA7', 'abbreviation' => null],
            ],
            [
                ['id' => 1, 'stateId' => 1, 'countryId' => 1, 'name' => 'Le Havre', 'lat' => 49.5, 'lon' => 0.1333, '_maxmind' => 50221],
            ],
            [
                ['id' => 1, 'name' => '76600', 'cityId' => 1, 'lat' => 49.4938, 'lon' => 0.1077],
                ['id' => 2, 'name' => '76610', 'cityId' => 1, 'lat' => 49.5213, 'lon' => 0.1581],
                ['id' => 3, 'name' => '76620', 'cityId' => 1, 'lat' => 49.4938, 'lon' => 0.1077],
                ['id' => 4, 'name' => '76630', 'cityId' => 1, 'lat' => 49.4938, 'lon' => 0.1077],
                ['id' => 5, 'name' => '76640', 'cityId' => 1, 'lat' => 49.5213, 'lon' => 0.1581],
                ['id' => 6, 'name' => '76650', 'cityId' => 1, 'lat' => 49.4938, 'lon' => 0.1077],
            ],
            []
        );
    }

    public function testOverlappingIpBlocks_noOverlapping() {
        $this->_import(
            [
                ['France', 'FR'],
                ['United States', 'US'],
            ],
            [],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['223', 'US', '', '', '', '38', '-97'],
            ],
            [
                ['33555968', '33556223', '75'],
                ['266578176', '266578431', '223'],
                ['266586368', '266586623', '223'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'FR', 'name' => 'France'],
                ['id' => 2, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [],
            [],
            [],
            [
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_COUNTRY, 'ipStart' => 33555968, 'ipEnd' => 33556223],
                ['id' => 2, 'level' => CM_Model_Location::LEVEL_COUNTRY, 'ipStart' => 266578176, 'ipEnd' => 266578431],
                ['id' => 2, 'level' => CM_Model_Location::LEVEL_COUNTRY, 'ipStart' => 266586368, 'ipEnd' => 266586623],
            ]
        );
        $this->assertSame(false, strpos($this->_outputStream->read(), 'Overlapping IP blocks:'));
    }

    public function testOverlappingIpBlocks_sameIpStart() {
        $this->_import(
            [
                ['France', 'FR'],
                ['United States', 'US'],
            ],
            [],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['223', 'US', '', '', '', '38', '-97'],
            ],
            [
                ['33555968', '33556223', '75'],
                ['33555968', '33556243', '75'],
                ['266578176', '266578431', '223'],
                ['266586368', '266586623', '223'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'FR', 'name' => 'France'],
                ['id' => 2, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [],
            [],
            [],
            [
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_COUNTRY, 'ipStart' => 33555968, 'ipEnd' => 33556223],
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_COUNTRY, 'ipStart' => 33555968, 'ipEnd' => 33556243],
                ['id' => 2, 'level' => CM_Model_Location::LEVEL_COUNTRY, 'ipStart' => 266578176, 'ipEnd' => 266578431],
                ['id' => 2, 'level' => CM_Model_Location::LEVEL_COUNTRY, 'ipStart' => 266586368, 'ipEnd' => 266586623],
            ]
        );
        $this->assertNotSame(false, strpos($this->_outputStream->read(), "Overlapping IP blocks:\n ! 33555968-33556223 and 33555968-33556243\n\n *"));
    }

    public function testOverlappingIpBlocks_inclusion() {
        $this->_import(
            [
                ['France', 'FR'],
                ['United States', 'US'],
            ],
            [],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['223', 'US', '', '', '', '38', '-97'],
            ],
            [
                ['33555968', '33556223', '75'],
                ['33556000', '33556200', '75'],
                ['266578200', '266578400', '223'],
                ['266578176', '266578431', '223'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'FR', 'name' => 'France'],
                ['id' => 2, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [],
            [],
            [],
            [
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_COUNTRY, 'ipStart' => 33555968, 'ipEnd' => 33556223],
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_COUNTRY, 'ipStart' => 33556000, 'ipEnd' => 33556200],
                ['id' => 2, 'level' => CM_Model_Location::LEVEL_COUNTRY, 'ipStart' => 266578200, 'ipEnd' => 266578400],
                ['id' => 2, 'level' => CM_Model_Location::LEVEL_COUNTRY, 'ipStart' => 266578176, 'ipEnd' => 266578431],
            ]
        );
        $this->assertNotSame(false, strpos($this->_outputStream->read(), "Overlapping IP blocks:\n ! 266578200-266578400 and 266578176-266578431\n ! 33556000-33556200 and 33555968-33556223\n\n *"));
    }

    public function testOverlappingIpBlocks_overlapping() {
        $this->_import(
            [
                ['France', 'FR'],
                ['United States', 'US'],
            ],
            [],
            [
                ['75', 'FR', '', '', '', '48.86', '2.35'],
                ['223', 'US', '', '', '', '38', '-97'],
            ],
            [
                ['33555968', '33556223', '75'],
                ['33556000', '33557000', '75'],
                ['266578200', '266578500', '223'],
                ['266578176', '266578431', '223'],
            ],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'FR', 'name' => 'France'],
                ['id' => 2, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [],
            [],
            [],
            [
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_COUNTRY, 'ipStart' => 33555968, 'ipEnd' => 33556223],
                ['id' => 1, 'level' => CM_Model_Location::LEVEL_COUNTRY, 'ipStart' => 33556000, 'ipEnd' => 33557000],
                ['id' => 2, 'level' => CM_Model_Location::LEVEL_COUNTRY, 'ipStart' => 266578200, 'ipEnd' => 266578500],
                ['id' => 2, 'level' => CM_Model_Location::LEVEL_COUNTRY, 'ipStart' => 266578176, 'ipEnd' => 266578431],
            ]
        );
        $this->assertNotSame(false, strpos($this->_outputStream->read(), "Overlapping IP blocks:\n ! 266578176-266578431 and 266578200-266578500\n ! 33555968-33556223 and 33556000-33557000\n\n *"));
    }

    /**
     * @expectedException CM_Exception
     * @expectedExceptionMessage Unknown country code
     */
    public function testExceptionAddRegionInUnknownCountry() {
        $this->_import(
            [],
            [
                ['FR', 'A7', 'Haute-Normandie'],
            ],
            [],
            [],
            []
        );
    }

    /**
     * @expectedException CM_Exception
     * @expectedExceptionMessage Unknown country code
     */
    public function testExceptionUpdateRegionCodeInUnknownCountry() {
        $this->_import(
            [
                ['France', 'FR'],
            ],
            [
                ['FR', 'A7', 'Haute-Normandie'],
            ],
            [],
            [],
            []
        );
        $this->_import(
            [],
            [
                ['FR', 'AA', 'Haute-Normandie'],
            ],
            [],
            [],
            []
        );
    }

    /**
     * @expectedException CM_Exception
     * @expectedExceptionMessage Unknown country code
     */
    public function testExceptionUpdateRegionNameInUnknownCountry() {
        $this->_import(
            [
                ['France', 'FR'],
            ],
            [
                ['FR', 'A7', 'Haute-Normandie'],
            ],
            [],
            [],
            []
        );
        $this->_import(
            [],
            [
                ['FR', 'A7', 'Normandie'],
            ],
            [],
            [],
            []
        );
    }

    public function testNormalizeCountryName() {
        $this->_import(
            array(
                ['Korea, Democratic People\'s Republic of', 'KP'],
                ['Korea, Republic of', 'KR'],
                ['Virgin Islands, British', 'VG'],
                ['Virgin Islands, U.S.', 'VI'],
                ['Saint Martin (French part)', 'MF'],
                ['Congo, The Democratic Republic of the', 'CD'],
            ),
            [],
            [],
            [],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'KP', 'name' => 'North Korea'],
                ['id' => 2, 'abbreviation' => 'KR', 'name' => 'Korea'],
                ['id' => 3, 'abbreviation' => 'VG', 'name' => 'British Virgin Islands'],
                ['id' => 4, 'abbreviation' => 'VI', 'name' => 'Virgin Islands'],
                ['id' => 5, 'abbreviation' => 'MF', 'name' => 'Saint Martin'],
                ['id' => 6, 'abbreviation' => 'CD', 'name' => 'Congo'],
            ],
            [],
            [],
            [],
            []
        );
    }

    public function testNormalizeRegionName() {
        $this->_import(
            [
                ['United Kingdom', 'GB'],
                ['United States', 'US'],
            ],
            [
                ['GB', 'H9', 'London, City of'],
                ['US', 'AE', 'Armed Forces Europe, Middle East, & Canada'],
            ],
            [],
            [],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'GB', 'name' => 'United Kingdom'],
                ['id' => 2, 'abbreviation' => 'US', 'name' => 'United States'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'London', '_maxmind' => 'GBH9', 'abbreviation' => null],
                ['id' => 2, 'countryId' => 2, 'name' => 'Armed Forces Europe, Middle East, & Canada', '_maxmind' => 'USAE', 'abbreviation' => 'AE'],
            ],
            [],
            [],
            []
        );
    }

    public function testNormalizeCityName() {
        $this->_import(
            [
                ['Lithuania', 'LT'],
                ['Taiwan, Province of China', 'TW'],
            ],
            [
                ['LT', '61', 'Siauliu Apskritis'],
                ['TW', '4', 'T\'ai-wan'],
            ],
            array(
                array('295514', 'LT', '61', '(( Mantjurgiai ))', '', '49.5', '0.1333'),
                array('298374', 'TW', '4', 'Erhchiehtsun (1)', '', '24.7833', '121.6667'),
                array('298375', 'TW', '4', 'Erhchiehtsun (1)', '12345', '24.7833', '121.6667'),
                array('300421', 'TW', '4', 'Chihtan (2)', '12345', '24.9333', '121.65'),
            ),
            [],
            []
        );
        $this->_verify(
            [
                ['id' => 1, 'abbreviation' => 'LT', 'name' => 'Lithuania'],
                ['id' => 2, 'abbreviation' => 'TW', 'name' => 'Taiwan'],
            ],
            [
                ['id' => 1, 'countryId' => 1, 'name' => 'Siauliu Apskritis', '_maxmind' => 'LT61', 'abbreviation' => null],
                ['id' => 2, 'countryId' => 2, 'name' => 'T\'ai-wan', '_maxmind' => 'TW4', 'abbreviation' => null],
            ],
            [],
            [],
            []
        );
    }

    protected function _import($countryDataMock, $regionDataMock, $locationDataMock, $ipDataMock, $regionListLegacyMock) {
        $ipBlocksReaderMock = $this->_getReaderMock($ipDataMock, "Copyright (c) 2011 MaxMind Inc.  All Rights Reserved.\nstartIpNum,endIpNum,locId\n");
        $locationReaderMock = $this->_getReaderMock($locationDataMock, "Copyright (c) 2012 MaxMind LLC.  All Rights Reserved.\nlocId,country,region,city,postalCode,latitude,longitude,metroCode,areaCode\n");
        $maxMind = $this->getMock('CMService_MaxMind',
            ['_getCountryData', '_getRegionData', '_getLocationReader', '_getIpBlocksReader', '_getRegionListLegacy', '_updateSearchIndex']);
        $maxMind->expects($this->any())->method('_getCountryData')->will($this->returnValue($countryDataMock));
        $maxMind->expects($this->any())->method('_getRegionData')->will($this->returnValue($regionDataMock));
        $maxMind->expects($this->any())->method('_getLocationReader')->will($this->returnValue($locationReaderMock));
        $maxMind->expects($this->any())->method('_getIpBlocksReader')->will($this->returnValue($ipBlocksReaderMock));
        $maxMind->expects($this->any())->method('_getRegionListLegacy')->will($this->returnValue($regionListLegacyMock));
        $maxMind->expects($this->any())->method('_updateSearchIndex')->will($this->returnValue(null));
        /** @var CMService_MaxMind $maxMind */
        $maxMind->setStreamOutput($this->_outputStream);
        $maxMind->setStreamError($this->_errorStream);
        $maxMind->setVerbose(true);
        $maxMind->upgrade();
    }

    protected function _verify($countryDataExpected, $regionDataExpected, $cityDataExpected, $zipCodeDataExpected, $ipDataExpected) {
        $countryDataActual = CM_Db_Db::select('cm_model_location_country', '*')->fetchAll();
        $this->assertEquals($countryDataExpected, $countryDataActual);
        $regionDataActual = CM_Db_Db::select('cm_model_location_state', '*')->fetchAll();
        $this->assertEquals($regionDataExpected, $regionDataActual);
        $cityDataActual = CM_Db_Db::select('cm_model_location_city', '*')->fetchAll();
        $this->assertEquals($cityDataExpected, $cityDataActual);
        $zipCodeDataActual = CM_Db_Db::select('cm_model_location_zip', '*')->fetchAll();
        $this->assertEquals($zipCodeDataExpected, $zipCodeDataActual);
        $ipDataActual = CM_Db_Db::select('cm_model_location_ip', '*')->fetchAll();
        $this->assertEquals($ipDataExpected, $ipDataActual);
    }

    /**
     * @param array       $data
     * @param string|null $header
     * @return array stream, lineCount
     */
    private function _getReaderMock(array $data, $header = null) {
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $header);
        foreach ($data as $row) {
            fputcsv($stream, $row);
        }
        rewind($stream);
        $lineCount = count($data) + substr_count($header, "\n");
        return ['stream' => $stream, 'lineCount' => $lineCount];
    }
}
