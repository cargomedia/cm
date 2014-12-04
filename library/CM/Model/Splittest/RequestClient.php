<?php

class CM_Model_Splittest_RequestClient extends CM_Model_Splittest {

    /**
     * @param CM_Http_Request_Abstract $request
     * @param string              $variationName
     * @return bool
     */
    public function isVariationFixture(CM_Http_Request_Abstract $request, $variationName) {
        if ($request->isBotCrawler()) {
            return false;
        }
        return $this->_isVariationFixture(new CM_Splittest_Fixture($request), $variationName);
    }

    /**
     * @param CM_Http_Request_Abstract $request
     * @param float|null          $weight
     */
    public function setConversion(CM_Http_Request_Abstract $request, $weight = null) {
        if ($request->isBotCrawler()) {
            return;
        }
        $this->_setConversion(new CM_Splittest_Fixture($request), $weight);
    }

    /**
     * @param string              $splittestName
     * @param CM_Http_Request_Abstract $request
     * @param string              $variationName
     * @return bool
     */
    public static function isVariationFixtureStatic($splittestName, CM_Http_Request_Abstract $request, $variationName) {
        /** @var CM_Model_Splittest_RequestClient $splittest */
        $splittest = static::find($splittestName);
        if (!$splittest) {
            return false;
        }
        return $splittest->isVariationFixture($request, $variationName);
    }

    /**
     * @param string              $splittestName
     * @param CM_Http_Request_Abstract $request
     * @param float|null          $weight
     */
    public static function setConversionStatic($splittestName, CM_Http_Request_Abstract $request, $weight = null) {
        /** @var CM_Model_Splittest_RequestClient $splittest */
        $splittest = static::find($splittestName);
        if ($splittest) {
            $splittest->setConversion($request, $weight);
        }
    }
}
