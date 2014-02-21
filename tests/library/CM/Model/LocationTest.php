<?php

class CM_Model_LocationTest extends CMTest_TestCase {

	private static $_fields;

	public static function setUpBeforeClass() {
		$switzerland = CM_Db_Db::insert('cm_locationCountry', array('abbreviation' => 'CH', 'name' => 'Switzerland'));
		$germany = CM_Db_Db::insert('cm_locationCountry', array('abbreviation' => 'DE', 'name' => 'Germany'));

		$baselStadt = CM_Db_Db::insert('cm_locationState', array('countryId' => $switzerland, 'name' => 'Basel-Stadt'));
		$zuerich = CM_Db_Db::insert('cm_locationState', array('countryId' => $switzerland, 'name' => 'Zürich'));

		$basel = CM_Db_Db::insert('cm_locationCity', array(
			'stateId' => $baselStadt,
			'countryId' => $switzerland,
			'name' => 'Basel',
			'lat'     => 47.569535,
			'lon' => 7.574063,
		));
		$winterthur = CM_Db_Db::insert('cm_locationCity', array(
			'stateId' => $zuerich,
			'countryId' => $switzerland,
			'name' => 'Winterthur',
			'lat'     => 47.502315,
			'lon' => 8.724947,
		));

		CM_Db_Db::insert('cm_locationZip', array('cityId' => $basel, 'name' => '4057', 'lat' => 47.574155, 'lon' => 7.592993));
		CM_Db_Db::insert('cm_locationZip', array('cityId' => $basel, 'name' => '4056', 'lat' => 47.569535, 'lon' => 7.574063));

		$location = CM_Db_Db::exec('SELECT `1`.`id` `1.id`, `1`.`name` `1.name`,
				`2`.`id` `2.id`, `2`.`name` `2.name`,
				`3`.`id` `3.id`, `3`.`name` `3.name`, `3`.`lat` `3.lat`, `3`.`lon` `3.lon`,
				`4`.`id` `4.id`, `4`.`name` `4.name`, `4`.`lat` `4.lat`, `4`.`lon` `4.lon`
			FROM `cm_locationZip` AS `4`
			JOIN `cm_locationCity` AS `3` ON(`4`.`cityId`=`3`.`id`)
			JOIN `cm_locationState` AS `2` ON(`3`.`stateId`=`2`.`id`)
			JOIN `cm_locationCountry` AS `1` ON(`3`.`countryId`=`1`.`id`)
			LIMIT 1')->fetch();

		self::$_fields[CM_Model_Location::LEVEL_COUNTRY] = array('id' => (int) $location['1.id'], 'name' => $location['1.name']);
		self::$_fields[CM_Model_Location::LEVEL_STATE] = array('id' => (int) $location['2.id'], 'name' => $location['2.name']);
		self::$_fields[CM_Model_Location::LEVEL_CITY] = array('id' => (int) $location['3.id'], 'name' => $location['3.name']);
		self::$_fields[CM_Model_Location::LEVEL_ZIP] = array('id' => (int) $location['4.id'], 'name' => $location['4.name']);
	}

	public function testConstructor() {
		foreach (self::$_fields as $level => $fields) {
			$location = new CM_Model_Location($level, $fields['id']);
			$this->assertInstanceOf('CM_Model_Location', $location);

			try {
				$location = new CM_Model_Location($level, -1);
				$this->fail('Can instantiate invalid cityId');
			} catch (CM_Exception_Invalid $e) {
				$this->assertTrue(true);
			}
		}

		try {
			$location = new CM_Model_Location(-1, 1);
			$this->fail('Can instantiate invalid level');
		} catch (CM_Exception_Invalid $e) {
			$this->assertTrue(true);
		}
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
	}

	public function testGetDistance() {
		$winterthur = (int) CM_Db_Db::select('cm_locationCity', 'id', array('name' => 'Winterthur'))->fetchColumn();
		$basel = (int) CM_Db_Db::select('cm_locationCity', 'id', array('name' => 'Basel'))->fetchColumn();
		$location = new CM_Model_Location(CM_Model_Location::LEVEL_CITY, $basel);
		$locationAgainst = new CM_Model_Location(CM_Model_Location::LEVEL_CITY, $winterthur);

		$this->assertSame(86720, $location->getDistance($locationAgainst));
	}

	public function testFindByCoordinates() {
		CM_Db_Db::insert('cm_locationCity', array(
			'stateId'   => self::$_fields[CM_Model_Location::LEVEL_STATE]['id'],
			'countryId' => self::$_fields[CM_Model_Location::LEVEL_COUNTRY]['id'],
			'name'      => 'test',
			'lat'       => 20, 'lon' => 20));

		$expectedId = CM_Db_Db::insert('cm_locationCity', array(
			'stateId'   => self::$_fields[CM_Model_Location::LEVEL_STATE]['id'],
			'countryId' => self::$_fields[CM_Model_Location::LEVEL_COUNTRY]['id'],
			'name'      => 'test',
			'lat'       => 20.1, 'lon' => 20.2));

		$expected = new CM_Model_Location(CM_Model_Location::LEVEL_CITY, $expectedId);

		CM_Model_Location::createAggregation();
		$this->assertEquals($expected, CM_Model_Location::findByCoordinates(20, 20.3));
		$this->assertNull(CM_Model_Location::findByCoordinates(100, 100));
	}

	public function testFindByIp() {
		$cityId1 = CM_Db_Db::getRandId('cm_locationCity', 'id');
		CM_Db_Db::insert('cm_locationCityIp', array('ipStart' => 1, 'ipEnd' => 5, 'cityId' => $cityId1));
		$cityId2 = CM_Db_Db::getRandId('cm_locationCity', 'id');
		CM_Db_Db::insert('cm_locationCityIp', array('ipStart' => 123456789, 'ipEnd' => 223456789, 'cityId' => $cityId2));
		$countryId1 = CM_Db_Db::getRandId('cm_locationCountry', 'id');
		CM_Db_Db::insert('cm_locationCountryIp', array('ipStart' => 10, 'ipEnd' => 15, 'countryId' => $countryId1));
		$countryId2 = CM_Db_Db::getRandId('cm_locationCountry', 'id');
		CM_Db_Db::insert('cm_locationCountryIp', array('ipStart' => 1234567890, 'ipEnd' => 2234567890, 'countryId' => $countryId2));

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
		$country = CM_Model_Location::createCountry('Example Country', 'EC');
		$this->assertSame(CM_Model_Location::LEVEL_COUNTRY, $country->getLevel());
		$this->assertSame(1, CM_Db_Db::count('cm_locationCountry', array('abbreviation' => 'EC')));
		$this->assertSame('Example Country', $country->getName());
		$this->assertSame('EC', $country->getAbbreviation());
	}

	public function testCreateState() {
		$country = CM_Model_Location::createCountry('Example Country', 'EC');
		$state = CM_Model_Location::createState($country, 'Example State', 'ES');
		$this->assertSame($country->getId(), $state->getId(CM_Model_Location::LEVEL_COUNTRY));
		$this->assertsame('Example State', $state->getName());
		$this->assertSame('ES', $state->getAbbreviation());
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
}
