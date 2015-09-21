<?php

class CM_Elasticsearch_Document {

    /** @var string|null */
    protected $_id = null;

    /** @var array Document data */
    protected $_data = array();

    /**
     * Creates a new document
     *
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
     */
    public function get($key) {
        return $this->_data[$key];
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return CM_Elasticsearch_Document
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
     * @throws CM_Exception_Invalid
     * @return CM_Elasticsearch_Document
     */
    public function remove($key) {
        if (!$this->has($key)) {
            throw new CM_Exception_Invalid("Field `{$key}` does not exist");
        }
        unset($this->_data[$key]);
    }

    /**
     * Adds a geopoint to the document
     *
     * Geohashes are not yet supported
     *
     * @param string $key       Field key
     * @param float  $latitude  Latitude value
     * @param float  $longitude Longitude value
     * @link http://www.elasticsearch.org/guide/reference/mapping/geo-point-type.html
     * @return CM_Elasticsearch_Document
     */
    public function addGeoPoint($key, $latitude, $longitude) {
        $value = array('lat' => $latitude, 'lon' => $longitude,);

        $this->set($key, $value);
    }

    /**
     * Overwrites the current document data with the given data
     *
     * @param  array $data Data
     * @return CM_Elasticsearch_Document
     */
    public function setData(array $data) {
        $this->_data = $data;
    }

    /**
     * Returns the document data
     *
     * @return array Document data
     */
    public function getData() {
        return $this->_data;
    }

    /**
     * Returns the document as an array
     * @return array
     */
    public function toArray() {
        return $this->getData();
    }

    /**
     * @param  array $data
     * @return CM_Elasticsearch_Document
     */
    public static function create(array $data) {
        return new self(null, $data);
    }
}
