<?php
/**
 * @param array                    $params
 * @param Smarty_Internal_Template $template
 * @return bool
 */
function smarty_function_advertisement(array $params, Smarty_Internal_Template $template) {
	if (!isset($params['zone'])) {
		trigger_error('Param `zone` missing.');
	}
	return '<div class="advertisement">' . CM_Adprovider::getInstance()->getHtml($params['zone']) . '</div>';
}
