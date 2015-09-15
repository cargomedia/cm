<?php

class CM_Elasticsearch_Document {

    /** @var string|null */
    protected $_id = null;

    /**
     * Document data
     *
     * @var array Document data
     */
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
     * @param string $key
     * @return mixed
     */
    public function __get($key) {
        return $this->get($key);
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function __set($key, $value) {
        $this->set($key, $value);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function __isset($key) {
        return $this->has($key) && null !== $this->get($key);
    }

    /**
     * @param string $key
     */
    public function __unset($key) {
        $this->remove($key);
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
            throw new CM_Exception_Invalid("Field `{$key}` does not exist");
        }
        return $this->_data[$key];
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @throws CM_Exception_Invalid
     * @return CM_Elasticsearch_Document
     */
    public function set($key, $value) {
        if (!is_array($this->_data)) {
            throw new CM_Exception_Invalid('Document data is serialized data. Data creation is forbidden.');
        }
        $this->_data[$key] = $value;

        return $this;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key) {
        return is_array($this->_data) && array_key_exists($key, $this->_data);
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

        return $this;
    }

    /**
     * Adds the given key/value pair to the document
     *
     * @deprecated
     * @param  string $key   Document entry key
     * @param  mixed  $value Document entry value
     * @return CM_Elasticsearch_Document
     */
    public function add($key, $value) {
        return $this->set($key, $value);
    }

    /**
     * Adds a file to the index
     *
     * To use this feature you have to call the following command in the
     * elasticsearch directory:
     * <code>
     * ./bin/plugin -install elasticsearch/elasticsearch-mapper-attachments/1.6.0
     * </code>
     * This installs the tika file analysis plugin. More infos about supported formats
     * can be found here: {@link http://tika.apache.org/0.7/formats.html}
     *
     * @param  string $key      Key to add the file to
     * @param  string $filepath Path to add the file
     * @param  string $mimeType OPTIONAL Header mime type
     * @return CM_Elasticsearch_Document
     */
    public function addFile($key, $filepath, $mimeType = '') {
        $value = base64_encode(file_get_contents($filepath));

        if (!empty($mimeType)) {
            $value = array('_content_type' => $mimeType, '_name' => $filepath, 'content' => $value,);
        }

        $this->set($key, $value);

        return $this;
    }

    /**
     * Add file content
     *
     * @param  string $key     Document key
     * @param  string $content Raw file content
     * @return CM_Elasticsearch_Document
     */
    public function addFileContent($key, $content) {
        return $this->set($key, base64_encode($content));
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

        return $this;
    }

    /**
     * Overwrites the current document data with the given data
     *
     * @param  array $data Data
     * @return CM_Elasticsearch_Document
     */
    public function setData(array $data) {
        $this->_data = $data;

        return $this;
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
        $doc = $this->getData();

        $id = $this->getId();
        if (null !== $id) {
            $doc['_id'] = (string) $id;
        }
        return $doc;
    }

    /**
     * @param  array $data
     * @return CM_Elasticsearch_Document
     */
    public static function create(array $data) {
        return new self(null, $data);
    }
}
