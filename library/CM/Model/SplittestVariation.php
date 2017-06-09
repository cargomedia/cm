<?php

class CM_Model_SplittestVariation extends CM_Model_Abstract {

    /**
     * @return string
     */
    public function getName() {
        return $this->_get('name');
    }

    /**
     * @return bool
     */
    public function getEnabled() {
        return (bool) $this->_get('enabled');
    }

    /**
     * @param bool $state
     * @return $this
     * @throws CM_Exception
     */
    public function setEnabled($state) {
        $state = (bool) $state;
        $variationsEnabled = $this->getSplittest()->getVariationsEnabled();
        if (!$state && $state != $this->getEnabled() && $variationsEnabled->getCount() <= 1) {
            throw new CM_Exception('No variations for Splittest', null, null, [
                'messagePublic' => new CM_I18n_Phrase('At least one variation needs to be enabled'),
            ]);
        }
        CM_Db_Db::update('cm_splittestVariation', array('enabled' => $state), array('id' => $this->getId()));
        $this->_change();
        $variationsEnabled->_change();
        return $this;
    }

    /**
     * @return float
     */
    public function getFrequency() {
        return (float) $this->_get('frequency');
    }

    /**
     * @param float $frequency
     * @return $this
     * @throws CM_Exception_Invalid
     */
    public function setFrequency($frequency) {
        $frequency = (float) $frequency;
        $frequency = round($frequency, 2);
        if ($frequency <= 0) {
            throw new CM_Exception_Invalid('Frequency must be positive');
        }
        CM_Db_Db::update('cm_splittestVariation', ['frequency' => $frequency], ['id' => $this->getId()]);
        return $this->_change();
    }

    /**
     * @return int
     */
    public function getConversionCount() {
        $aggregationData = $this->_getAggregationData();
        return $aggregationData['conversionCount'];
    }

    /**
     * @return float
     */
    public function getConversionWeight() {
        $aggregationData = $this->_getAggregationData();
        return $aggregationData['conversionWeight'];
    }

    /**
     * @return float
     */
    public function getConversionWeightSquared() {
        $aggregationData = $this->_getAggregationData();
        return $aggregationData['conversionWeightSquared'];
    }

    /**
     * @return float
     */
    public function getConversionRate() {
        $fixtureCount = $this->getFixtureCount();
        if (0 === $fixtureCount) {
            return 0.;
        }
        return $this->getConversionWeight() / $fixtureCount;
    }

    /**
     * @return int
     */
    public function getFixtureCount() {
        $aggregationData = $this->_getAggregationData();
        return $aggregationData['fixtureCount'];
    }

    /**
     * @param CM_Model_SplittestVariation $variationWorse
     * @return float|null P-value with Šidák correction
     */
    public function getSignificance(CM_Model_SplittestVariation $variationWorse) {
        $fixturesA = $this->getFixtureCount();
        $fixturesB = $variationWorse->getFixtureCount();
        if (!$fixturesA || !$fixturesB) {
            return null;
        }
        $conversionsA = $this->getConversionCount();
        $conversionsB = $variationWorse->getConversionCount();
        if (!$conversionsA || !$conversionsB) {
            return null;
        }
        $weightA = $this->getConversionWeight();
        $weightB = $variationWorse->getConversionWeight();
        if (!$weightA || !$weightB) {
            return null;
        }
        $rateA = $weightA / $fixturesA;
        $rateB = $weightB / $fixturesB;
        $netRateA = $weightA / $conversionsA;
        $netRateB = $weightB / $conversionsB;

        $fixturesTotal = $fixturesA + $fixturesB;
        $weightTotal = $weightA + $weightB;
        $rateTotal = $weightTotal / $fixturesTotal;

        $conversionsExpectedA = $rateTotal * $fixturesA / $netRateA;
        $conversionsExpectedB = $rateTotal * $fixturesB / $netRateB;
        $varianceExpectedA = $conversionsExpectedA * (1 - $conversionsExpectedA / $fixturesA);
        $varianceExpectedB = $conversionsExpectedB * (1 - $conversionsExpectedB / $fixturesB);

        if ($varianceExpectedA < 9 || $varianceExpectedB < 9) {
            return null;
        }

        $sigmaExpectedA = sqrt($varianceExpectedA);
        $sigmaExpectedB = sqrt($varianceExpectedB);

        if ($conversionsExpectedA - 3 * $sigmaExpectedA < 0 || $conversionsExpectedB - 3 * $sigmaExpectedB < 0) {
            return null;
        }
        if ($conversionsExpectedA + 3 * $sigmaExpectedA > $fixturesA || $conversionsExpectedB + 3 * $sigmaExpectedB > $fixturesB) {
            return null;
        }

        $rateDeviation = abs($rateA - $rateB);
        $sigmaExpectedRateA = $sigmaExpectedA * $netRateA / $fixturesA;
        $sigmaExpectedRateB = $sigmaExpectedB * $netRateB / $fixturesB;
        $sigmaExpectedRateDeviation = sqrt($sigmaExpectedRateA * $sigmaExpectedRateA + $sigmaExpectedRateB * $sigmaExpectedRateB);

        $pValue = 2 * stats_cdf_normal(-$rateDeviation, 0, $sigmaExpectedRateDeviation, 1);

        $variationCount = $this->getSplittest()->getVariations()->getCount();
        $independentExperimentCount = max(1, $variationCount - 1);
        $pValueSidak = 1 - pow(1 - $pValue, $independentExperimentCount);

        return $pValueSidak;
    }

    /**
     * @return CM_Model_Splittest
     */
    public function getSplittest() {
        return CM_Model_Splittest::findId($this->_getSplittestId());
    }

    /**
     * @return float
     */
    public function getStandardDeviation() {
        $fixtureCount = $this->getFixtureCount();
        if (0 === $fixtureCount) {
            return 0.;
        }
        $conversionRate = $this->getConversionRate();
        $conversionWeightSquared = $this->getConversionWeightSquared();
        return sqrt($conversionWeightSquared / $fixtureCount - $conversionRate * $conversionRate);
    }

    /**
     * @return float
     */
    public function getUpperConfidenceBound() {
        $conversionRate = $this->getConversionRate();
        $fixtureCount = $this->getFixtureCount();
        if (0 === $fixtureCount) {
            return $conversionRate;
        }
        $fixtureCountSplittest = 0;
        foreach ($this->getSplittest()->getVariations() as $variation) {
            /** @var CM_Model_SplittestVariation $variation */
            $fixtureCountSplittest += $variation->getFixtureCount();
        }
        $standardDeviation = $this->getStandardDeviation();
        $upperConfidenceBound = $conversionRate + $standardDeviation * sqrt(log($fixtureCountSplittest) / $fixtureCount);
        return $upperConfidenceBound;
    }

    /**
     * @param CM_Model_SplittestVariation $variationWorse
     * @return bool
     */
    public function isDeviationSignificant(CM_Model_SplittestVariation $variationWorse) {
        $significance = $this->getSignificance($variationWorse);
        if (null === $significance) {
            return false;
        }
        return $significance < 0.01;
    }

    /**
     * @return array
     */
    protected function _getAggregationData() {
        $cacheKey = $this->_getCacheKeyAggregation();
        $cache = CM_Cache_Local::getInstance();
        if (false === ($aggregationData = $cache->get($cacheKey))) {
            $conversionData = CM_Db_Db::execRead('
              SELECT COUNT(1) as `conversionCount`, SUM(`conversionWeight`) as `conversionWeight`, SUM(`conversionWeight` * `conversionWeight`) as `conversionWeightSquared`
                FROM `cm_splittestVariation_fixture`
				WHERE `splittestId`=? AND `variationId`=? AND `conversionStamp` IS NOT NULL',
                array($this->_getSplittestId(), $this->getId()))->fetch();
            $fixtureCount = (int) CM_Db_Db::execRead('SELECT COUNT(1) FROM `cm_splittestVariation_fixture`
				WHERE `splittestId`=? AND `variationId`=?',
                array($this->_getSplittestId(), $this->getId()))->fetchColumn();
            $aggregationData = array(
                'conversionCount'         => (int) $conversionData['conversionCount'],
                'conversionWeight'        => (float) $conversionData['conversionWeight'],
                'conversionWeightSquared' => (float) $conversionData['conversionWeightSquared'],
                'fixtureCount'            => $fixtureCount,
            );
            $cache->set($cacheKey, $aggregationData, 30);
        }
        return $aggregationData;
    }

    protected function _loadData() {
        return CM_Db_Db::select('cm_splittestVariation', '*', array('id' => $this->getId()))->fetch();
    }

    /**
     * @return string
     */
    private function _getCacheKeyAggregation() {
        return CM_CacheConst::Splittest_Variation . '_id:' . $this->getId();
    }

    /**
     * @return int
     */
    private function _getSplittestId() {
        return (int) $this->_get('splittestId');
    }
}
