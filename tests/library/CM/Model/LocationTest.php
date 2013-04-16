<?php

class CM_Model_LocationTest extends CMTest_TestCase {

	private static $_fields;

	public static function setUpBeforeClass() {
		$switzerland = CM_Db_Db::insert(TBL_CM_LOCATIONCOUNTRY, array('abbreviation' => 'CH', 'name' => 'Switzerland'));
		$germany = CM_Db_Db::insert(TBL_CM_LOCATIONCOUNTRY, array('abbreviation' => 'DE', 'name' => 'Germany'));

		$baselStadt = CM_Db_Db::insert(TBL_CM_LOCATIONSTATE, array('countryId' => $switzerland, 'name' => 'Basel-Stadt'));
		$zuerich = CM_Db_Db::insert(TBL_CM_LOCATIONSTATE, array('countryId' => $switzerland, 'name' => 'Zürich'));

		$basel = CM_Db_Db::insert(TBL_CM_LOCATIONCITY, array('stateId' => $baselStadt, 'countryId' => $switzerland, 'name' => 'Basel',
															 'lat'     => 47.569535, 'lon' => 7.574063));
		$winterthur = CM_Db_Db::insert(TBL_CM_LOCATIONCITY, array('stateId' => $zuerich, 'countryId' => $switzerland, 'name' => 'Winterthur',
																  'lat'     => 47.502315, 'lon' => 8.724947));

		CM_Db_Db::insert(TBL_CM_LOCATIONZIP, array('cityId' => $basel, 'name' => '4057', 'lat' => 47.574155, 'lon' => 7.592993));
		CM_Db_Db::insert(TBL_CM_LOCATIONZIP, array('cityId' => $basel, 'name' => '4056', 'lat' => 47.569535, 'lon' => 7.574063));

		$location = CM_Db_Db::exec('SELECT `1`.`id` `1.id`, `1`.`name` `1.name`,
				`2`.`id` `2.id`, `2`.`name` `2.name`,
				`3`.`id` `3.id`, `3`.`name` `3.name`, `3`.`lat` `3.lat`, `3`.`lon` `3.lon`,
				`4`.`id` `4.id`, `4`.`name` `4.name`, `4`.`lat` `4.lat`, `4`.`lon` `4.lon`
			FROM TBL_CM_LOCATIONZIP AS `4`
			JOIN TBL_CM_LOCATIONCITY AS `3` ON(`4`.`cityId`=`3`.`id`)
			JOIN TBL_CM_LOCATIONSTATE AS `2` ON(`3`.`stateId`=`2`.`id`)
			JOIN TBL_CM_LOCATIONCOUNTRY AS `1` ON(`3`.`countryId`=`1`.`id`)
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

	public function testFindByIp() {
		$cityId1 = CM_Db_Db::getRandId(TBL_CM_LOCATIONCITY, 'id');
		CM_Db_Db::insert(TBL_CM_LOCATIONCITYIP, array('ipStart' => 1, 'ipEnd' => 5, 'cityId' => $cityId1));
		$cityId2 = CM_Db_Db::getRandId(TBL_CM_LOCATIONCITY, 'id');
		CM_Db_Db::insert(TBL_CM_LOCATIONCITYIP, array('ipStart' => 123456789, 'ipEnd' => 223456789, 'cityId' => $cityId2));
		$countryId1 = CM_Db_Db::getRandId(TBL_CM_LOCATIONCOUNTRY, 'id');
		CM_Db_Db::insert(TBL_CM_LOCATIONCOUNTRYIP, array('ipStart' => 10, 'ipEnd' => 15, 'countryId' => $countryId1));
		$countryId2 = CM_Db_Db::getRandId(TBL_CM_LOCATIONCOUNTRY, 'id');
		CM_Db_Db::insert(TBL_CM_LOCATIONCOUNTRYIP, array('ipStart' => 1234567890, 'ipEnd' => 2234567890, 'countryId' => $countryId2));

		$this->assertEquals(new CM_Model_Location(CM_Model_Location::LEVEL_CITY, $cityId1), CM_Model_Location::findByIp(3));
		$this->assertNull(CM_Model_Location::findByIp(6));
		$this->assertEquals(new CM_Model_Location(CM_Model_Location::LEVEL_CITY, $cityId2), CM_Model_Location::findByIp(223456700));
		$this->assertNull(CM_Model_Location::findByIp(223456800));

		$this->assertEquals(new CM_Model_Location(CM_Model_Location::LEVEL_COUNTRY, $countryId1), CM_Model_Location::findByIp(12));
		$this->assertNull(CM_Model_Location::findByIp(16));
		$this->assertEquals(new CM_Model_Location(CM_Model_Location::LEVEL_COUNTRY, $countryId2), CM_Model_Location::findByIp(2234567870));
		$this->assertNull(CM_Model_Location::findByIp(2234567900));
	}
}
