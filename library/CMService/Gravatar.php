<?php

class CMService_Gravatar extends CM_Class_Abstract {

	/**
	 * @param string|null $email
	 * @param int|null    $size
	 * @param string|null $default
	 * @return string
	 */
	public static function getUrl($email, $size = null, $default = null) {
		if (null !== $size) {
			$size = (int) $size;
		}
		if (null !== $default) {
			$default = (string) $default;
		}
		if (empty($email)) {
			return (string) $default;
		}
		$email = (string) $email;
		$url = 'https://secure.gravatar.com/avatar/' . md5(strtolower(trim($email)));
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
