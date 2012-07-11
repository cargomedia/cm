<?php

function smarty_compiler_translateStatic($params, Smarty $smarty) {
	/** @var CM_Render $render */
	$render = $smarty->getTemplateVars('render');

	$key = substr($params['key'], 1, -1);

	return $render->getTranslation($key);
}
