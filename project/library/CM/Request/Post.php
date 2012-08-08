<?php

class CM_Request_Post extends CM_Request_Abstract {
	const FORMAT_JSON = 1;
	const FORMAT_FORM = 2;

	private $_body;

	/** @var int|boolean */
	private $_bodyEncoding = self::FORMAT_JSON;

	/**
	 * @param string     $uri
	 * @param array|null $headers
	 * @param string     $body
	 */
	public function __construct($uri, array $headers = null, $body) {
		parent::__construct($uri, $headers);
		$this->_body = (string) $body;
	}

	/**
	 * @return string
	 */
	public function getBody() {
		return $this->_body;
	}

	public function getQuery() {
		if ($this->_bodyEncoding == self::FORMAT_JSON) {
			if (!is_array($bodyQuery = json_decode($this->getBody(), true))) {
				throw new CM_Exception_Invalid('Cannot extract query from body `' . $this->getBody() . '`.');
			}
			return array_merge($this->_query, $bodyQuery);
		}

		if ($this->_bodyEncoding == self::FORMAT_FORM) {
			parse_str($this->getBody(), $bodyQuery);
			return array_merge($this->_query, $bodyQuery);
		}
		return $this->_query;
	}

	/**
	 * @param int|null $bodyEncoding
	 */
	public function setBodyEncoding($bodyEncoding) {
		$this->_bodyEncoding = $bodyEncoding;
	}
}
