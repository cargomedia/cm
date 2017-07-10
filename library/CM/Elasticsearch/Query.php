<?php

class CM_Elasticsearch_Query {

    private $_queries = [];
    private $_filters = [];
    private $_sorts = [];
    private $_mode, $_filterMode;

    /** @var float|null */
    private $_minScore;

    /** @var int|null */
    private $_randomScoreSeed;

    /**
     * @param string|null $mode       must,must_not,should
     * @param string|null $filterMode or, and, not
     */
    function __construct($mode = null, $filterMode = null) {
        if (is_null($mode)) {
            $mode = 'must';
        }
        if (is_null($filterMode)) {
            $filterMode = 'and';
        }
        $this->_mode = (string) $mode;
        $this->_filterMode = (string) $filterMode;
    }

    public function query($query) {
        if ($query instanceof CM_Elasticsearch_Query) {
            $query = $query->getQuery();
        }
        $this->_queries[] = $query;
    }

    /**
     * @param string $field
     * @param string $value
     */
    public function queryField($field, $value) {
        $this->_queries[] = ['field' => [$field => $value]];
    }

    /**
     * @param string     $field
     * @param string     $query
     * @param array|null $options
     */
    public function queryMatch($field, $query, array $options = null) {
        $field = (string) $field;
        $query = (string) $query;
        $data = [
            'query' => $query,
        ];
        if (isset($options['operator'])) {
            $data['operator'] = (string) $options['operator'];
        }
        if (isset($options['fuzziness'])) {
            $data['fuzziness'] = (string) $options['fuzziness'];
        }
        if (isset($options['analyzer'])) {
            $data['analyzer'] = (string) $options['analyzer'];
        }
        $this->query(['match' => [$field => $data]]);
    }

    /**
     * @param string[]    $fields
     * @param string      $value
     * @param string|null $operator  'or' / 'and'
     * @param float|null  $fuzziness 0 - 1
     */
    public function queryMatchMulti($fields, $value, $operator = null, $fuzziness = null) {
        $data = [
            'query'  => $value,
            'fields' => $fields,
        ];
        if (null !== $operator) {
            $data['operator'] = (string) $operator;
        }
        if (null !== $fuzziness) {
            $data['fuzziness'] = (float) $fuzziness;
        }
        $this->query(['multi_match' => $data]);
    }

    /**
     * @param array $filter
     */
    protected function _filter(array $filter) {
        $this->_filters[] = $filter;
    }

    /**
     * @param array $filter
     */
    protected function _filterNot(array $filter) {
        $this->_filters[] = ['not' => ['filter' => $filter]];
    }

    /**
     * @param string $field
     */
    public function filterExists($field) {
        $this->_filter(['exists' => ['field' => (string) $field]]);
    }

    /**
     * @param string $field
     * @param string $value
     */
    public function filterPrefix($field, $value) {
        $this->_filter(['prefix' => [$field => $value]]);
    }

    /**
     * @param string          $field
     * @param string|string[] $value
     */
    public function filterTerm($field, $value) {
        if (is_array($value)) {
            $this->_filter(['terms' => [$field => $value]]);
        } else {
            $this->_filter(['term' => [$field => $value]]);
        }
    }

    /**
     * @param string          $field
     * @param string|string[] $value
     */
    public function filterTermNot($field, $value) {
        if (is_array($value)) {
            $this->_filterNot(['terms' => [$field => $value]]);
        } else {
            $this->_filterNot(['term' => [$field => $value]]);
        }
    }

    /**
     * @param string    $field
     * @param int|null  $from
     * @param int|null  $to
     * @param bool|null $openIntervalFrom
     * @param bool|null $openIntervalTo
     */
    public function filterRange($field, $from = null, $to = null, $openIntervalFrom = null, $openIntervalTo = null) {
        $range = [];
        if ($from !== null) {
            $operand = 'gte';
            if ($openIntervalFrom) {
                $operand = 'gt';
            }
            $range[$operand] = $from;
        }
        if ($to !== null) {
            $operand = 'lte';
            if ($openIntervalTo) {
                $operand = 'lt';
            }
            $range[$operand] = $to;
        }
        if (!empty($range)) {
            $this->_filter(['range' => [$field => $range]]);
        }
    }

    /**
     * @param string $field
     */
    public function filterMissing($field) {
        $this->_filter(['missing' => ['field' => (string) $field, 'existence' => true, 'null_value' => true]]);
    }

    /**
     * @param string            $field
     * @param CM_Model_Location $location
     * @param int               $distance
     */
    public function filterGeoDistance($field, CM_Model_Location $location, $distance) {
        $coordinates = $location->getCoordinates(CM_Model_Location::LEVEL_CITY);
        if (!$coordinates) {
            return;
        }
        $distance = ($distance / 1000) . 'km';
        $this->_filter(['geo_distance' => [$field => $coordinates, 'distance' => $distance, 'distance_type' => 'plane',]]);
    }

    /**
     * @return float|null
     */
    public function getMinScore() {
        return $this->_minScore;
    }

    /**
     * @param float $percentage
     * @param int   $seed
     */
    public function selectRandomSubset($percentage, $seed) {
        $percentage = (float) $percentage;
        $seed = (int) $seed;
        $this->_minScore = (float) (1 << 24) * (1 - $percentage / 100);
        $this->_randomScoreSeed = (int) $seed;
    }

    /**
     * @param string            $field
     * @param CM_Model_Location $location
     */
    public function sortGeoDistance($field, CM_Model_Location $location) {
        $coordinates = $location->getCoordinates();
        if (!$coordinates) {
            return;
        }
        $this->_sort(['_geo_distance' => [$field => $coordinates]]);
    }

    public function sortScore() {
        $this->_sort(['_score' => 'desc']);
    }

    /**
     * @return array
     */
    public function getQuery() {
        if (count($this->_queries) == 0) {
            $query = ['match_all' => new stdClass()];
        } elseif (count($this->_queries) == 1) {
            $query = reset($this->_queries);
        } else {
            $query = ['bool' => [$this->_mode => $this->_queries]];
        }
        if (!empty($this->_filters)) {
            $query = ['filtered' => ['query' => $query, 'filter' => [$this->_filterMode => $this->_filters]]];
        }
        if (isset($this->_randomScoreSeed)) {
            $query = ['function_score' => [
                'random_score' => ['seed' => $this->_randomScoreSeed],
                'filter'       => ['query' => $query],
                'boost_mode'   => 'replace',
            ]];
        }
        return $query;
    }

    /**
     * @return array
     */
    public function getSort() {
        if (empty($this->_sorts)) {
            $this->_sortDefault();
        }
        return $this->_sorts;
    }

    /**
     * @param array $sort
     */
    protected function _sort(array $sort) {
        $sortNew = [];
        foreach ($sort as $key => $value) {
            $key = (string) $key;
            if (null === $value) {
                $value = 'desc';
            }
            $sortNew[$key] = $value;
        }
        $this->_sorts[] = $sortNew;
    }

    protected function _sortDefault() {
    }

    /**
     * @param int $timestamp
     * @return string
     */
    public static function formatDate($timestamp) {
        return date('Y-m-d', $timestamp);
    }

    /**
     * @param int $timestamp Timestamp to return as date
     * @param int $round     OPTIONAL Number of seconds the result should be rounded (floor) (default = 1)
     * @return string Date in format Y-m-d\TH:i:s\Z
     */
    public static function formatDateTime($timestamp, $round = 1) {
        $timestamp = $timestamp - ($timestamp % $round);
        return date('Y-m-d\TH:i:s\Z', $timestamp);
    }

    /**
     * @param string $term
     * @param array  $chars OPTIONAL
     * @return string
     */
    public static function escape($term, array $chars = ['\\', '+', '-', '&&', '||', '!', '(', ')', '{', '}', '[', ']', '^', '"', '~', '*', '?',
        ':']) {
        foreach ($chars as $char) {
            $term = str_replace($char, '\\' . $char, $term);
        }
        return $term;
    }
}
