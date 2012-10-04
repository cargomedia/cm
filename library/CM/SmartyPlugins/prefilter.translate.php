<?php

function smarty_prefilter_translate($source, Smarty_Internal_Template $template) {
	$source = preg_replace('#{translate\s+([^}${=]+})#', '{translateStatic key=$1', $source);
	$source = preg_replace('#{translate\s+#', '{translateVariable key=', $source);
	return $source;
}
