<?php

/**
 * Based on and compatible with http://timeago.yarp.com/
 *
 * @param array                    $params
 * @param Smarty_Internal_Template $template
 * @return string
 */
function smarty_function_date_timeago($params, Smarty_Internal_Template $template) {
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');
	$stamp = (int) $params['time'];
	$distance = time() - $stamp;
	$class = 'timeago';
	if (isset($params['class'])) {
		$class .= ' ' . $params['class'];
	}

	if ($distance >= 0) {
		$prefix = $render->getTranslation('.date.timeago.prefixAgo');
		$suffix = $render->getTranslation('.date.timeago.suffixAgo');
	} else {
		$prefix = $render->getTranslation('.date.timeago.prefixFromNow');
		$suffix = $render->getTranslation('.date.timeago.suffixFromNow');
	}

	$seconds = abs($distance);
	$minutes = $seconds / 60;
	$hours = $minutes / 60;
	$days = $hours / 24;
	$years = $days / 365;

	if ($seconds < 45) {
		$print = $render->getTranslation('.date.timeago.seconds', array('count' => round($seconds)));
	} elseif ($seconds < 90) {
		$print = $render->getTranslation('.date.timeago.minute', array('count' => 1));
	} elseif ($minutes < 45) {
		$print = $render->getTranslation('.date.timeago.minutes', array('count' => round($minutes)));
	} elseif ($minutes < 90) {
		$print = $render->getTranslation('.date.timeago.hour', array('count' => 1));
	} elseif ($hours < 24) {
		$print = $render->getTranslation('.date.timeago.hours', array('count' => round($hours)));
	} elseif ($hours < 48) {
		$print = $render->getTranslation('.date.timeago.day', array('count' => 1));
	} elseif ($days < 30) {
		$print = $render->getTranslation('.date.timeago.days', array('count' => floor($days)));
	} elseif ($days < 60) {
		$print = $render->getTranslation('.date.timeago.month', array('count' => 1));
	} elseif ($days < 365) {
		$print = $render->getTranslation('.date.timeago.months', array('count' => floor($days / 30)));
	} elseif ($years < 2) {
		$print = $render->getTranslation('.date.timeago.year', array('count' => 1));
	} else {
		$print = $render->getTranslation('.date.timeago.years', array('count' => floor($years)));
	}

	$print = trim($prefix . ' ' . $print . ' ' . $suffix);
	$date = new DateTime('@' . $stamp);
	$iso8601 = $date->format('c');
	return '<time datetime="' . $iso8601 . '" class="' . $class . '">' . $print . '</time>';
}
