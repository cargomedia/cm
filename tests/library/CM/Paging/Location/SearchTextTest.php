<?php

class CM_Paging_Location_SearchTextTest extends CMTest_TestCase {

    public function setUp() {
        CM_Config::get()->CM_Elasticsearch_Client->enabled = true;
    }

    public function tearDown() {
        (new CM_Elasticsearch_Type_Location())->getIndex()->delete();
        CMTest_TH::clearEnv();
    }

    public function testSearch() {
        $country = CM_Model_Location::createCountry('Spain', 'ES');
        CM_Model_Location::createCity($country, 'York', 0, 0);
        CM_Model_Location::createCity($country, 'New York', 10, 10);
        CM_Model_Location::createCity($country, 'Basel', 10, 10);
        $this->_recreateLocationIndex();

        $source = new CM_Paging_Location_SearchText('', CM_Model_Location::LEVEL_COUNTRY, CM_Model_Location::LEVEL_CITY);
        $this->assertEquals(4, $source->getCount());

        $source = new CM_Paging_Location_SearchText('', CM_Model_Location::LEVEL_CITY, CM_Model_Location::LEVEL_CITY);
        $this->assertEquals(3, $source->getCount());

        $source = new CM_Paging_Location_SearchText('York', CM_Model_Location::LEVEL_CITY, CM_Model_Location::LEVEL_CITY);
        $this->assertEquals(2, $source->getCount());
    }

    public function testSearchDistance() {
        $country = CM_Model_Location::createCountry('Country', 'CR');
        $source = CM_Model_Location::createCity($country, 'Source', 0, 0);
        $locationMiddle = CM_Model_Location::createCity($country, 'City', 0, 10);
        $locationFar = CM_Model_Location::createCity($country, 'City', 0, 100);
        $locationClose = CM_Model_Location::createCity($country, 'City', 0, 0);
        $this->_recreateLocationIndex();

        $paging = new CM_Paging_Location_SearchText('City', CM_Model_Location::LEVEL_CITY, CM_Model_Location::LEVEL_CITY, $source);
        $expected = array(
            $locationClose,
            $locationMiddle,
            $locationFar,
        );
        $this->assertEquals($expected, $paging->getItems());
    }

    private function _recreateLocationIndex() {
        CM_Model_Location::createAggregation();
        $searchIndexCli = new CM_Elasticsearch_Index_Cli();
        $searchIndexCli->create((new CM_Elasticsearch_Type_Location())->getIndex()->getName());
    }
}
