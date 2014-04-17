<?php

class CM_Model_Location extends CM_Model_Abstract {

    const LEVEL_COUNTRY = 1;
    const LEVEL_STATE = 2;
    const LEVEL_CITY = 3;
    const LEVEL_ZIP = 4;

    /** @var CM_Model_Location_Country|CM_Model_Location_State|CM_Model_Location_City|CM_Model_Location_Zip */
    protected $_location;

    /**
     * @param int $level A LEVEL_*-const
     * @param int $id
     * @throws CM_Exception_Invalid
     */
    public function __construct($level, $id) {
        switch ($level) {
            case self::LEVEL_COUNTRY:
                $this->_location = new CM_Model_Location_Country($id);
                break;
            case self::LEVEL_STATE:
                $this->_location = new CM_Model_Location_State($id);
                break;
            case self::LEVEL_CITY:
                $this->_location = new CM_Model_Location_City($id);
                break;
            case self::LEVEL_ZIP:
                $this->_location = new CM_Model_Location_Zip($id);
                break;
            default:
                throw new CM_Exception_Invalid('Invalid location level `' . $level . '`');
        }
    }

    /**
     * @param int    $level
     * @param string $key
     * @return mixed|null
     */
    private function _getField($level, $key) {
        $level = (int) $level;
        $fields = $this->_get('fields');
        if (!array_key_exists($level, $fields)) {
            return null;
        }
        if (!array_key_exists($key, $fields[$level])) {
            return null;
        }
        return $fields[$level][$key];
    }

    /**
     * @param int $level
     * @return CM_Model_Location|null
     */
    public function get($level) {
        $id = $this->getId($level);
        if (null === $id) {
            return null;
        }
        return new self($level, $id);
    }

    /**
     * @return int
     */
    public function getLevel() {
        return $this->_location->getLevel();
    }

    /**
     * @param int|null $level
     * @return int|null
     */
    public function getId($level = null) {
        if ($location = $this->_getLocation($level)) {
            return $location->getId();
        }
        return null;
    }

    /**
     * @param int|null $level
     * @return string|null
     */
    public function getName($level = null) {
        if ($location = $this->_getLocation($level)) {
            return $location->getName();
        }
        return null;
    }

    /**
     * @param int|null $level
     * @return string|null
     */
    public function getAbbreviation($level = null) {
        if ($location = $this->_getLocation($level)) {
            if (method_exists($location, 'getAbbreviation')) {
                return $location->getAbbreviation();
            }
        }
        return null;
    }

    /**
     * @return float[]|null
     */
    public function getCoordinates() {
        /** @var CM_Model_Location_Zip $location */
        if ($location = $this->_getLocation(CM_Model_Location::LEVEL_ZIP)) {
            return $location->getCoordinates();
        }
        /** @var CM_Model_Location_City $location */
        if ($location = $this->_getLocation(CM_Model_Location::LEVEL_CITY)) {
            return $location->getCoordinates();
        }
        return null;
    }

    /**
     * @param CM_Model_Location $location
     * @return int|null
     */
    public function getDistance(CM_Model_Location $location) {
        $currentCoordinates = $this->getCoordinates();
        $againstCoordinates = $location->getCoordinates();

        if (!$currentCoordinates || !$againstCoordinates) {
            return null;
        }

        $pi180 = M_PI / 180;
        $currentCoordinates['lat'] *= $pi180;
        $currentCoordinates['lon'] *= $pi180;
        $againstCoordinates['lat'] *= $pi180;
        $againstCoordinates['lon'] *= $pi180;

        $earthRadius = 6371009;
        $arcCosine = acos(
            sin($currentCoordinates['lat']) * sin($againstCoordinates['lat'])
            + cos($currentCoordinates['lat']) * cos($againstCoordinates['lat']) * cos($currentCoordinates['lon'] - $againstCoordinates['lon'])
        );

        return (int) round($earthRadius * $arcCosine);
    }

    /**
     * @param int|null $level
     * @return CM_Model_Location_Abstract|null
     */
    protected function _getLocation($level = null) {
        if (null === $level) {
            $level = $this->getLevel();
        }
        return $this->_location->get($level);
    }

    protected function _loadData() {
        return array();
    }

    /**
     * @param int $ip
     * @return CM_Model_Location|null
     */
    public static function findByIp($ip) {
        $cacheKey = CM_CacheConst::Location_ByIp . '_ip:' . $ip;
        $cache = CM_Cache_Local::getInstance();
        if ((list($level, $id) = $cache->get($cacheKey)) === false) {
            $level = $id = null;
            if ($id = self::_getLocationIdByIp('cm_locationCityIp', 'cityId', $ip)) {
                $level = self::LEVEL_CITY;
            } elseif ($id = self::_getLocationIdByIp('cm_locationCountryIp', 'countryId', $ip)) {
                $level = self::LEVEL_COUNTRY;
            }
            $cache->set($cacheKey, array($level, $id));
        }
        if (!$level && !$id) {
            return null;
        }
        return new self($level, $id);
    }

    /**
     * @param float $lat
     * @param float $lon
     * @return CM_Model_Location|null
     */
    public static function findByCoordinates($lat, $lon) {
        $lat = (float) $lat;
        $lon = (float) $lon;
        $searchRadius = 100000;
        $metersPerDegree = 111100;

        $result = CM_Db_Db::execRead("
			SELECT `id`, `level`
			FROM `cm_tmp_location_coordinates`
			WHERE
				MBRContains(
					GeomFromText(
						'LineString(
							" . ($lat + $searchRadius / ($metersPerDegree / cos($lat))) . "
							" . ($lon + $searchRadius / $metersPerDegree) . ",
							" . ($lat - $searchRadius / ($metersPerDegree / cos($lat))) . "
							" . ($lon - $searchRadius / $metersPerDegree) . "
						)'
					), coordinates
				)
			ORDER BY
				((POW(" . $lat . " - X(coordinates), 2)) + (POW(" . $lon . " - Y(coordinates), 2))) ASC
			LIMIT 1"
        )->fetch();

        if (!$result) {
            return null;
        }

        return new CM_Model_Location($result['level'], $result['id']);
    }

    /**
     * @param string $db_table
     * @param string $db_column
     * @param int    $ip
     * @return int|false
     */
    private static function _getLocationIdByIp($db_table, $db_column, $ip) {
        $result = CM_Db_Db::execRead("SELECT `ipStart`, `" . $db_column . "` FROM `" . $db_table . "`
			WHERE `ipEnd` >= ?
			ORDER BY `ipEnd` ASC
			LIMIT 1", array($ip))->fetch();
        if ($result) {
            if ($result['ipStart'] <= $ip) {
                return (int) $result[$db_column];
            }
        }
        return false;
    }

    public function toArray() {
        return array('level' => $this->getLevel(), 'id' => $this->getId());
    }

    public static function fromArray(array $data) {
        return new self($data['level'], $data['id']);
    }

    /**
     * @param CM_Model_Location_Abstract $location
     * @return CM_Model_Location
     */
    public static function fromLocation(CM_Model_Location_Abstract $location) {
        return new self($location->getLevel(), $location->getId());
    }

    public static function getCacheClass() {
        return 'CM_Model_StorageAdapter_CacheLocal';
    }

    public static function createUSStatesAbbreviation() {
        $idUS = CM_Db_Db::select('cm_locationCountry', 'id', array('abbreviation' => 'US'))->fetchColumn();
        if (false === $idUS) {
            throw new CM_Exception_Invalid('No country with abbreviation `US` found');
        }
        $idUS = (int) $idUS;

        $stateMilitaryId = CM_Db_Db::select('cm_locationState', 'id', array('name'      => 'U.S. Armed Forces', 'abbreviation' => 'AE',
                                                                            'countryId' => $idUS))->fetchColumn();
        if (false === $stateMilitaryId) {
            $stateMilitaryId = CM_Db_Db::insert('cm_locationState', array('countryId'    => $idUS, 'name' => 'U.S. Armed Forces',
                                                                          'abbreviation' => 'AE'));
        }
        $stateMilitaryId = (int) $stateMilitaryId;

        foreach (self::_getUSCityMilitrayBasisList() as $militaryBasis) {
            CM_Db_Db::update('cm_locationCity', array('stateId' => $stateMilitaryId), array('name' => $militaryBasis, 'countryId' => $idUS));
        }

        foreach (self::_getUSStateAbbreviationList() as $stateName => $abbreviation) {
            CM_Db_Db::update('cm_locationState', array('abbreviation' => $abbreviation), array('name' => $stateName, 'countryId' => $idUS));
        }

        self::createAggregation();
    }

    public static function createAggregation() {
        CM_Db_Db::truncate('cm_tmp_location');
        CM_Db_Db::exec('INSERT INTO `cm_tmp_location` (`level`,`id`,`1Id`,`2Id`,`3Id`,`4Id`,`name`, `abbreviation`, `lat`,`lon`)
			SELECT 1, `1`.`id`, `1`.`id`, NULL, NULL, NULL,
					`1`.`name`, `1`.`abbreviation`, NULL, NULL
			FROM `cm_locationCountry` AS `1`
			UNION
			SELECT 2, `2`.`id`, `1`.`id`, `2`.`id`, NULL, NULL,
					`2`.`name`, `2`.`abbreviation`, NULL, NULL
			FROM `cm_locationState` AS `2`
			LEFT JOIN `cm_locationCountry` AS `1` ON(`2`.`countryId`=`1`.`id`)
			UNION
			SELECT 3, `3`.`id`, `1`.`id`, `2`.`id`, `3`.`id`, NULL,
					`3`.`name`, NULL, `3`.`lat`, `3`.`lon`
			FROM `cm_locationCity` AS `3`
			LEFT JOIN `cm_locationState` AS `2` ON(`3`.`stateId`=`2`.`id`)
			LEFT JOIN `cm_locationCountry` AS `1` ON(`3`.`countryId`=`1`.`id`)
			UNION
			SELECT 4, `4`.`id`, `1`.`id`, `2`.`id`, `3`.`id`, `4`.`id`,
					`4`.`name`, NULL, `4`.`lat`, `4`.`lon`
			FROM `cm_locationZip` AS `4`
			LEFT JOIN `cm_locationCity` AS `3` ON(`4`.`cityId`=`3`.`id`)
			LEFT JOIN `cm_locationState` AS `2` ON(`3`.`stateId`=`2`.`id`)
			LEFT JOIN `cm_locationCountry` AS `1` ON(`3`.`countryId`=`1`.`id`)');

        CM_Db_Db::truncate('cm_tmp_location_coordinates');
        CM_Db_Db::exec('INSERT INTO `cm_tmp_location_coordinates` (`level`,`id`,`coordinates`)
			SELECT 3, `id`, POINT(lat, lon)
			FROM `cm_locationCity`
			WHERE `lat` IS NOT NULL AND `lon` IS NOT NULL
			UNION
			SELECT 4, `id`, POINT(lat, lon)
			FROM `cm_locationZip`
			WHERE `lat` IS NOT NULL AND `lon` IS NOT NULL');
    }

    /**
     * @param string $name
     * @param string $abbreviation
     * @return CM_Model_Location
     */
    public static function createCountry($name, $abbreviation) {
        $country = CM_Model_Location_Country::create($name, $abbreviation);
        return self::fromLocation($country);
    }

    /**
     * @param CM_Model_Location $country
     * @param string            $name
     * @param string|null       $abbreviation
     * @param string|null       $maxMind
     * @throws CM_Exception_Invalid
     * @return CM_Model_Location
     */
    public static function createState(CM_Model_Location $country, $name, $abbreviation = null, $maxMind = null) {
        if (CM_Model_Location::LEVEL_COUNTRY !== $country->getLevel()) {
            throw new CM_Exception_Invalid('The parent location should be a country');
        }
        $state = CM_Model_Location_State::create($country->_getLocation(), $name, $abbreviation, $maxMind);
        return self::fromLocation($state);
    }

    /**
     * @param CM_Model_Location $parentLocation
     * @param string            $name
     * @param float             $latitude
     * @param float             $longitude
     * @param string|null       $_maxmind
     * @throws CM_Exception_Invalid
     * @return CM_Model_Location
     */
    public static function createCity(CM_Model_Location $parentLocation, $name, $latitude, $longitude, $_maxmind = null) {
        if (CM_Model_Location::LEVEL_STATE !== $parentLocation->getLevel() && CM_Model_Location::LEVEL_COUNTRY !== $parentLocation->getLevel()) {
            throw new CM_Exception_Invalid('The parent location should be a state or a country');
        }
        $state = $parentLocation->_getLocation(self::LEVEL_STATE);
        $country = $parentLocation->_getLocation(self::LEVEL_COUNTRY);
        $city = CM_Model_Location_City::create($country, $state, $name, $latitude, $longitude, $_maxmind);
        return self::fromLocation($city);
    }

    /**
     * @param CM_Model_Location $city
     * @param string            $name
     * @param float             $latitude
     * @param float             $longitude
     * @throws CM_Exception_Invalid
     * @return CM_Model_Location
     */
    public static function createZip(CM_Model_Location $city, $name, $latitude, $longitude) {
        if (CM_Model_Location::LEVEL_CITY !== $city->getLevel()) {
            throw new CM_Exception_Invalid('The parent location should be a city');
        }
        $zip = CM_Model_Location_Zip::create($city->_getLocation(), $name, $latitude, $longitude);
        return self::fromLocation($zip);
    }

    /**
     * @return string[]
     */
    private static function _getUSStateAbbreviationList() {
        return array(
            'Alabama'              => 'AL',
            'Alaska'               => 'AK',
            'Arizona'              => 'AZ',
            'Arkansas'             => 'AR',
            'California'           => 'CA',
            'Colorado'             => 'CO',
            'Connecticut'          => 'CT',
            'Delaware'             => 'DE',
            'District of Columbia' => 'DC',
            'Florida'              => 'FL',
            'Georgia'              => 'GA',
            'Hawaii'               => 'HI',
            'Idaho'                => 'ID',
            'Illinois'             => 'IL',
            'Indiana'              => 'IN',
            'Iowa'                 => 'IA',
            'Kansas'               => 'KS',
            'Kentucky'             => 'KY',
            'Louisiana'            => 'LA',
            'Maine'                => 'ME',
            'Maryland'             => 'MD',
            'Massachusetts'        => 'MA',
            'Michigan'             => 'MI',
            'Minnesota'            => 'MN',
            'Mississippi'          => 'MS',
            'Missouri'             => 'MO',
            'Montana'              => 'MT',
            'Nebraska'             => 'NE',
            'Nevada'               => 'NV',
            'New Hampshire'        => 'NH',
            'New Jersey'           => 'NJ',
            'New Mexico'           => 'NM',
            'New York'             => 'NY',
            'North Carolina'       => 'NC',
            'North Dakota'         => 'ND',
            'Ohio'                 => 'OH',
            'Oklahoma'             => 'OK',
            'Oregon'               => 'OR',
            'Pennsylvania'         => 'PA',
            'Rhode Island'         => 'RI',
            'South Carolina'       => 'SC',
            'South Dakota'         => 'SD',
            'Tennessee'            => 'TN',
            'Texas'                => 'TX',
            'Utah'                 => 'UT',
            'Vermont'              => 'VT',
            'Virginia'             => 'VA',
            'Washington'           => 'WA',
            'West Virginia'        => 'WV',
            'Wisconsin'            => 'WI',
            'Wyoming'              => 'WY'
        );
    }

    /**
     * @return string[]
     */
    private static function _getUSCityMilitrayBasisList() {
        return array('T3 R1 Nbpp', 'Apo', 'Fpo');
    }
}
