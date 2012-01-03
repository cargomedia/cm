<?php

function smarty_modifier_date_period($seconds) {
	if (($seconds / (31 * 86400)) >= 1) {
		$count = floor($seconds / (31 * 86400));
		$item = 'mo';
	} elseif (($seconds / (7 * 86400)) >= 1) {
		$count = floor($seconds / (7 * 86400));
		$item = 'w';
	} elseif (($seconds / 86400) >= 1) {
		$count = floor($seconds / 86400);
		$item = 'd';
	} elseif (($seconds / 3600) >= 1) {
		$count = floor($seconds / 3600);
		$item = 'h';
	} elseif (($seconds / 60) >= 1) {
		$count = floor($seconds / 60);
		$item = 'm';
	} else {
		$count = 1;
		$item = 'm';
	}

	$lang_key = ($count > 1) ? 'i18n.date.period_' . $item : 'i18n.date.period_one_' . $item;
	$return = $count . ' ' . CM_Language::text($lang_key);
	return $return;
}
