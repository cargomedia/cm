<?php

function smarty_function_component(array $params, Smarty_Internal_Template $template) {
    if (empty($params['name'])) {
        trigger_error('Param `name` missing.');
    }
    $name = $params['name'];
    /** @var CM_Render $render */
    $render = $template->smarty->getTemplateVars('render');
    unset($params['name']);
    if ($name instanceof CM_Component_Abstract) {
        $component = $name;
    } else {
        $componentParams = CM_Params::factory($params);
        $component = CM_Component_Abstract::factory($name, $componentParams, $render->getViewer());
        $component->checkAccessible($render);
        $component->prepare($componentParams);
    }

    $renderAdapter = new CM_RenderAdapter_Component($render, $component);
    return $renderAdapter->fetch($params);
}
