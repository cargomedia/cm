<?php

class CM_Request_Post extends CM_Request_Abstract {
	const FORMAT_JSON = 1;
	const FORMAT_FORM = 2;

	private $_body;

	/**
	 * @param string $uri
	 * @param array $headers
	 * @param string $body
	 * @param int|false $bodyQueryFormat OPTIONAL
	 */
	public function __construct($uri, array $headers, $body, $bodyQueryFormat = self::FORMAT_JSON) {
		parent::__construct($uri, $headers);
		$this->_body = (string) $body;

		if ($bodyQueryFormat == self::FORMAT_JSON) {
			if (!is_array($bodyQuery = json_decode($this->getBody(), true))) {
				throw new CM_Exception_Invalid('Cannot extract query from body `' . $this->getBody() . '`.');
			}
			$this->_query = array_merge($this->_query, $bodyQuery);
		}

		if ($bodyQueryFormat == self::FORMAT_FORM) {
			parse_str($this->getBody(), $bodyQuery);
			$this->_query = array_merge($this->_query, $bodyQuery);
		}
	}

	/**
	 * @return string
	 */
	public function getBody() {
		return $this->_body;
	}

}
