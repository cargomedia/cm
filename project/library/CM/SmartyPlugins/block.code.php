<?php

function smarty_block_code($params, $content, Smarty_Internal_Template $template, $open) {

	if ($open) {
		array_unshift(CM_Render::$block_stack, array());
		return '';
	} else {
		$attributes = '';
		if (!empty($params['language'])) {
			$language = (string) $params['language'];
			$attributes .= 'class="' . $language . '-syntax"';
		}


		return '<code ' . $attributes . '>' . htmlspecialchars(trim($content, "\n\r")) . '</code>';
	}
}

