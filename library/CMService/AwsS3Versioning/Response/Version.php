<?php

class CMService_AwsS3Versioning_Response_Version {

    /** @var string */
    private $_id;

    /** @var string */
    private $_key;

    /** @var DateTime */
    private $_lastModified;

    /** @var int|null */
    private $_size;

    /** @var string|null */
    private $_eTag;

    /** @var bool */
    private $_isLatest;

    /**
     * @param array $data
     */
    public function __construct(array $data) {
        $this->_key = (string) $data['Key'];
        $this->_id = (string) $data['VersionId'];
        $this->_lastModified = new DateTime($data['LastModified']);
        $this->_size = isset($data['Size']) ? (int) $data['Size'] : null;
        $this->_eTag = isset($data['ETag']) ? (string) $data['ETag'] : null;
        $this->_isLatest = (bool) $data['IsLatest'];
    }

    /**
     * @return string
     */
    public function getId() {
        return $this->_id;
    }

    /**
     * @return string
     */
    public function getKey() {
        return $this->_key;
    }

    /**
     * @return DateTime
     */
    public function getLastModified() {
        return $this->_lastModified;
    }

    /**
     * @return string|null
     */
    public function getETag() {
        return $this->_eTag;
    }

    /**
     * @return int|null
     */
    public function getSize() {
        return $this->_size;
    }

    /**
     * @return bool
     */
    public function getIsLatest() {
        return $this->_isLatest;
    }
}
