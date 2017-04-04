<?php

class CM_Model_Splittest_User extends CM_Model_Splittest {

    /**
     * @param CM_Model_User $user
     * @param string        $variationName
     * @return bool
     */
    public function isVariationFixture(CM_Model_User $user, $variationName) {
        return $this->_isVariationFixture(new CM_Splittest_Fixture($user), $variationName);
    }

    /**
     * @param CM_Model_User               $user
     * @param CM_Model_SplittestVariation $variation
     * @return bool
     */
    public function setVariationFixture(CM_Model_User $user, CM_Model_SplittestVariation $variation) {
        return $this->_setVariationFixture(new CM_Splittest_Fixture($user), $variation);
    }

    /**
     * @param CM_Model_User $user
     * @param float|null    $weight
     */
    public function setConversion(CM_Model_User $user, $weight = null) {
        $this->_setConversion(new CM_Splittest_Fixture($user), $weight);
    }

    /**
     * @param string        $splittestName
     * @param CM_Model_User $user
     * @param string        $variationName
     * @return bool
     */
    public static function isVariationFixtureStatic($splittestName, CM_Model_User $user, $variationName) {
        /** @var CM_Model_Splittest_User $splittest */
        $splittest = static::find($splittestName);
        if (!$splittest) {
            return false;
        }
        return $splittest->isVariationFixture($user, $variationName);
    }

    /**
     * @param string        $splittestName
     * @param CM_Model_User $user
     * @param string        $variationName
     */
    public static function setVariationFixtureStatic($splittestName, CM_Model_User $user, $variationName) {
        /** @var CM_Model_Splittest_User $splittest */
        $splittest = static::find($splittestName);
        if (!$splittest) {
            return;
        }
        $variation = $splittest->getVariations()->findByName($variationName);
        if (!$variation) {
            return;
        }
        $splittest->_setVariationFixture(new CM_Splittest_Fixture($user), $variation);
    }

    /**
     * @param string        $splittestName
     * @param CM_Model_User $user
     * @param float|null    $weight
     */
    public static function setConversionStatic($splittestName, CM_Model_User $user, $weight = null) {
        /** @var CM_Model_Splittest_User $splittest */
        $splittest = static::find($splittestName);
        if ($splittest) {
            $splittest->setConversion($user, $weight);
        }
    }
}
