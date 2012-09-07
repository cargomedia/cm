<?php

function smarty_function_id(array $params, Smarty_Internal_Template $template) {
	/** @var CM_Render $render */
	$render = $template->smarty->getTemplateVars('render');

	if (empty($params['tag'])) {
		trigger_error('Param `name` is missing.');
	}

	$tag = $params['tag'];

	return $render->getStackLast('views')->getTagAutoId($tag);
}
