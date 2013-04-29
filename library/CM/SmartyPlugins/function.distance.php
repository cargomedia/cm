<?php

function smarty_function_distance(array $params, Smarty_Internal_Template $template) {
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');

	$distance = (int) $params['distance'];
	$distanceDisplayMin = 5;

	$locale = $render->getLocale();
	switch (true) {
		case 0 === stripos($locale, 'en'):
			$distanceDisplay = $distance / 1609;
			$unitDisplay = 'mi';
			break;
		default:
			$distanceDisplay = $distance / 1000;
			$unitDisplay = 'km';
	}

	$distanceDisplay = round($distanceDisplay);

	if ($distanceDisplay < $distanceDisplayMin) {
		$distanceDisplay = '<' . $distanceDisplayMin;
	}

	return $distanceDisplay . ' ' . $unitDisplay;
}
