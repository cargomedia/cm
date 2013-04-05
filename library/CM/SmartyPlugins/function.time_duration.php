<?php

function smarty_function_time_duration(array $params, Smarty_Internal_Template $template) {

	$seconds = (int) $params['duration'];
	$format = 'i:s';

	if (($seconds / 3600) >= 1) {
		$format = 'H:i:s';
	}

	return '<span class="time-duration">' . gmdate($format, $seconds) . '</span>';
}
