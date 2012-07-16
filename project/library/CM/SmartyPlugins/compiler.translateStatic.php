<?php

function smarty_compiler_translateStatic($params, Smarty $smarty) {
	/** @var CM_Render $render */
	$render = $smarty->getTemplateVars('render');
	$key = eval('return ' . $params['key'] . ';');

	return $render->getTranslation($key);
}
