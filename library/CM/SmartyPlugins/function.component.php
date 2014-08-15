<?php

function smarty_function_component(array $params, Smarty_Internal_Template $template) {

    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');

    if (empty($params['name'])) {
        trigger_error('Param `name` missing.');
    }
    $classname = null;
    $name = $params['name'];
    unset($params['name']);

    if (class_exists($name)) {
        $classname = $name;
    } else {
        if (0 === strpos($name, '*')) {
            $name = str_replace('*_', '', $name);
            foreach ($render->getSite()->getModules() as $availableNamespace) {
                $classString = $availableNamespace . '_' . $name;
                if (class_exists($classString)) {
                    $classname = $classString;
                    break;
                }
            }
            if (null === $classname) {
                throw new CM_Exception_Invalid('The class was not found in any namespace.', array('name' => $name));
            }
        }
    }

    $component = CM_Component_Abstract::factory($classname, $params);
    $renderAdapter = CM_RenderAdapter_Component::factory($render, $component);
    return $renderAdapter->fetch();
}
