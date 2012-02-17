<?php

function smarty_modifier_timeago($stamp) {
	$seconds = time() - $stamp;
	$minutes = $seconds / 60;
	$hours = $minutes / 60;
	$days = $hours / 24;
	$years = $days / 365;

	$langSection = CM_Language::section('date.timeago');
	if ($seconds >= 0) {
		$suffix = $langSection->text('suffixAgo');
	} else {
		$suffix = $langSection->text('suffixFromNow');
	}

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

	$print = $print . ' ' . $suffix;

	$date = new DateTime('@' . $stamp);
	$iso8601 = $date->format('c');
	return '<abbr title="' . $iso8601 . '" class="timeago">' . $print . '</abbr>';
}
