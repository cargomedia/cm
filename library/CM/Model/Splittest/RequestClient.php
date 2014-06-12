<?php

class CM_Model_Splittest_RequestClient extends CM_Model_Splittest {

    /**
     * @param string              $splittestName
     * @param CM_Request_Abstract $request
     * @param string              $variationName
     * @return bool
     */
    public static function getVariationFixtureEnabled($splittestName, CM_Request_Abstract $request, $variationName) {
        return static::_getVariationFixtureEnabled($splittestName, $request, $variationName);
    }

    /**
     * @param CM_Request_Abstract $request
     * @param string              $variationName
     * @return bool
     */
    public function isVariationFixture(CM_Request_Abstract $request, $variationName) {
        if ($request->isBotCrawler()) {
            return false;
        }
        return $this->_isVariationFixture(new CM_Splittest_Fixture($request), $variationName);
    }

    /**
     * @param CM_Request_Abstract $request
     * @param float|null          $weight
     */
    public function setConversion(CM_Request_Abstract $request, $weight = null) {
        if ($request->isBotCrawler()) {
            return;
        }
        $this->_setConversion(new CM_Splittest_Fixture($request), $weight);
    }
}
