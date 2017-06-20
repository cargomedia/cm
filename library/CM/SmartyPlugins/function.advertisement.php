<?php
/**
 * @param array                    $params
 * @param Smarty_Internal_Template $template
 * @return string
 */
function smarty_function_advertisement(array $params, Smarty_Internal_Template $template) {
    if (!isset($params['zone'])) {
        trigger_error('Param `zone` missing.');
    }
    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');
    $variables = isset($params['variables']) ? $params['variables'] : null;
    return '<div class="Adv3rt153m3nt">' . CM_Adprovider::getInstance()->getHtml($render->getSite(), $params['zone'], $variables) . '</div>';
}
