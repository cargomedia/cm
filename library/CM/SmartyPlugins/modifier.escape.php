<?php

function smarty_modifier_escape($string, $char_set = 'UTF-8') {
	return htmlspecialchars($string, ENT_QUOTES, $char_set);
}

