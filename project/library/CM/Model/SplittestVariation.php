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
			throw new CM_Exception('At least one variation needs to be enabled');
		}
		CM_Mysql::update(TBL_CM_SPLITTESTVARIATION, array('enabled' => $state), array('id' => $this->getId()));
		$this->_change();
		$variationsEnabled->_change();
	}

	/**
	 * @return int
	 */
	public function getConversionCount() {
		return (int) CM_Mysql::exec('SELECT COUNT(1) FROM TBL_CM_SPLITTESTVARIATION_USER WHERE `splittestId`=? AND `variationId`=? AND `conversionStamp` IS NOT NULL', $this->_getSplittestId(), $this->getId())->fetchOne();
	}

	/**
	 * @return float
	 */
	public function getConversionRate() {
		$fixtureCount = $this->getFixtureCount();
		if (0 == $fixtureCount) {
			return 0;
		}
		return $this->getConversionCount() / $fixtureCount;
	}

	/**
	 * @return int
	 */
	public function getFixtureCount() {
		return CM_Mysql::count(TBL_CM_SPLITTESTVARIATION_USER, array('splittestId' => $this->_getSplittestId(), 'variationId' => $this->getId()));
	}

	/**
	 * @param CM_Model_SplittestVariation $variationWorse
	 * @return float|null P-value
	 */
	public function getSignificance(CM_Model_SplittestVariation $variationWorse) {
		$conversionsA = $this->getConversionCount();
		$fixturesA = $this->getFixtureCount();
		$conversionsB = $variationWorse->getConversionCount();
		$fixturesB = $variationWorse->getFixtureCount();
		if (0 == $fixturesA || 0 == $fixturesB) {
			return null;
		}
		$rateA = $conversionsA / $fixturesA;
		$rateB = $conversionsB / $fixturesB;
		$error = sqrt(($rateA * (1 - $rateA) / $fixturesA) + ($rateB * (1 - $rateB) / $fixturesB));
		$x = ($rateB - $rateA) / $error;

		// Abramowitz & Stegun - Handbook of Mathematical Functions: 26.2.19
		$d1 = 0.0498673470;
		$d2 = 0.0211410061;
		$d3 = 0.0032776263;
		$d4 = 0.0000380036;
		$d5 = 0.0000488906;
		$d6 = 0.0000053830;
		$p = 1 -
				0.5 * pow((1 + $d1 * pow($x, 1) + $d2 * pow($x, 2) + $d3 * pow($x, 3) + $d4 * pow($x, 4) + $d5 * pow($x, 5) + $d6 * pow($x, 6)), -16);
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
