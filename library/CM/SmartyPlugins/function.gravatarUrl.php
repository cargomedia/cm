<?php

function smarty_function_gravatarUrl(array $params) {
	$gravatar = new CMService_Gravatar();
	$size = null;
	if (isset($params['size'])) {
		$size = $params['size'];
	}
	$default = null;
	if (isset($params['default'])) {
		$default = $params['default'];
	}
	return $gravatar->getUrl($params['email'], $size, $default);
}
