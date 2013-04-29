<?php

class CMService_Gravatar extends CM_Class_Abstract {

	/**
	 * @param string|null $email
	 * @param int|null    $size
	 * @param string|null $default
	 * @return string
	 */
	public function getUrl($email, $size = null, $default = null) {
		if (null !== $email) {
			$email = (string) $email;
		}
		if (null !== $size) {
			$size = (int) $size;
		}
		if (null !== $default) {
			$default = (string) $default;
		}
		if ((null === $email) && (null !== $default)) {
			return $default;
		}
		$url = 'https://secure.gravatar.com/avatar';
		if (null !== $email) {
			$url .= '/' . md5(strtolower(trim($email)));
		}
		$params = array();
		if (null !== $size) {
			$params['s'] = $size;
		}
		if (null !== $default) {
			$params['d'] = $default;
		}
		return CM_Util::link($url, $params);
	}
}
