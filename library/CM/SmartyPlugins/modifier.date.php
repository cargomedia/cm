<?php

function smarty_modifier_date($stamp) {
	$date = new DateTime('@' . $stamp);
	return $date->format('M d, Y');
}
