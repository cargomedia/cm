<?php

function smarty_modifier_timeago($stamp) {
	$date = new DateTime('@' . $stamp);
	$print = $date->format('Y-m-d H:i (O)');
	$iso8601 = $date->format('c');
	return '<abbr title="' . $iso8601 . '" class="timeago">' . $print . '</abbr>';
}
