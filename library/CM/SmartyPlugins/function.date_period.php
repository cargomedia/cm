<?php

function smarty_function_date_period(array $params, Smarty_Internal_Template $template) {
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');
	$seconds = (int) $params['period'];
	$short = !empty($params['short']);

	$class = 'date-period';
	if (isset($params['class'])) {
		$class .= ' ' . $params['class'];
	}

	if (($seconds / 86400) >= 1) {
		$short = false;
	}

	if ($short) {
		$format = 'i:s';
		if (($seconds / 3600) >= 1) {
			$format = 'H:i:s';
		}

		$text = gmdate($format, $seconds);
	} else {
		if (($seconds / (365 * 86400)) >= 1) {
			$count = floor($seconds / (365 * 86400));
			$periodName = 'year';
		} elseif (($seconds / (30 * 86400)) >= 1) {
			$count = floor($seconds / (30 * 86400));
			$periodName = 'month';
		} elseif (($seconds / (7 * 86400)) >= 1) {
			$count = floor($seconds / (7 * 86400));
			$periodName = 'week';
		} elseif (($seconds / 86400) >= 1) {
			$count = floor($seconds / 86400);
			$periodName = 'day';
		} elseif (($seconds / 3600) >= 1) {
			$count = floor($seconds / 3600);
			$periodName = 'hour';
		} elseif (($seconds / 60) >= 1) {
			$count = floor($seconds / 60);
			$periodName = 'minute';
		} else {
			$count = 1;
			$periodName = 'minute';
		}

		$translationVariables = array();
		if ($count > 1) {
			$periodName .= 's';
			$translationVariables['count'] = $count;
		}

		$text = $render->getTranslation('.date.period.' . $periodName, $translationVariables);
	}

	return '<span class="' . $class . '">' . $text . '</span>';
}
