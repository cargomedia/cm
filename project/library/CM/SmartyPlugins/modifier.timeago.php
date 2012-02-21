<?php

/**
 * Based on and compatible with http://timeago.yarp.com/
 *
 * @param int $stamp
 * @return string
 */
function smarty_modifier_timeago($stamp) {
	$distance = time() - $stamp;

	$langSection = CM_Language::section('date.timeago');
	if ($distance >= 0) {
		$prefix = $langSection->text('prefixAgo');
		$suffix = $langSection->text('suffixAgo');
	} else {
		$prefix = $langSection->text('prefixFromNow');
		$suffix = $langSection->text('suffixFromNow');
	}

	$seconds = abs($distance);
	$minutes = $seconds / 60;
	$hours = $minutes / 60;
	$days = $hours / 24;
	$years = $days / 365;

	if ($seconds < 45) {
		$print = $langSection->text('seconds', array('count' => round($seconds)));
	} elseif ($seconds < 90) {
		$print = $langSection->text('minute', array('count' => 1));
	} elseif ($minutes < 45) {
		$print = $langSection->text('minutes', array('count' => round($minutes)));
	} elseif ($minutes < 90) {
		$print = $langSection->text('hour', array('count' => 1));
	} elseif ($hours < 24) {
		$print = $langSection->text('hours', array('count' => round($hours)));
	} elseif ($hours < 48) {
		$print = $langSection->text('day', array('count' => 1));
	} elseif ($days < 30) {
		$print = $langSection->text('days', array('count' => floor($days)));
	} elseif ($days < 60) {
		$print = $langSection->text('month', array('count' => 1));
	} elseif ($days < 365) {
		$print = $langSection->text('months', array('count' => floor($days / 30)));
	} elseif ($years < 2) {
		$print = $langSection->text('year', array('count' => 1));
	} else {
		$print = $langSection->text('years', array('count' => floor($years)));
	}

	$print = trim(implode(' ', array($prefix, $print, $suffix)));

	$date = new DateTime('@' . $stamp);
	$iso8601 = $date->format('c');
	return '<abbr title="' . $iso8601 . '" class="timeago">' . $print . '</abbr>';
}
