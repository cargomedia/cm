<?php

function smarty_compiler_translate($params, Smarty $smarty) {
	/** @var CM_Render $render */
	$render = $smarty->getTemplateVars('render');

	$phrase = substr($params['key'], 1, -1);
	unset($params['key']);

	return $render->getText($phrase, $params);
}
