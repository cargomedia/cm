<?php

class CM_Elasticsearch_Document {

    /** @var string|null */
    protected $_id = null;

    /** @var array Document data */
    protected $_data = array();

    /**
     * @param string|null $id   Id is create if empty
     * @param array|null  $data Data array
     */
    public function __construct($id = null, array $data = null) {
        if (null !== $id) {
            $id = (string) $id;
        }
        if (null === $data) {
            $data = [];
        }
        $this->setId($id);
        $this->setData($data);
    }

    /**
     * @return string|null
     */
    public function getId() {
        return $this->_id;
    }

    /**
     * @param string|null $id
     */
    public function setId($id = null) {
        if (null !== $id) {
            $id = (string) $id;
        }
        $this->_id = $id;
    }

    /**
     * @param string $key
     * @return mixed
     * @throws CM_Exception_Invalid
     */
    public function get($key) {
        if (!$this->has($key)) {
            throw new CM_Exception_Invalid('Field does not exist', null, ['key' => $key]);
        }
        return $this->_data[$key];
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function set($key, $value) {
        $this->_data[$key] = $value;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key) {
        return array_key_exists($key, $this->_data);
    }

    /**
     * @param string $key
     */
    public function remove($key) {
        unset($this->_data[$key]);
    }

    /**
     * @param string $key       Field key
     * @param float  $latitude  Latitude value
     * @param float  $longitude Longitude value
     * @link http://www.elasticsearch.org/guide/reference/mapping/geo-point-type.html
     */
    public function addGeoPoint($key, $latitude, $longitude) {
        $value = ['lat' => $latitude, 'lon' => $longitude];

        $this->set($key, $value);
    }

    /**
     * @param  array $data Data
     */
    public function setData(array $data) {
        $this->_data = $data;
    }

    /**
     * @return array Document data
     */
    public function getData() {
        return $this->_data;
    }

    /**
     * @param  array $data
     * @return CM_Elasticsearch_Document
     */
    public static function create(array $data) {
        return new self(null, $data);
    }
}
