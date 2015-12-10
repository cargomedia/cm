<?php

class CM_Util_UaParsing extends Jenssegers\Agent\Agent {

    const CACHE_PREFIX = 'CM_Util_UaParsing:';

    protected $_CMRules = [
        'Samsung' => 'SM-G925F',
    ];

    public function getRules() {
        return $this->mergeRules(parent::getRules(), $this->_CMRules);
    }

    /**
     * @param string $brand
     * @param string $modelToken
     * @return bool
     */
    public function isAModel($brand, $modelToken) {
        $brand = (string) $brand;
        $modelToken = (string) $modelToken;

        $this->setDetectionType(self::DETECTION_TYPE_EXTENDED);
        $_rules = $this->getRules();

        return $this->matchUAAgainstKey($brand) && (bool) preg_match(sprintf('#%s#is', $_rules[$brand]), $modelToken);
    }

    public function checkHttpHeadersForMobile() {
        $cacheKey = self::CACHE_PREFIX . md5(serialize($this->httpHeaders)) . ':checkHttpHeadersForMobile:';
        $cache = CM_Cache_Shared::getInstance();
        if (($isMatch = $cache->get($cacheKey)) === false) {
            $isMatch = parent::checkHttpHeadersForMobile();
            $cache->set($cacheKey, (int) $isMatch);
        }
        return (bool) $isMatch;
    }

    protected function matchUAAgainstKey($key) {
        $cacheKey = self::CACHE_PREFIX . md5($this->userAgent) . ':matchUAAgainstKey:' . $key;
        $cache = CM_Cache_Shared::getInstance();
        if (($isMatch = $cache->get($cacheKey)) === false) {
            $isMatch = parent::matchUAAgainstKey($key);
            $cache->set($cacheKey, (int) $isMatch);
        }
        return (bool) $isMatch;
    }

    protected function matchDetectionRulesAgainstUA($userAgent = null) {
        $cacheKey = self::CACHE_PREFIX . md5($this->userAgent) . ':matchDetectionRulesAgainstUA';
        $cache = CM_Cache_Shared::getInstance();
        if (($isMatch = $cache->get($cacheKey)) === false) {
            $isMatch = parent::matchDetectionRulesAgainstUA();
            $cache->set($cacheKey, (int) $isMatch);
        }
        return (bool) $isMatch;
    }

    //TODO cache
    //findDetectionRulesAgainstUA
    //isTablet
    //isRobot

}
