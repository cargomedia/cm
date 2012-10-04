<?php

/**
 * Based on: http://w-shadow.com/blog/2008/12/10/fast-weighted-random-choice-in-php/
 */
class CM_WeightedRandom {
	private $_values = array();
	private $_lookup = array();
	private $_total_weight = 0;

	/**
	 * Initalize the weighted random selector
	 *
	 * @param array $values  Array of elements to choose from
	 * @param array $weights An array of weights. Weight must be a positive number.
	 */
	function __construct($values, $weights) {
		$this->_values = $values;

		for ($i = 0; $i < count($weights); $i++) {
			$this->_total_weight += $weights[$i];
			$this->_lookup[$i] = $this->_total_weight;
		}
	}

	/**
	 * Randomly select one of the elements based on their weights.
	 *
	 * @return mixed Selected element
	 */
	function lookup() {
		$r = mt_rand(0, $this->_total_weight);
		return $this->_values[$this->binary_search($r, $this->_lookup)];
	}

	/**
	 * binary_search()
	 * Search a sorted array for a number. Returns the item's index if found. Otherwise
	 * returns the position where it should be inserted, or count($haystack)-1 if the
	 * $needle is higher than every element in the array.
	 *
	 * @param int   $needle
	 * @param array $haystack
	 * @return int
	 */
	private function binary_search($needle, $haystack) {
		$high = count($haystack) - 1;
		$low = 0;

		while ($low < $high) {
			$probe = (int) (($high + $low) / 2);
			if ($haystack[$probe] < $needle) {
				$low = $probe + 1;
			} elseif ($haystack[$probe] > $needle) {
				$high = $probe - 1;
			} else {
				return $probe;
			}
		}

		if ($low != $high) {
			return $probe;
		} else {
			if ($haystack[$low] >= $needle) {
				return $low;
			} else {
				return $low + 1;
			}
		}
	}

}
