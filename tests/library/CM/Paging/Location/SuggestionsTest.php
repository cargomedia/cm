<?php

class CM_Paging_Location_SuggestionsTest extends CMTest_TestCase {

    /** @var CM_Elasticsearch_Type_Location */
    protected static $_type;

    /** @var CM_Elasticsearch_Index_Cli */
    protected static $_searchIndexCli;

    /** @var int */
    protected static $_cityId, $_cityIdNext;

    public static function setUpBeforeClass() {
        $country = CM_Model_Location_Country::create('Andorra', 'AD');
        $state1 = CM_Model_Location_State::create($country, 'Canillo', null, 'AD02');
        $state2 = CM_Model_Location_State::create($country, 'Encamp', null, 'AD03');
        $state3 = CM_Model_Location_State::create($country, 'La Massana', null, 'AD04');
        $city = CM_Model_Location_City::create($country, $state1, 'Canillo', '42.567', '1.6', '146765');
        $cityNext = CM_Model_Location_City::create($country, $state1, 'El Tarter', '42.583', '1.65', '211022');
        CM_Model_Location_City::create($country, $state1, 'Meritxell', '42.55', '1.6', '230839');
        CM_Model_Location_City::create($country, $state1, 'Pas De La Casa', '42.55', '1.733', '177897');
        CM_Model_Location_City::create($country, $state1, 'Soldeu', '42.583', '1.667', '177181');
        CM_Model_Location_City::create($country, $state2, 'Encamp', '42.533', '1.583', '58282');
        CM_Model_Location_City::create($country, $state3, 'Arinsal', '42.567', '1.483', '209956');
        CM_Model_Location_City::create($country, $state3, 'El Serrat', '42.617', '1.55', '209961');
        CM_Model_Location::createAggregation();
        CM_Config::get()->CM_Elasticsearch_Client->enabled = true;

        self::$_type = new CM_Elasticsearch_Type_Location();
        self::$_searchIndexCli = new CM_Elasticsearch_Index_Cli();
        self::$_searchIndexCli->create(self::$_type->getIndex()->getName());
        self::$_cityId = $city->getId();
        self::$_cityIdNext = $cityNext->getId();
    }

    public static function tearDownAfterClass() {
        self::$_type->getIndex()->delete();
        parent::tearDownAfterClass();
    }

    public function testSearch() {
        $source = new CM_Paging_Location_Suggestions('Merit ad', CM_Model_Location::LEVEL_COUNTRY, CM_Model_Location::LEVEL_CITY);
        $this->assertEquals(1, $source->getCount());
        $this->assertEquals('Meritxell', $source->getItem(0)->getName());
    }

    public function testSearchWithLevel() {
        $source = new CM_Paging_Location_Suggestions('Encamp', CM_Model_Location::LEVEL_CITY, CM_Model_Location::LEVEL_CITY);
        $this->assertEquals(1, $source->getCount());
        $this->assertEquals('Encamp', $source->getItem(0)->getName());
    }

    public function testSearchEmpty() {
        $source = new CM_Paging_Location_Suggestions('', CM_Model_Location::LEVEL_CITY, CM_Model_Location::LEVEL_CITY);
        $this->assertEquals(8, $source->getCount());
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
        $this->assertEquals(0, $source->getCount());

        $source = new CM_Paging_Location_Suggestions('el', CM_Model_Location::LEVEL_CITY, CM_Model_Location::LEVEL_CITY);
        $this->assertEquals(0, $source->getCount());
    }
}
