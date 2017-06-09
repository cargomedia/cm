<?php

class CM_Model_LocationTest extends CMTest_TestCase {

    private static $_fields;

    private static $_switzerlandId;

    private static $_baselStadtId;

    private static $_baselId;

    private static $_baselZipId;

    public static function setUpBeforeClass() {
        $switzerland = CM_Db_Db::insert('cm_model_location_country', array('abbreviation' => 'CH', 'name' => 'Switzerland'));
        $germany = CM_Db_Db::insert('cm_model_location_country', array('abbreviation' => 'DE', 'name' => 'Germany'));

        $baselStadt = CM_Db_Db::insert('cm_model_location_state', array('countryId' => $switzerland, 'name' => 'Basel-Stadt'));
        $zuerich = CM_Db_Db::insert('cm_model_location_state', array('countryId' => $switzerland, 'name' => 'Zürich'));

        $basel = CM_Db_Db::insert('cm_model_location_city', array(
            'stateId'   => $baselStadt,
            'countryId' => $switzerland,
            'name'      => 'Basel',
            'lat'       => 47.569535,
            'lon'       => 7.574063,
        ));
        $winterthur = CM_Db_Db::insert('cm_model_location_city', array(
            'stateId'   => $zuerich,
            'countryId' => $switzerland,
            'name'      => 'Winterthur',
            'lat'       => 47.502315,
            'lon'       => 8.724947,
        ));

        $baselZip = CM_Db_Db::insert('cm_model_location_zip', array('cityId' => $basel, 'name' => '4056', 'lat' => 47.569535, 'lon' => 7.574063));
        CM_Db_Db::insert('cm_model_location_zip', array('cityId' => $basel, 'name' => '4057', 'lat' => 47.574155, 'lon' => 7.592993));

        $location = CM_Db_Db::exec('SELECT `1`.`id` `1.id`, `1`.`name` `1.name`,
				`2`.`id` `2.id`, `2`.`name` `2.name`,
				`3`.`id` `3.id`, `3`.`name` `3.name`, `3`.`lat` `3.lat`, `3`.`lon` `3.lon`,
				`4`.`id` `4.id`, `4`.`name` `4.name`, `4`.`lat` `4.lat`, `4`.`lon` `4.lon`
			FROM `cm_model_location_zip` AS `4`
			JOIN `cm_model_location_city` AS `3` ON(`4`.`cityId`=`3`.`id`)
			JOIN `cm_model_location_state` AS `2` ON(`3`.`stateId`=`2`.`id`)
			JOIN `cm_model_location_country` AS `1` ON(`3`.`countryId`=`1`.`id`)
			LIMIT 1')->fetch();

        self::$_fields[CM_Model_Location::LEVEL_COUNTRY] = array('id' => (int) $location['1.id'], 'name' => $location['1.name']);
        self::$_fields[CM_Model_Location::LEVEL_STATE] = array('id' => (int) $location['2.id'], 'name' => $location['2.name']);
        self::$_fields[CM_Model_Location::LEVEL_CITY] = array('id' => (int) $location['3.id'], 'name' => $location['3.name']);
        self::$_fields[CM_Model_Location::LEVEL_ZIP] = array('id' => (int) $location['4.id'], 'name' => $location['4.name']);

        self::$_switzerlandId = $switzerland;
        self::$_baselStadtId = $baselStadt;
        self::$_baselId = $basel;
        self::$_baselZipId = $baselZip;
    }

    public function tearDown() {
        CMTest_TH::clearCache();
    }

    public function testConstructor() {
        foreach (self::$_fields as $level => $fields) {
            $location = new CM_Model_Location($level, $fields['id']);
            $this->assertInstanceOf('CM_Model_Location', $location);

            try {
                $location = new CM_Model_Location($level, -1);
                $this->fail('Can instantiate invalid cityId');
            } catch (CM_Exception_Nonexistent $e) {
                $this->assertTrue(true);
            }
        }

        /** @var CM_Location_InvalidLevelException $exception */
        $exception = $this->catchException(function () {
            $location = new CM_Model_Location(-1, 1);
        });
        $this->assertInstanceOf(CM_Location_InvalidLevelException::class, $exception);
        $this->assertSame('Invalid location level', $exception->getMessage());
        $this->assertSame(['level' => -1], $exception->getMetaInfo());
    }

    public function testGetLevel() {
        foreach (self::$_fields as $level => $fields) {
            $location = new CM_Model_Location($level, $fields['id']);
            $this->assertSame($level, $location->getLevel());
        }
    }

    public function testGetId() {
        foreach (self::$_fields as $level => $fields) {
            $location = new CM_Model_Location($level, $fields['id']);
            foreach (array(CM_Model_Location::LEVEL_COUNTRY, CM_Model_Location::LEVEL_STATE, CM_Model_Location::LEVEL_CITY,
                         CM_Model_Location::LEVEL_ZIP) as $level2) {
                if ($level >= $level2) {
                    $this->assertSame(self::$_fields[$level2]['id'], $location->getId($level2));
                } else {
                    $this->assertNull($location->getId($level2));
                }
            }
        }
    }

    public function testGetName() {
        foreach (self::$_fields as $level => $fields) {
            $location = new CM_Model_Location($level, $fields['id']);
            foreach (array(CM_Model_Location::LEVEL_COUNTRY, CM_Model_Location::LEVEL_STATE, CM_Model_Location::LEVEL_CITY,
                         CM_Model_Location::LEVEL_ZIP) as $level2) {
                if ($level >= $level2) {
                    $this->assertSame(self::$_fields[$level2]['name'], $location->getName($level2));
                } else {
                    $this->assertNull($location->getId($level2));
                }
            }
        }
    }

    public function testGet() {
        foreach (self::$_fields as $level => $fields) {
            $location = new CM_Model_Location($level, $fields['id']);
            foreach (array(CM_Model_Location::LEVEL_COUNTRY, CM_Model_Location::LEVEL_STATE, CM_Model_Location::LEVEL_CITY,
                         CM_Model_Location::LEVEL_ZIP) as $level2) {
                $location2 = $location->get($level2);
                if ($level2 > $level) {
                    $this->assertNull($location2);
                } else {
                    $this->assertInstanceOf('CM_Model_Location', $location2);
                    $this->assertSame($location->getId($level2), $location2->getId());
                    $this->assertSame($level2, $location2->getLevel());
                }
            }
        }
    }

    public function testGetCoordinates() {
        foreach (self::$_fields as $level => $fields) {
            $location = new CM_Model_Location($level, $fields['id']);
            $coordinates = $location->getCoordinates();
            if ($level >= CM_Model_Location::LEVEL_CITY) {
                $this->assertInternalType('array', $coordinates);
                $this->assertCount(2, $coordinates);
                $this->assertInternalType('float', $coordinates['lat']);
                $this->assertInternalType('float', $coordinates['lon']);
            } else {
                $this->assertNull($location->getCoordinates());
            }
        }
        $countryId = CM_Db_Db::insert('cm_model_location_country', ['abbreviation' => 'CO', 'name' => 'Country', 'lat' => 1.1, 'lon' => 2.2]);
        $stateId = CM_Db_Db::insert('cm_model_location_state', ['countryId' => $countryId, 'name' => 'State', 'lat' => 3.3, 'lon' => 4.4]);
        $cityId = CM_Db_Db::insert('cm_model_location_city', [
            'stateId'   => $stateId,
            'countryId' => $countryId,
            'name'      => 'City',
            'lat'       => 5.5,
            'lon'       => 6.6,
        ]);
        $zipId = CM_Db_Db::insert('cm_model_location_zip', ['cityId' => $cityId, 'name' => 'Zip', 'lat' => 7.7, 'lon' => 8.8]);
        $locationZip = new CM_Model_Location(CM_Model_Location::LEVEL_ZIP, $zipId);
        $this->assertSame(['lat' => 7.7, 'lon' => 8.8], $locationZip->getCoordinates());
        $this->assertSame(['lat' => 7.7, 'lon' => 8.8], $locationZip->getCoordinates(CM_Model_Location::LEVEL_ZIP));
        $this->assertSame(['lat' => 7.7, 'lon' => 8.8], $locationZip->getCoordinates(CM_Model_Location::LEVEL_CITY));
        $this->assertSame(['lat' => 7.7, 'lon' => 8.8], $locationZip->getCoordinates(CM_Model_Location::LEVEL_STATE));
        $this->assertSame(['lat' => 7.7, 'lon' => 8.8], $locationZip->getCoordinates(CM_Model_Location::LEVEL_COUNTRY));
        $locationCity = new CM_Model_Location(CM_Model_Location::LEVEL_CITY, $cityId);
        $this->assertSame(['lat' => 5.5, 'lon' => 6.6], $locationCity->getCoordinates());
        $this->assertSame(null, $locationCity->getCoordinates(CM_Model_Location::LEVEL_ZIP));
        $this->assertSame(['lat' => 5.5, 'lon' => 6.6], $locationCity->getCoordinates(CM_Model_Location::LEVEL_CITY));
        $this->assertSame(['lat' => 5.5, 'lon' => 6.6], $locationCity->getCoordinates(CM_Model_Location::LEVEL_STATE));
        $this->assertSame(['lat' => 5.5, 'lon' => 6.6], $locationCity->getCoordinates(CM_Model_Location::LEVEL_COUNTRY));
        $locationState = new CM_Model_Location(CM_Model_Location::LEVEL_STATE, $stateId);
        $this->assertSame(['lat' => 3.3, 'lon' => 4.4], $locationState->getCoordinates());
        $this->assertSame(null, $locationState->getCoordinates(CM_Model_Location::LEVEL_ZIP));
        $this->assertSame(null, $locationState->getCoordinates(CM_Model_Location::LEVEL_CITY));
        $this->assertSame(['lat' => 3.3, 'lon' => 4.4], $locationState->getCoordinates(CM_Model_Location::LEVEL_STATE));
        $this->assertSame(['lat' => 3.3, 'lon' => 4.4], $locationState->getCoordinates(CM_Model_Location::LEVEL_COUNTRY));
        $locationCountry = new CM_Model_Location(CM_Model_Location::LEVEL_COUNTRY, $countryId);
        $this->assertSame(['lat' => 1.1, 'lon' => 2.2], $locationCountry->getCoordinates());
        $this->assertSame(null, $locationCountry->getCoordinates(CM_Model_Location::LEVEL_ZIP));
        $this->assertSame(null, $locationCountry->getCoordinates(CM_Model_Location::LEVEL_CITY));
        $this->assertSame(null, $locationCountry->getCoordinates(CM_Model_Location::LEVEL_STATE));
        $this->assertSame(['lat' => 1.1, 'lon' => 2.2], $locationCountry->getCoordinates(CM_Model_Location::LEVEL_COUNTRY));
    }

    public function testGetDistance() {
        $winterthur = (int) CM_Db_Db::select('cm_model_location_city', 'id', array('name' => 'Winterthur'))->fetchColumn();
        $basel = (int) CM_Db_Db::select('cm_model_location_city', 'id', array('name' => 'Basel'))->fetchColumn();
        $location = new CM_Model_Location(CM_Model_Location::LEVEL_CITY, $basel);
        $locationAgainst = new CM_Model_Location(CM_Model_Location::LEVEL_CITY, $winterthur);

        $this->assertSame(86720, $location->getDistance($locationAgainst));
    }

    public function testFindByCoordinates() {
        CM_Db_Db::insert('cm_model_location_city', array(
            'stateId'   => self::$_fields[CM_Model_Location::LEVEL_STATE]['id'],
            'countryId' => self::$_fields[CM_Model_Location::LEVEL_COUNTRY]['id'],
            'name'      => 'test',
            'lat'       => 20,
            'lon'       => 20));

        $idExpected1 = CM_Db_Db::insert('cm_model_location_city', array(
            'stateId'   => self::$_fields[CM_Model_Location::LEVEL_STATE]['id'],
            'countryId' => self::$_fields[CM_Model_Location::LEVEL_COUNTRY]['id'],
            'name'      => 'test',
            'lat'       => 20.1,
            'lon'       => 20.2));

        $idExpected2 = CM_Db_Db::insert('cm_model_location_city', array(
            'stateId'   => self::$_fields[CM_Model_Location::LEVEL_STATE]['id'],
            'countryId' => self::$_fields[CM_Model_Location::LEVEL_COUNTRY]['id'],
            'name'      => 'Waite Park',
            'lat'       => 45.53,
            'lon'       => -94.233,
        ));

        $locationExpected1 = new CM_Model_Location(CM_Model_Location::LEVEL_CITY, $idExpected1);
        $locationExpected2 = new CM_Model_Location(CM_Model_Location::LEVEL_CITY, $idExpected2);

        CM_Model_Location::createAggregation();
        $this->assertNull(CM_Model_Location::findByCoordinates(100, 100));
        $this->assertEquals($locationExpected1, CM_Model_Location::findByCoordinates(20, 20.3));
        $this->assertEquals($locationExpected2, CM_Model_Location::findByCoordinates(45.552222998855, -94.214516300796));
    }

    public function testFindByIp() {
        $cityId1 = CM_Db_Db::getRandId('cm_model_location_city', 'id');
        CM_Db_Db::insert('cm_model_location_ip', array(
            'id'      => $cityId1,
            'level'   => CM_Model_Location::LEVEL_CITY,
            'ipStart' => 1,
            'ipEnd'   => 5,
        ));
        $cityId2 = CM_Db_Db::getRandId('cm_model_location_city', 'id');
        CM_Db_Db::insert('cm_model_location_ip', array(
            'id'      => $cityId2,
            'level'   => CM_Model_Location::LEVEL_CITY,
            'ipStart' => 123456789,
            'ipEnd'   => 223456789,
        ));
        $countryId1 = CM_Db_Db::getRandId('cm_model_location_country', 'id');
        CM_Db_Db::insert('cm_model_location_ip', array(
            'id'      => $countryId1,
            'level'   => CM_Model_Location::LEVEL_COUNTRY,
            'ipStart' => 10,
            'ipEnd'   => 15,
        ));
        $countryId2 = CM_Db_Db::getRandId('cm_model_location_country', 'id');
        CM_Db_Db::insert('cm_model_location_ip', array(
            'id'      => $countryId2,
            'level'   => CM_Model_Location::LEVEL_COUNTRY,
            'ipStart' => 1234567890,
            'ipEnd'   => 2234567890,
        ));

        $this->assertEquals(new CM_Model_Location(CM_Model_Location::LEVEL_CITY, $cityId1), CM_Model_Location::findByIp(3));
        $this->assertNull(CM_Model_Location::findByIp(6));
        $this->assertEquals(new CM_Model_Location(CM_Model_Location::LEVEL_CITY, $cityId2), CM_Model_Location::findByIp(223456700));
        $this->assertNull(CM_Model_Location::findByIp(223456800));

        $this->assertEquals(new CM_Model_Location(CM_Model_Location::LEVEL_COUNTRY, $countryId1), CM_Model_Location::findByIp(12));
        $this->assertNull(CM_Model_Location::findByIp(16));
        $this->assertEquals(new CM_Model_Location(CM_Model_Location::LEVEL_COUNTRY, $countryId2), CM_Model_Location::findByIp(2234567870));
        $this->assertNull(CM_Model_Location::findByIp(2234567900));
    }

    public function testCreateCountry() {
        $country = CM_Model_Location::createCountry('Example Country', 'EC', 1.1, 2.2);
        $this->assertSame(CM_Model_Location::LEVEL_COUNTRY, $country->getLevel());
        $this->assertSame(1, CM_Db_Db::count('cm_model_location_country', array('abbreviation' => 'EC')));
        $this->assertSame('Example Country', $country->getName());
        $this->assertSame('EC', $country->getAbbreviation());
        $this->assertSame(['lat' => 1.1, 'lon' => 2.2], $country->getCoordinates());
    }

    public function testCreateState() {
        $country = CM_Model_Location::createCountry('Example Country', 'EC');
        $state = CM_Model_Location::createState($country, 'Example State', 'ES', 1.1, 2.2);
        $this->assertSame($country->getId(), $state->getId(CM_Model_Location::LEVEL_COUNTRY));
        $this->assertsame('Example State', $state->getName());
        $this->assertSame('ES', $state->getAbbreviation());
        $this->assertSame(['lat' => 1.1, 'lon' => 2.2], $state->getCoordinates());
    }

    public function testCreateCity() {
        $country = CM_Model_Location::createCountry('Example Country', 'EC');
        $cityWithoutState = CM_Model_Location::createCity($country, 'Example City', 50, 100);
        $this->assertSame($country->getId(), $cityWithoutState->getId(CM_Model_Location::LEVEL_COUNTRY));
        $this->assertSame(null, $cityWithoutState->getId(CM_Model_Location::LEVEL_STATE));
        $this->assertSame('Example City', $cityWithoutState->getName());
        $this->assertSame(array('lat' => (float) 50, 'lon' => (float) 100), $cityWithoutState->getCoordinates());

        $state = CM_Model_Location::createState($country, 'Example State', 'ES');
        $cityWithState = CM_Model_Location::createCity($state, 'Example City', 50, 100);
        $this->assertSame($country->getId(), $cityWithState->getId(CM_Model_Location::LEVEL_COUNTRY));
        $this->assertSame($state->getId(), $cityWithState->getId(CM_Model_Location::LEVEL_STATE));
    }

    public function testCreateZip() {
        $country = CM_Model_Location::createCountry('Example Country', 'EC');
        $state = CM_Model_Location::createState($country, 'Example State', 'ES');
        $city = CM_Model_Location::createCity($state, 'Example City', 50, 100);
        $zip = CM_Model_Location::createZip($city, '12333', 50, 100);
        $this->assertSame($country->getId(), $zip->getId(CM_Model_Location::LEVEL_COUNTRY));
        $this->assertSame($state->getId(), $zip->getId(CM_Model_Location::LEVEL_STATE));
        $this->assertSame($city->getId(), $zip->getId(CM_Model_Location::LEVEL_CITY));
        $this->assertSame('12333', $zip->getName());
        $this->assertSame(array('lat' => (float) 50, 'lon' => (float) 100), $zip->getCoordinates());
    }

    public function testFindByAttributes() {
        $country = CM_Model_Location::findByAttributes(CM_Model_Location::LEVEL_COUNTRY, ['abbreviation' => 'UK']);
        $this->assertNull($country);

        $country = CM_Model_Location::findByAttributes(CM_Model_Location::LEVEL_COUNTRY, ['abbreviation' => 'CH']);
        $this->assertTrue($country->equals(new CM_Model_Location(CM_Model_Location::LEVEL_COUNTRY, self::$_switzerlandId)));

        $country = CM_Model_Location::findByAttributes(CM_Model_Location::LEVEL_COUNTRY, ['abbreviation' => 'CH', 'name' => 'Switzerland']);
        $this->assertTrue($country->equals(new CM_Model_Location(CM_Model_Location::LEVEL_COUNTRY, self::$_switzerlandId)));

        $country = CM_Model_Location::findByAttributes(CM_Model_Location::LEVEL_COUNTRY, ['name' => 'Switzerland']);
        $this->assertTrue($country->equals(new CM_Model_Location(CM_Model_Location::LEVEL_COUNTRY, self::$_switzerlandId)));

        $state = CM_Model_Location::findByAttributes(CM_Model_Location::LEVEL_STATE, ['name' => 'Basel-Stadt']);
        $this->assertTrue($state->equals(new CM_Model_Location(CM_Model_Location::LEVEL_STATE, self::$_baselStadtId)));

        $state = CM_Model_Location::findByAttributes(CM_Model_Location::LEVEL_STATE, ['countryId' => self::$_switzerlandId]);
        $this->assertTrue($state->equals(new CM_Model_Location(CM_Model_Location::LEVEL_STATE, self::$_baselStadtId)));

        $state = CM_Model_Location::findByAttributes(CM_Model_Location::LEVEL_STATE, ['countryId' => self::$_switzerlandId, 'name' => 'Basel-Stadt']);
        $this->assertTrue($state->equals(new CM_Model_Location(CM_Model_Location::LEVEL_STATE, self::$_baselStadtId)));

        $city = CM_Model_Location::findByAttributes(CM_Model_Location::LEVEL_CITY, ['countryId' => self::$_switzerlandId]);
        $this->assertTrue($city->equals(new CM_Model_Location(CM_Model_Location::LEVEL_CITY, self::$_baselId)));

        $city = CM_Model_Location::findByAttributes(CM_Model_Location::LEVEL_CITY, ['stateId' => self::$_baselStadtId]);
        $this->assertTrue($city->equals(new CM_Model_Location(CM_Model_Location::LEVEL_CITY, self::$_baselId)));

        $city = CM_Model_Location::findByAttributes(CM_Model_Location::LEVEL_CITY, ['name' => 'Basel']);
        $this->assertTrue($city->equals(new CM_Model_Location(CM_Model_Location::LEVEL_CITY, self::$_baselId)));

        $zip = CM_Model_Location::findByAttributes(CM_Model_Location::LEVEL_ZIP, ['cityId' => self::$_baselId]);
        $this->assertTrue($zip->equals(new CM_Model_Location(CM_Model_Location::LEVEL_ZIP, self::$_baselZipId)));

        $zip = CM_Model_Location::findByAttributes(CM_Model_Location::LEVEL_ZIP, ['name' => '4056']);
        $this->assertTrue($zip->equals(new CM_Model_Location(CM_Model_Location::LEVEL_ZIP, self::$_baselZipId)));
    }

    public function testFindByAttributesCache() {
        $cacheKey = CM_CacheConst::Location_ByAttribute . '_level:' . CM_Model_Location::LEVEL_COUNTRY .
            '_name:abbreviation_value:CH_name:name_value:Switzerland';
        $cache = CM_Cache_Local::getInstance();

        $this->assertFalse($cache->get($cacheKey));

        $country = CM_Model_Location::findByAttributes(CM_Model_Location::LEVEL_COUNTRY, ['abbreviation' => 'CH', 'name' => 'Switzerland']);

        $this->assertSame($country->getId(), (int) $cache->get($cacheKey));
    }

    /**
     * @expectedException CM_Db_Exception
     */
    public function testFindByAttributesException() {
        CM_Model_Location::findByAttributes(CM_Model_Location::LEVEL_COUNTRY, ['notExistingField' => 'CH']);
    }

    public function testGetTimezone() {
        /** @var CM_Model_Location|\Mocka\AbstractClassTrait $locationMock */
        $locationMock = $this->mockClass('CM_Model_Location')->newInstanceWithoutConstructor();

        $locationMock->mockMethod('getCoordinates')->set(['lat' => 51.58742, 'lon' => -0.28425]);
        $timeZone = $locationMock->getTimeZone();
        $this->assertInstanceOf('DateTimeZone', $timeZone);
        $this->assertSame('Europe/London', $timeZone->getName());

        $locationMock->mockMethod('getCoordinates')->set(['lat' => 49.82072, 'lon' => 1.44115]);
        $timeZone = $locationMock->getTimeZone();
        $this->assertSame('Europe/Paris', $timeZone->getName());

        $locationMock->mockMethod('getCoordinates')->set(['lat' => 40.58026, 'lon' => -74.84595]);
        $timeZone = $locationMock->getTimeZone();
        $this->assertSame('America/New_York', $timeZone->getName());

        $locationMock->mockMethod('getCoordinates')->set(['lat' => 35.80933, 'lon' => -118.55927]);
        $timeZone = $locationMock->getTimeZone();
        $this->assertSame('America/Los_Angeles', $timeZone->getName());
    }

    public function testGetGeoPoint() {
        /** @var CM_Model_Location|\Mocka\AbstractClassTrait $locationMock */
        $locationMock = $this->mockClass('CM_Model_Location')->newInstanceWithoutConstructor();
        $locationMock->mockMethod('getCoordinates')->set(['lat' => 51.58742, 'lon' => -0.28425]);

        $point = $locationMock->getGeoPoint();
        $this->assertInstanceOf('CM_Geo_Point', $point);
        $this->assertEquals(51.58742, $point->getLatitude());
        $this->assertEquals(-0.28425, $point->getLongitude());

        $locationMock->mockMethod('getCoordinates')->set(null);
        $this->assertNull($locationMock->getGeoPoint());
    }

    public function testArrayConvertible() {
        $location = CMTest_TH::createLocation();

        $this->assertEquals($location, CM_Model_Location::fromArray($location->toArray()));
    }
}
