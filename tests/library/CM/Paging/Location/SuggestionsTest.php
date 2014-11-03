<?php

class CM_Paging_Location_SuggestionsTest extends CMTest_TestCase {

    /** @var CM_Elasticsearch_Type_Location */
    protected static $_type;

    /** @var CM_Elasticsearch_Index_Cli */
    protected static $_searchIndexCli;

    /** @var int */
    protected static $_countryId1, $_countryId2, $_stateId1, $_stateId4, $_cityId, $_cityIdNext;

    public static function setUpBeforeClass() {
        $country1 = CM_Model_Location_Country::create('Andorra', 'AD');
        $state1 = CM_Model_Location_State::create($country1, 'Canillo', null, 'AD02');
        $state2 = CM_Model_Location_State::create($country1, 'Encamp', null, 'AD03');
        $state3 = CM_Model_Location_State::create($country1, 'La Massana', null, 'AD04');
        $city = CM_Model_Location_City::create($country1, $state1, 'Canillo', '42.567', '1.6', '146765');
        $cityNext = CM_Model_Location_City::create($country1, $state1, 'El Tarter', '42.583', '1.65', '211022');
        CM_Model_Location_City::create($country1, $state1, 'Meritxell', '42.55', '1.6', '230839');
        CM_Model_Location_City::create($country1, $state1, 'Pas De La Casa', '42.55', '1.733', '177897');
        CM_Model_Location_City::create($country1, $state1, 'Soldeu', '42.583', '1.667', '177181');
        CM_Model_Location_City::create($country1, $state2, 'Encamp', '42.533', '1.583', '58282');
        CM_Model_Location_City::create($country1, $state3, 'Arinsal', '42.567', '1.483', '209956');
        CM_Model_Location_City::create($country1, $state3, 'El Serrat', '42.617', '1.55', '209961');

        $country2 = CM_Model_Location_Country::create('Australia', 'AU');
        $state4 = CM_Model_Location_State::create($country2, 'Queensland', null, 'AU04');
        $state5 = CM_Model_Location_State::create($country2, 'Victoria', null, 'AU07');
        $state6 = CM_Model_Location_State::create($country2, 'Tasmania', null, 'AU06');
        CM_Model_Location_City::create($country2, $state4, 'Abermain', '-27.567', '152.783', '33924');
        CM_Model_Location_City::create($country2, $state5, 'Acheron', '-37.25', '145.7', '195676');
        CM_Model_Location_City::create($country2, $state6, 'Baden', '-42.433', '147.467', '242082');

        CM_Model_Location::createAggregation();
        CM_Config::get()->CM_Elasticsearch_Client->enabled = true;

        self::$_type = new CM_Elasticsearch_Type_Location();
        self::$_searchIndexCli = new CM_Elasticsearch_Index_Cli();
        self::$_searchIndexCli->create(self::$_type->getIndex()->getName());
        self::$_countryId1 = $country1->getId();
        self::$_countryId2 = $country2->getId();
        self::$_stateId1 = $state1->getId();
        self::$_stateId4 = $state4->getId();
        self::$_cityId = $city->getId();
        self::$_cityIdNext = $cityNext->getId();
    }

    public static function tearDownAfterClass() {
        self::$_type->getIndex()->delete();
        parent::tearDownAfterClass();
    }

    public function testSearch() {
        $source = new CM_Paging_Location_Suggestions('Merit ad', CM_Model_Location::LEVEL_COUNTRY, CM_Model_Location::LEVEL_CITY);
        $this->assertCount(1, $source);
        $this->assertEquals('Meritxell', $source->getItem(0)->getName());
    }

    public function testSearchWithLevel() {
        $source = new CM_Paging_Location_Suggestions('Encamp', CM_Model_Location::LEVEL_CITY, CM_Model_Location::LEVEL_CITY);
        $this->assertCount(1, $source);
        $this->assertEquals('Encamp', $source->getItem(0)->getName());
    }

    public function testSearchWithScope() {
        $scopeLocation = new CM_Model_Location(CM_Model_Location::LEVEL_COUNTRY, self::$_countryId1);
        $source = new CM_Paging_Location_Suggestions('', CM_Model_Location::LEVEL_COUNTRY, CM_Model_Location::LEVEL_ZIP, null, $scopeLocation);
        $this->assertCount(12, $source);

        $scopeLocation = new CM_Model_Location(CM_Model_Location::LEVEL_STATE, self::$_stateId1);
        $source = new CM_Paging_Location_Suggestions('', CM_Model_Location::LEVEL_COUNTRY, CM_Model_Location::LEVEL_ZIP, null, $scopeLocation);
        $this->assertCount(6, $source);

        $scopeLocation = new CM_Model_Location(CM_Model_Location::LEVEL_CITY, self::$_cityId);
        $source = new CM_Paging_Location_Suggestions('', CM_Model_Location::LEVEL_COUNTRY, CM_Model_Location::LEVEL_ZIP, null, $scopeLocation);
        $this->assertCount(1, $source);

        $scopeLocation = new CM_Model_Location(CM_Model_Location::LEVEL_COUNTRY, self::$_countryId2);
        $source = new CM_Paging_Location_Suggestions('', CM_Model_Location::LEVEL_COUNTRY, CM_Model_Location::LEVEL_ZIP, null, $scopeLocation);
        $this->assertCount(7, $source);

        $scopeLocation = new CM_Model_Location(CM_Model_Location::LEVEL_STATE, self::$_stateId4);
        $source = new CM_Paging_Location_Suggestions('', CM_Model_Location::LEVEL_COUNTRY, CM_Model_Location::LEVEL_ZIP, null, $scopeLocation);
        $this->assertCount(2, $source);
    }

    public function testSearchEmpty() {
        $source = new CM_Paging_Location_Suggestions('', CM_Model_Location::LEVEL_CITY, CM_Model_Location::LEVEL_CITY);
        $this->assertCount(11, $source);
    }

    public function testSearchDistance() {
        $location = new CM_Model_Location(CM_Model_Location::LEVEL_CITY, self::$_cityId);
        $source = new CM_Paging_Location_Suggestions('el', CM_Model_Location::LEVEL_CITY, CM_Model_Location::LEVEL_CITY, $location);
        $locationList = $source->getItems();
        $locationNext = new CM_Model_Location(CM_Model_Location::LEVEL_CITY, self::$_cityIdNext);
        $this->assertEquals($locationNext, reset($locationList));
    }

    public function testSearchWithoutSearchEnabled() {
        CM_Config::get()->CM_Elasticsearch_Client->enabled = false;
        CM_Cache_Local::getInstance()->flush();

        $source = new CM_Paging_Location_Suggestions('', CM_Model_Location::LEVEL_CITY, CM_Model_Location::LEVEL_CITY);
        $this->assertCount(0, $source);

        $source = new CM_Paging_Location_Suggestions('el', CM_Model_Location::LEVEL_CITY, CM_Model_Location::LEVEL_CITY);
        $this->assertCount(0, $source);
    }
}
