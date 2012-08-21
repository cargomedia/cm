<?php

require_once DIR_LIBRARY . 'geshi/geshi.php';

function smarty_block_code($params, $content, Smarty_Internal_Template $template, $open) {

	if ($open) {
		return '';
	} else {
		$language = isset($params['language']) ? (string) $params['language'] : null;

		$class = '';
		if ($language) {
			$class .= $language;
		}

		$geshi = new GeSHi($content, $language);
		$geshi->set_header_type(GESHI_HEADER_NONE);

		return '<code class="' . $class . '">' . $geshi->parse_code() . '</code>';
	}
}
