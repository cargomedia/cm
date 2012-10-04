<?php

require_once 'geshi/geshi.php';

function smarty_block_code($params, $content, Smarty_Internal_Template $template, $open) {

	if ($open) {
		return '';
	} else {
		$language = isset($params['language']) ? (string) $params['language'] : null;

		$classes = array();
		if ($language) {
			$classes[] = $language;
		}

		if (!empty($params['class'])) {
			$classes[] = $params['class'];
		}
		$content = trim($content, "\n\r");
		$content = rtrim($content);
		$rows = preg_split("#[\n\r]#", $content);
		preg_match('#^\s+#', reset($rows), $matches);
		if ($matches) {
			$whitespace = $matches[0];
			foreach ($rows as &$row) {
				$row = preg_replace('#^' . $whitespace . '#', '', $row);
			}
		}
		$content = implode(PHP_EOL, $rows);
		$content = trim($content, "\n\r");

		$geshi = new GeSHi($content, $language);
		$geshi->keyword_links = false;
		$geshi->set_tab_width(4);
		$geshi->set_header_type(GESHI_HEADER_NONE);

		return '<code class="' . implode(' ', $classes) . '">' . $geshi->parse_code() . '</code>';
	}
}
