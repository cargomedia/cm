<?php

class CMService_AwsS3Versioning_Response_Version {

    /** @var string */
    private $_id;

    /** @var string */
    private $_key;

    /** @var DateTime */
    private $_lastModified;

    /**
     * @param array $data
     */
    public function __construct(array $data) {
        $this->_key = (string) $data['Key'];
        $this->_id = (string) $data['VersionId'];
        $this->_lastModified = new DateTime($data['LastModified']);
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
}
