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
		CM_Mysql::update(TBL_CM_SPLITTESTVARIATION, array('enabled' => $state), array('id' => $this->getId()));
		$this->_change();
		$variationsEnabled->_change();
	}

	/**
	 * @return int
	 */
	public function getConversionCount() {
		return (int) CM_Mysql::exec('SELECT COUNT(1) FROM TBL_CM_SPLITTESTVARIATION_FIXTURE WHERE `splittestId`=? AND `variationId`=? AND `conversionStamp` IS NOT NULL', $this->_getSplittestId(), $this->getId())->fetchOne();
	}

	/**
	 * @return float
	 */
	public function getConversionWeight() {
		return (float) CM_Mysql::exec('SELECT SUM(`conversionWeight`) FROM TBL_CM_SPLITTESTVARIATION_FIXTURE WHERE `splittestId`=?
		AND `variationId`=? AND `conversionStamp` IS NOT NULL', $this->_getSplittestId(), $this->getId())->fetchOne();
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
		return CM_Mysql::count(TBL_CM_SPLITTESTVARIATION_FIXTURE, array('splittestId' => $this->_getSplittestId(), 'variationId' => $this->getId()));
	}

	/**
	 * @param CM_Model_SplittestVariation $variationWorse
	 * @return float|null P-value
	 */
	public function getSignificance(CM_Model_SplittestVariation $variationWorse) {
		$conversionsA = $this->getConversionWeight();
		$fixturesA = $this->getFixtureCount();
		$conversionsB = $variationWorse->getConversionWeight();
		$fixturesB = $variationWorse->getFixtureCount();

		// See http://math.hws.edu/javamath/ryan/ChiSquare.html
		$nominator = (($conversionsA + $conversionsB + $fixturesA + $fixturesB) * pow($fixturesA * $conversionsB - $fixturesB * $conversionsA, 2));
		$denominator = (($fixturesA + $fixturesB) * ($conversionsA + $conversionsB) * ($fixturesB + $conversionsB) * ($fixturesA + $conversionsA));
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

	protected function _loadData() {
		return CM_Mysql::select(TBL_CM_SPLITTESTVARIATION, '*', array('id' => $this->getId()))->fetchAssoc();
	}

	/**
	 * @return int
	 */
	private function _getSplittestId() {
		return (int) $this->_get('splittestId');
	}
}
