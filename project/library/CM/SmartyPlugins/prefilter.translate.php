<?php

function smarty_prefilter_translate($source, Smarty_Internal_Template $template) {
	$source = preg_replace('#{translate(\s+"[^"]*[\$]+[^"]*"|\s+\$)#', '{translateVariable$1', $source);
	$source = preg_replace('#{translate(Variable)?\s+#', '$0 key=', $source);
	return $source;
}
