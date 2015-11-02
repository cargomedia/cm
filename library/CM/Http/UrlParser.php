<?php

class CM_Http_UrlParser {

    /** @var string */
    private $_url;

    /**
     * @param string $url
     */
    public function __construct($url) {
        $this->_url = (string) $url;
    }

    /**
     * @return string
     * @throws CM_Exception
     */
    public function getHost() {
        $host = parse_url($this->_url, PHP_URL_HOST);
        if (false === $host || null === $host) {
            throw new CM_Exception('Cannot detect host from `' . $this->_url . '`.');
        }
        return $host;
    }
}
