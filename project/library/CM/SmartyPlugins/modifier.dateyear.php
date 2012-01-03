<?php

function smarty_modifier_dateyear($stamp) {
	$date = new DateTime('@' . $stamp);
	return $date->format('Y');
}
