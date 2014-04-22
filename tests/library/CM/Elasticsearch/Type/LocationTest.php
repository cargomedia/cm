<?php

class CM_Elasticsearch_Type_LocationTest extends CMTest_TestCase {

    /** @var CM_Elasticsearch_Type_Location */
    protected static $_type;

    /** @var CM_Elasticsearch_Index_Cli */
    protected static $_searchIndexCli;

    public static function setUpBeforeClass() {
        $cities = array(
            array('id'       => '1', 'stateId' => '670', 'countryId' => '9', 'name' => 'Canillo', 'lat' => '42.567', 'lon' => '1.6',
                  '_maxmind' => '146765'),
            array('id'       => '2', 'stateId' => '670', 'countryId' => '9', 'name' => 'El Tarter', 'lat' => '42.583', 'lon' => '1.65',
                  '_maxmind' => '211022'),
            array('id'       => '3', 'stateId' => '670', 'countryId' => '9', 'name' => 'Meritxell', 'lat' => '42.55', 'lon' => '1.6',
                  '_maxmind' => '230839'),
            array('id'       => '4', 'stateId' => '670', 'countryId' => '9', 'name' => 'Pas De La Casa', 'lat' => '42.55', 'lon' => '1.733',
                  '_maxmind' => '177897'),
            array('id'       => '5', 'stateId' => '670', 'countryId' => '9', 'name' => 'Soldeu', 'lat' => '42.583', 'lon' => '1.667',
                  '_maxmind' => '177181'),
            array('id'       => '6', 'stateId' => '1110', 'countryId' => '9', 'name' => 'Encamp', 'lat' => '42.533', 'lon' => '1.583',
                  '_maxmind' => '58282'),
            array('id'       => '7', 'stateId' => '1941', 'countryId' => '9', 'name' => 'Arinsal', 'lat' => '42.567', 'lon' => '1.483',
                  '_maxmind' => '209956'),
            array('id'       => '8', 'stateId' => '1941', 'countryId' => '9', 'name' => 'El Serrat', 'lat' => '42.617', 'lon' => '1.55',
                  '_maxmind' => '209961')
        );

        CM_Db_Db::insert('cm_locationCity', array('id', 'stateId', 'countryId', 'name', 'lat', 'lon', '_maxmind'), $cities);
        CM_Model_Location::createAggregation();
        CM_Config::get()->CM_Search->enabled = true;

        self::$_type = new CM_Elasticsearch_Type_Location();
        self::$_searchIndexCli = new CM_Elasticsearch_Index_Cli();
        self::$_searchIndexCli->create(self::$_type->getIndex()->getName());
    }

    public static function tearDownAfterClass() {
        self::$_type->getIndex()->delete();
        parent::tearDownAfterClass();
    }

    public function testSearch() {
        $searchQuery = new CM_Elasticsearch_Query_Location();
        $source = new CM_PagingSource_Search_Location($searchQuery);
        $this->assertSame(8, $source->getCount());
    }

    public function testSearchDistance() {
        $searchQuery = new CM_Elasticsearch_Query_Location();
        $location = new CM_Model_Location(CM_Model_Location::LEVEL_CITY, 1);
        $searchQuery->sortDistance($location);
        $source = new CM_PagingSource_Search_Location($searchQuery);
        $locationList = $source->getItems();
        $this->assertEquals(array('id' => 1, 'level' => CM_Model_Location::LEVEL_CITY), reset($locationList));
    }
}
