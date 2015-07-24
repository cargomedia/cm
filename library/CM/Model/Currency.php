<?php

class CM_Model_Currency extends CM_Model_Abstract {

    /**
     * @return string
     */
    public function getCode() {
        return $this->_get('code');
    }

    /**
     * @return string
     */
    public function getAbbreviation() {
        return $this->_get('abbreviation');
    }

    /**
     * @param CM_Model_Location $location
     * @throws CM_Exception_Invalid
     */
    public function setCountryMapping(CM_Model_Location $location) {
        $country = $location->get(CM_Model_Location::LEVEL_COUNTRY);
        if (null === $country) {
            throw new CM_Exception_Invalid('Location has no country', null, ['location' => $location->getName()]);
        }
        CM_Db_Db::replace('cm_model_currency_country', ['currencyId' => $this->getId(), 'countryId' => $country->getId()]);
    }

    protected function _getSchema() {
        return new CM_Model_Schema_Definition(array(
            'code'         => ['type' => 'string'],
            'abbreviation' => ['type' => 'string'],
        ));
    }

    protected function _getContainingCacheables() {
        $cacheables = parent::_getContainingCacheables();
        $cacheables[] = new CM_Paging_Currency_All();
        return $cacheables;
    }

    protected function _onDelete() {
        self::_deleteCacheByAbbreviation($this->getAbbreviation());
        parent::_onDelete();
    }

    /**
     * @param string $code
     * @param string $abbreviation
     * @return CM_Model_Currency
     * @throws CM_Exception_Invalid
     */
    public static function create($code, $abbreviation) {
        $currency = new self();
        $currency->_set([
            'code'         => $code,
            'abbreviation' => $abbreviation,
        ]);
        $currency->commit();
        self::_deleteCacheByAbbreviation($abbreviation);
        return $currency;
    }

    /**
     * @param string $abbreviation
     * @return CM_Model_Currency|null
     */
    public static function findByAbbreviation($abbreviation) {
        $cache = CM_Cache_Local::getInstance();
        $cacheKey = CM_CacheConst::Currency_ByAbbreviation . '_abbreviation:' . $abbreviation;

        if (false === ($currencyId = $cache->get($cacheKey))) {
            $currencyId = CM_Db_Db::select('cm_model_currency', 'id', ['abbreviation' => $abbreviation])->fetchColumn();
            $currencyId = $currencyId ? (int) $currencyId : null;
            $cache->set($cacheKey, $currencyId);
        }

        if (null === $currencyId) {
            return null;
        }

        return new self($currencyId);
    }

    /**
     * @param CM_Model_Location $location
     * @return CM_Model_Currency|null
     */
    public static function findByLocation(CM_Model_Location $location) {
        $country = $location->get(CM_Model_Location::LEVEL_COUNTRY);
        if (null === $country) {
            return null;
        }

        $cache = CM_Cache_Local::getInstance();
        $cacheKey = CM_CacheConst::Currency_CountryId . '_countryId:' . $country->getId();
        if (false === ($currencyId = $cache->get($cacheKey))) {
            $currencyId = CM_Db_Db::select('cm_model_currency_country', 'currencyId', ['countryId' => $country->getId()])->fetchColumn();
            $currencyId = $currencyId ? (int) $currencyId : null;
            $cache->set($cacheKey, $currencyId);
        }

        if (null === $currencyId) {
            return null;
        }
        return new self($currencyId);
    }

    /**
     * @param CM_Model_Location|null $location
     * @return CM_Model_Currency
     */
    public static function getByLocation(CM_Model_Location $location = null) {
        if (null === $location) {
            return self::getDefaultCurrency();
        }

        $currency = self::findByLocation($location);
        if (null === $currency) {
            return self::getDefaultCurrency();
        }

        return $currency;
    }

    /**
     * @param string $abbreviation
     * @return CM_Model_Currency
     * @throws CM_Exception_Invalid
     */
    public static function getByAbbreviation($abbreviation) {
        if (!$currency = self::findByAbbreviation($abbreviation)) {
            throw new CM_Exception_Invalid('No currency with abbreviation `' . $abbreviation . '` set');
        }
        return $currency;
    }

    /**
     * @return CM_Model_Currency
     * @throws CM_Exception_Invalid
     */
    public static function getDefaultCurrency() {
        $defaultCurrency = self::_getConfig()->default;
        if (!$currency = self::findByAbbreviation($defaultCurrency['abbreviation'])) {
            throw new CM_Exception_Invalid('No default currency set');
        }
        return $currency;
    }

    public static function getPersistenceClass() {
        return 'CM_Model_StorageAdapter_Database';
    }

    /**
     * @param string $abbreviation
     */
    private static function _deleteCacheByAbbreviation($abbreviation) {
        $cache = CM_Cache_Local::getInstance();
        $cacheKey = CM_CacheConst::Currency_ByAbbreviation . '_abbreviation:' . $abbreviation;
        $cache->delete($cacheKey);
    }
}
