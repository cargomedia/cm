<?php

function smarty_function_component(array $params, Smarty_Internal_Template $template) {
    if (empty($params['name'])) {
        trigger_error('Param `name` missing.');
    }
    $name = $params['name'];
    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');
    unset($params['name']);

    $component = CM_Component_Abstract::factory($name, $params);
    $renderAdapter = CM_RenderAdapter_Component::factory($render, $component);
    return $renderAdapter->fetch();
}
