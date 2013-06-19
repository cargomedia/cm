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
	 * @return int
	 */
	public function getConversionCount() {
		return (int) CM_Db_Db::exec('SELECT COUNT(1) FROM TBL_CM_SPLITTESTVARIATION_FIXTURE WHERE `splittestId`=?
		AND `variationId`=? AND `conversionStamp` IS NOT NULL', array($this->_getSplittestId(), $this->getId()))->fetchColumn();
	}

	/**
	 * @return float
	 */
	public function getConversionWeight() {
		return (float) CM_Db_Db::exec('SELECT SUM(`conversionWeight`) FROM TBL_CM_SPLITTESTVARIATION_FIXTURE WHERE `splittestId`=?
		AND `variationId`=? AND `conversionStamp` IS NOT NULL', array($this->_getSplittestId(), $this->getId()))->fetchColumn();
	}

	/**
	 * @return float
	 */
	public function getConversionRate() {
		$fixtureCount = $this->getFixtureCount();
		if (0 == $fixtureCount) {
			return 0;
		}
		return $this->getConversionWeight() / $fixtureCount;
	}

	/**
	 * @return int
	 */
	public function getFixtureCount() {
		return CM_Db_Db::count(TBL_CM_SPLITTESTVARIATION_FIXTURE, array('splittestId' => $this->_getSplittestId(), 'variationId' => $this->getId()));
	}

	/**
	 * @param CM_Model_SplittestVariation $variationWorse
	 * @return float|null P-value
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
		$sigmaExpectedA = sqrt($conversionsExpectedA * (1 - $conversionsExpectedA / $fixturesA));
		$sigmaExpectedB = sqrt($conversionsExpectedB * (1 - $conversionsExpectedB / $fixturesB));

		if ($sigmaExpectedA < 3 || $sigmaExpectedB < 3) {
			return null;
		}
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

		return $pValue;
	}

	/**
	 * @return CM_Model_Splittest
	 */
	public function getSplittest() {
		return CM_Model_Splittest::findId($this->_getSplittestId());
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

	protected function _loadData() {
		return CM_Db_Db::select(TBL_CM_SPLITTESTVARIATION, '*', array('id' => $this->getId()))->fetch();
	}

	/**
	 * @return int
	 */
	private function _getSplittestId() {
		return (int) $this->_get('splittestId');
	}
}
