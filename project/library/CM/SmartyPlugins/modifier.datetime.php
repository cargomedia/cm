<?php

function smarty_modifier_datetime($stamp) {
	$date = new DateTime('@' . $stamp);
	return $date->format('M d, Y - h:i');
}
