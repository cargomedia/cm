<?php

function smarty_function_component(array $params, Smarty_Internal_Template $template) {
    if (empty($params['name'])) {
        trigger_error('Param `name` missing.');
    }
    $name = $params['name'];
    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');
    unset($params['name']);

    if ($name instanceof CM_Component_Abstract) {
        if (count($params)) {
            throw new CM_Exception_Invalid('Cannot set any params when passing component directly');
        }
        $component = $name;
    } else {
        $component = CM_Component_Abstract::factory($name, $params);
    }

    if ($component instanceof CM_Page_Abstract) {
        $renderAdapter = new CM_RenderAdapter_Page($render, $component);
    } else {
        $renderAdapter = new CM_RenderAdapter_Component($render, $component);
    }
    return $renderAdapter->fetch();
}
