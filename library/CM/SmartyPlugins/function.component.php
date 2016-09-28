<?php

function smarty_function_component(array $params, Smarty_Internal_Template $template) {

    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');

    $component = null;

    if (isset($params['name'])) {
        $name = $params['name'];
        unset($params['name']);
        if (0 === strpos($name, '*_')) {
            $name = $render->getClassnameByPartialClassname(mb_substr($name, 2));
        }
        $component = CM_Component_Abstract::factory($name, $params);
    }

    if (isset($params['view'])) {
        $view = $params['view'];
        unset($params['view']);
        if ($view instanceof CM_Component_Abstract) {
            $component = $view;
        }
    }

    if (!$component) {
        throw new CM_Exception('Missing component, either pass `name` or `view`.');
    }

    $renderAdapter = CM_RenderAdapter_Component::factory($render, $component);
    return $renderAdapter->fetch();
}
