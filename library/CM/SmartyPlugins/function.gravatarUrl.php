<?php

function smarty_function_gravatarUrl(array $params) {
	$email = $params['email'];
	$size = isset($params['size']) ? $params['size'] : null;
	$default = isset($params['default']) ? $params['default'] : null;

	$gravatar = new CMService_Gravatar();
	return $gravatar->getUrl($email, $size, $default);
}
