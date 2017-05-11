<?php

class CM_Http_Request_Post extends CM_Http_Request_Abstract {

    const ENCODING_NONE = 1;
    const ENCODING_JSON = 2;
    const ENCODING_FORM = 3;

    /** @var string */
    private $_body;

    /** @var array|null */
    private $_bodyQuery;

    /** @var int */
    private $_bodyEncoding = self::ENCODING_JSON;

    /**
     * @param string      $uri
     * @param array|null  $headers
     * @param array|null  $server
     * @param string|null $body
     */
    public function __construct($uri, array $headers = null, array $server = null, $body = null) {
        parent::__construct($uri, $headers, $server);
        $this->_body = (string) $body;
    }

    /**
     * @return string
     */
    public function getBody() {
        return $this->_body;
    }

    public function getMethodName() {
        return 'POST';
    }

    public function getQuery() {
        if ($this->_bodyQuery === null) {
            if ($this->_bodyEncoding == self::ENCODING_JSON) {
                $body = CM_Util::sanitizeUtf($this->getBody());
                if (!is_array($this->_bodyQuery = json_decode($body, true))) {
                    throw new CM_Exception_Invalid('Cannot extract query from body', CM_Exception::WARN, ['body' => $body]);
                }
            } elseif ($this->_bodyEncoding == self::ENCODING_FORM) {
                parse_str($this->getBody(), $this->_bodyQuery);
            } else {
                $this->_bodyQuery = array();
            }
        }
        return array_merge(parent::getQuery(), $this->_bodyQuery);
    }

    /**
     * @param int $bodyEncoding
     * @throws CM_Exception_Invalid
     */
    public function setBodyEncoding($bodyEncoding) {
        if (!in_array($bodyEncoding, array(self::ENCODING_NONE, self::ENCODING_JSON, self::ENCODING_FORM), true)) {
            throw new CM_Exception_Invalid('Invalid body encoding', null, ['bodyEncoding' => $bodyEncoding]);
        }
        $this->_bodyEncoding = (int) $bodyEncoding;
        $this->_bodyQuery = null;
    }
}
