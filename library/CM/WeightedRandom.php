<?php

/**
 * Based on: http://w-shadow.com/blog/2008/12/10/fast-weighted-random-choice-in-php/
 */
class CM_WeightedRandom {

    /** @var array */
    private $_values = array();

    /** @var array */
    private $_lookup = array();

    /** @var float */
    private $_weightTotal = 0;

    /**
     * Initalize the weighted random selector
     *
     * @param mixed[] $values  Array of elements to choose from
     * @param float[] $weights An array of weights. Weight must be a positive number.
     */
    public function __construct(array $values, array $weights) {
        $this->_values = $values;

        for ($i = 0; $i < count($weights); $i++) {
            $this->_weightTotal += $weights[$i];
            $this->_lookup[$i] = $this->_weightTotal;
        }
    }

    /**
     * Randomly select one of the elements based on their weights.
     *
     * @return mixed Selected element
     */
    public function lookup() {
        $r = mt_rand() / mt_getrandmax() * $this->_weightTotal;
        return $this->_values[$this->_binarySearch($r, $this->_lookup)];
    }

    /**
     * Search a sorted array for a number. Returns the item's index if found. Otherwise
     * returns the position where it should be inserted, or count($haystack)-1 if the
     * $needle is higher than every element in the array.
     *
     * @param int   $needle
     * @param array $haystack
     * @return int
     */
    private function _binarySearch($needle, $haystack) {
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
