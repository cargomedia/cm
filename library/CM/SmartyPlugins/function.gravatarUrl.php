<?php

function smarty_function_gravatarUrl(array $params) {
	return CMService_Gravatar::getUrl($params['email'], $params['size'], $params['default']);
}
