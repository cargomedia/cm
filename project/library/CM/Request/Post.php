<?php

class CM_Request_Post extends CM_Request_Abstract {
	const ENCODING_JSON = 1;
	const ENCODING_FORM = 2;

	private $_body;

	/** @var int|boolean */
	private $_bodyEncoding = self::ENCODING_JSON;

	/**
	 * @param string        $uri
	 * @param array|null    $headers
	 * @param CM_Model_User $viewer
	 * @param string        $body
	 */
	public function __construct($uri, array $headers = null, CM_Model_User $viewer = null, $body) {
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
		if ($this->_bodyEncoding == self::ENCODING_JSON) {
			if (!is_array($bodyQuery = json_decode($this->getBody(), true))) {
				throw new CM_Exception_Invalid('Cannot extract query from body `' . $this->getBody() . '`.');
			}
			return array_merge($this->_query, $bodyQuery);
		}

		if ($this->_bodyEncoding == self::ENCODING_FORM) {
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
