<?php

class CM_Elasticsearch_Type_LocationTest extends CMTest_TestCase {

    /** @var CM_Elasticsearch_Type_Location */
    protected static $_type;

    /** @var CM_Elasticsearch_Index_Cli */
    protected static $_searchIndexCli;

    /** @var int */
    protected static $_cityId;

    public static function setUpBeforeClass() {
        $country = CM_Model_Location_Country::create('Andorra', 'AD');
        $state1 = CM_Model_Location_State::create($country, 'Canillo', null, 'AD02');
        $state2 = CM_Model_Location_State::create($country, 'Encamp', null, 'AD03');
        $state3 = CM_Model_Location_State::create($country, 'La Massana', null, 'AD04');
        $city = CM_Model_Location_City::create($country, $state1, 'Canillo', '42.567', '1.6', '146765');
        CM_Model_Location_City::create($country, $state1, 'El Tarter', '42.583', '1.65', '211022');
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
    }

    public static function tearDownAfterClass() {
        self::$_type->getIndex()->delete();
        parent::tearDownAfterClass();
    }

    public function testSearch() {
        $searchQuery = new CM_Elasticsearch_Query_Location();
        $source = new CM_PagingSource_Elasticsearch_Location($searchQuery);
        $this->assertSame(12, $source->getCount());
    }

    public function testSearchDistance() {
        $searchQuery = new CM_Elasticsearch_Query_Location();
        $location = new CM_Model_Location(CM_Model_Location::LEVEL_CITY, self::$_cityId);
        $searchQuery->sortDistance($location);
        $source = new CM_PagingSource_Elasticsearch_Location($searchQuery);
        $locationList = $source->getItems();
        $this->assertEquals(array('id' => self::$_cityId, 'level' => CM_Model_Location::LEVEL_CITY), reset($locationList));
    }
}
