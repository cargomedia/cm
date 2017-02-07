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
            throw new CM_Exception('Cannot detect host from url.', null, ['url' => $this->_url]);
        }
        return $host;
    }

    /**
     * @return string
     * @throws CM_Exception
     */
    public function getScheme() {
        $scheme = parse_url($this->_url, PHP_URL_SCHEME);
        if (false === $scheme || null === $scheme) {
            throw new CM_Exception('Cannot detect scheme from url.', null, ['url' => $this->_url]);
        }
        return $scheme;
    }

    /**
     * @return string
     * @throws CM_Exception
     */
    public function getPath() {
        $path = parse_url($this->_url, PHP_URL_PATH);
        if (null === $path) {
            return '/';
        }
        if (false === $path) {
            throw new CM_Exception('Cannot detect path from url.', null, ['url' => $this->_url]);
        }
        return $path;
    }
}
