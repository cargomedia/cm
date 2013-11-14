<?php

function smarty_function_tag(array $params, Smarty_Internal_Template $template) {
	if (!isset($params['el'])) {
		trigger_error('Param `el` missing.');
	}
	$name = $params['el'];
	unset($params['el']);

	$content = '';
	if (isset($params['content'])) {
		$content = (string) $params['content'];
		unset($params['content']);
	}

	$attributes = $params;

	// http://www.w3.org/TR/html-markup/syntax.html#void-element
	$namesVoid = array('area', 'base', 'br', 'col', 'command', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'meta', 'param', 'source', 'track',
		'wbr');

	$html = '<' . $name;
	foreach ($attributes as $attributeName => $attributeValue) {
		if (isset($attributeValue)) {
			$html .= ' ' . $attributeName . '="' . CM_Util::htmlspecialchars($attributeValue) . '"';
		}
	}
	if (in_array($name, $namesVoid)) {
		$html .= ' />';
	} else {
		$html .= '>' . $content . '</' . $name . '>';
	}
	return $html;
}

