<?php

class CM_Model_Location extends CM_Model_Abstract {

    const LEVEL_COUNTRY = 1;
    const LEVEL_STATE = 2;
    const LEVEL_CITY = 3;
    const LEVEL_ZIP = 4;

    const EARTH_RADIUS = 6371009;

    /** @var CM_Model_Location_Abstract[] */
    protected $_locationList = array();

    /**
     * @param int $level A LEVEL_*-const
     * @param int $id
     */
    public function __construct($level, $id) {
        $this->_construct(array('id' => $id, 'level' => $level));
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
        return (int) $this->_getIdKey('level');
    }

    /**
     * @param int|null $level
     * @return int|null
     */
    public function getId($level = null) {
        if (null === $level) {
            return (int) $this->_getIdKey('id');
        }
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

        $arcCosine = acos(
            sin($currentCoordinates['lat']) * sin($againstCoordinates['lat'])
            + cos($currentCoordinates['lat']) * cos($againstCoordinates['lat']) * cos($currentCoordinates['lon'] - $againstCoordinates['lon'])
        );

        return (int) round(self::EARTH_RADIUS * $arcCosine);
    }

    /**
     * @param int|null $level
     * @throws CM_Exception_Invalid
     * @return CM_Model_Location_Abstract|null
     */
    protected function _getLocation($level = null) {
        if (null === $level) {
            $level = $this->getLevel();
        }
        $level = (int) $level;
        $typeList = array(
            self::LEVEL_COUNTRY => CM_Model_Location_Country::getTypeStatic(),
            self::LEVEL_STATE   => CM_Model_Location_State::getTypeStatic(),
            self::LEVEL_CITY    => CM_Model_Location_City::getTypeStatic(),
            self::LEVEL_ZIP     => CM_Model_Location_Zip::getTypeStatic(),
        );
        if (!isset($typeList[$level])) {
            throw new CM_Exception_Invalid('Invalid location level `' . $level . '`');
        }
        if (!array_key_exists($level, $this->_locationList)) {
            /** @var array[] $locationDataList */
            $locationDataList = $this->_get('locationDataList');
            if (!isset($locationDataList[$level])) {
                $this->_locationList[$level] = null;
            } else {
                $locationData = $locationDataList[$level];
                $this->_locationList[$level] = self::factoryGeneric($typeList[$level], $locationData['id'], $locationData['data']);
            }
        }
        return $this->_locationList[$level];
    }

    protected function _loadData() {
        $id = $this->getId();
        $level = $this->getLevel();
        switch ($level) {
            case self::LEVEL_COUNTRY:
                $location = new CM_Model_Location_Country($id);
                break;
            case self::LEVEL_STATE:
                $location = new CM_Model_Location_State($id);
                break;
            case self::LEVEL_CITY:
                $location = new CM_Model_Location_City($id);
                break;
            case self::LEVEL_ZIP:
                $location = new CM_Model_Location_Zip($id);
                break;
            default:
                throw new CM_Exception_Invalid('Invalid location level `' . $level . '`');
        }
        $this->_locationList = array();
        $locationDataList = array();
        do {
            $this->_locationList[$location->getLevel()] = $location;
            $locationDataList[$location->getLevel()] = array('id' => $location->getIdRaw(), 'data' => $location->_getData());
        } while ($location = $location->getParent());
        return array('locationDataList' => $locationDataList);
    }

    /**
     * @param int $ip
     * @return CM_Model_Location|null
     */
    public static function findByIp($ip) {
        $cacheKey = CM_CacheConst::Location_ByIp . '_ip:' . $ip;
        $cache = CM_Cache_Local::getInstance();
        $location = null;
        if ((list($level, $id) = $cache->get($cacheKey)) === false) {
            if ($location = self::_findByIp($ip)) {
                $level = $location->getLevel();
                $id = $location->getId();
            }
            $cache->set($cacheKey, array($level, $id));
        }
        if (!$level || !$id) {
            return null;
        }
        if (!$location) {
            $location = new self($level, $id);
        }
        return $location;
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

        $pi180 = M_PI / 180;
        $metersPerDegreeEquator = self::EARTH_RADIUS * $pi180;
        $metersPerDegree = $metersPerDegreeEquator * cos($lat * $pi180);

        $latMin = $lat - $searchRadius / $metersPerDegreeEquator;
        $latMax = $lat + $searchRadius / $metersPerDegreeEquator;
        $lonMin = $lon - $searchRadius / $metersPerDegree;
        $lonMax = $lon + $searchRadius / $metersPerDegree;
        $query = "SELECT `id`, `level` FROM `cm_tmp_location_coordinates`
            WHERE
                MBRContains(LineString(Point($latMax, $lonMax), Point($latMin, $lonMin)), coordinates)
            ORDER BY
                ((POW($lat - X(coordinates), 2)) + (POW($lon - Y(coordinates), 2))) ASC
            LIMIT 1";
        $result = CM_Db_Db::execRead($query)->fetch();

        if (!$result) {
            return null;
        }

        return new CM_Model_Location($result['level'], $result['id']);
    }

    /**
     * @param int $ip
     * @return CM_Model_Location|null
     */
    private static function _findByIp($ip) {
        $result = CM_Db_Db::execRead("SELECT `id`, `level`, `ipStart` FROM `cm_model_location_ip`
			WHERE `ipEnd` >= ?
			ORDER BY `ipEnd` ASC
			LIMIT 1", array($ip))->fetch();
        if ($result) {
            if ($result['ipStart'] <= $ip) {
                return new CM_Model_Location($result['level'], $result['id']);
            }
        }
        return null;
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

    public static function createAggregation() {
        CM_Db_Db::truncate('cm_tmp_location');
        CM_Db_Db::exec('INSERT INTO `cm_tmp_location` (`level`,`id`,`1Id`,`2Id`,`3Id`,`4Id`,`name`, `abbreviation`, `nameFull`, `lat`,`lon`)
			SELECT 1, `1`.`id`, `1`.`id`, NULL, NULL, NULL,
					`1`.`name`, `1`.`abbreviation`, CONCAT_WS(" ", `1`.`name`, `1`.`abbreviation`), NULL, NULL
			FROM `cm_model_location_country` AS `1`
			UNION
			SELECT 2, `2`.`id`, `1`.`id`, `2`.`id`, NULL, NULL,
					`2`.`name`, `2`.`abbreviation`, CONCAT_WS(" ", `2`.name, `2`.`abbreviation`, `1`.`name`, `1`.`abbreviation`), NULL, NULL
			FROM `cm_model_location_state` AS `2`
			LEFT JOIN `cm_model_location_country` AS `1` ON(`2`.`countryId`=`1`.`id`)
			UNION
			SELECT 3, `3`.`id`, `1`.`id`, `2`.`id`, `3`.`id`, NULL,
					`3`.`name`, NULL, CONCAT_WS(" ", `3`.`name`, `2`.`name`, `2`.`abbreviation`, `1`.`name`, `1`.`abbreviation`), `3`.`lat`, `3`.`lon`
			FROM `cm_model_location_city` AS `3`
			LEFT JOIN `cm_model_location_state` AS `2` ON(`3`.`stateId`=`2`.`id`)
			LEFT JOIN `cm_model_location_country` AS `1` ON(`3`.`countryId`=`1`.`id`)
			UNION
			SELECT 4, `4`.`id`, `1`.`id`, `2`.`id`, `3`.`id`, `4`.`id`,
					`4`.`name`, NULL, CONCAT_WS(" ", `4`.`name`, `3`.`name`, `2`.`name`, `2`.`abbreviation`, `1`.`name`, `1`.`abbreviation`), `4`.`lat`, `4`.`lon`
			FROM `cm_model_location_zip` AS `4`
			LEFT JOIN `cm_model_location_city` AS `3` ON(`4`.`cityId`=`3`.`id`)
			LEFT JOIN `cm_model_location_state` AS `2` ON(`3`.`stateId`=`2`.`id`)
			LEFT JOIN `cm_model_location_country` AS `1` ON(`3`.`countryId`=`1`.`id`)');

        CM_Db_Db::truncate('cm_tmp_location_coordinates');
        CM_Db_Db::exec('INSERT INTO `cm_tmp_location_coordinates` (`level`,`id`,`coordinates`)
			SELECT 3, `id`, POINT(lat, lon)
			FROM `cm_model_location_city`
			WHERE `lat` IS NOT NULL AND `lon` IS NOT NULL
			UNION
			SELECT 4, `id`, POINT(lat, lon)
			FROM `cm_model_location_zip`
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
}
