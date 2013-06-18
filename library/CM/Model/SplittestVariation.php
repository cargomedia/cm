<?php

class CM_Model_SplittestVariation extends CM_Model_Abstract {

	CONST TYPE = 17;

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
	 * @throws CM_Exception
	 */
	public function setEnabled($state) {
		$state = (bool) $state;
		$variationsEnabled = $this->getSplittest()->getVariationsEnabled();
		if (!$state && $state != $this->getEnabled() && $variationsEnabled->getCount() <= 1) {
			throw new CM_Exception('No variations for Splittest', 'At least one variation needs to be enabled');
		}
		CM_Db_Db::update(TBL_CM_SPLITTESTVARIATION, array('enabled' => $state), array('id' => $this->getId()));
		$this->_change();
		$variationsEnabled->_change();
	}

	/**
	 * @param bool $refreshCache
	 * @return int
	 */
	public function getConversionCount($refreshCache = null) {
		$aggregationData = $this->_getAggregationData($refreshCache);
		return $aggregationData['conversionCount'];
	}

	/**
	 * @param bool $refreshCache
	 * @return float
	 */
	public function getConversionWeight($refreshCache = null) {
		$aggregationData = $this->_getAggregationData($refreshCache);
		return $aggregationData['conversionWeight'];
	}

	/**
	 * @param bool $refreshCache
	 * @return float
	 */
	public function getConversionRate($refreshCache = null) {
		$fixtureCount = $this->getFixtureCount($refreshCache);
		if (0 == $fixtureCount) {
			return 0;
		}
		return $this->getConversionWeight($refreshCache) / $fixtureCount;
	}

	/**
	 * @param bool $refreshCache
	 * @return int
	 */
	public function getFixtureCount($refreshCache = null) {
		$aggregationData = $this->_getAggregationData($refreshCache);
		return $aggregationData['fixtureCount'];
	}

	/**
	 * @param CM_Model_SplittestVariation $variationWorse
	 * @return float|null P-value
	 */
	public function getSignificance(CM_Model_SplittestVariation $variationWorse) {
		$conversionsA = $this->getConversionCount();
		$nonConversionsA = $this->getFixtureCount() - $this->getConversionCount();
		$conversionsB = $variationWorse->getConversionCount();
		$nonConversionsB = $variationWorse->getFixtureCount() - $variationWorse->getConversionCount();

		$totalA = $conversionsA + $nonConversionsA;
		$totalB = $conversionsB + $nonConversionsB;
		$totalConversions = $conversionsA + $conversionsB;
		$totalNonConversions = $nonConversionsA + $nonConversionsB;
		$total = $totalA + $totalB;

		// See http://math.hws.edu/javamath/ryan/ChiSquare.html
		$nominator = $total * pow($nonConversionsA * $conversionsB - $nonConversionsB * $conversionsA, 2);
		$denominator = $totalA * $totalB * $totalConversions * $totalNonConversions;
		if (0 == $denominator) {
			return null;
		}
		$chiSquare = $nominator / $denominator;

		$p = 1 - stats_cdf_chisquare($chiSquare, 1, 1);
		return $p;
	}

	/**
	 * @return CM_Model_Splittest
	 */
	public function getSplittest() {
		return CM_Model_Splittest::findId($this->_getSplittestId());
	}

	/**
	 * @param bool $refreshCache
	 * @return array
	 */
	protected function _getAggregationData($refreshCache = null) {
		$cacheKey = $this->_getCacheKeyAggregation();
		if ($refreshCache || false === ($aggregationData = CM_CacheLocal::get($cacheKey))) {
			$conversionData = CM_Db_Db::execRead('SELECT COUNT(1) as `conversionCount`, SUM(`conversionWeight`) as `conversionWeight` FROM TBL_CM_SPLITTESTVARIATION_FIXTURE
				WHERE `splittestId`=? AND `variationId`=? AND `conversionStamp` IS NOT NULL',
				array($this->_getSplittestId(), $this->getId()))->fetch();
			$fixtureCount = (int) CM_Db_Db::execRead('SELECT COUNT(1) FROM TBL_CM_SPLITTESTVARIATION_FIXTURE
				WHERE `splittestId`=? AND `variationId`=?',
				array($this->_getSplittestId(), $this->getId()))->fetchColumn();
			$aggregationData = array(
				'conversionCount'  => (int) $conversionData['conversionCount'],
				'conversionWeight' => (float) $conversionData['conversionWeight'],
				'fixtureCount'     => $fixtureCount,
			);
			CM_CacheLocal::set($cacheKey, $aggregationData, 30);
		}
		return $aggregationData;
	}

	protected function _loadData() {
		return CM_Db_Db::select(TBL_CM_SPLITTESTVARIATION, '*', array('id' => $this->getId()))->fetch();
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
