<?php

function smarty_function_component(array $params, Smarty_Internal_Template $template) {

    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');

    if (empty($params['name'])) {
        trigger_error('Param `name` missing.');
    }
    $classname = null;
    $name = $params['name'];
    $namespace = !empty($params['namespace']) ? $params['namespace'] : null;
    $availableNamespaceList = $render->getSite()->getModules();

    if (class_exists($name)) {
        $classname = $name;
    } else {
        if ($namespace) {
            if (!in_array($namespace, $availableNamespaceList)) {
                throw new CM_Exception_Invalid('Given namespace is not available.', array(
                    'namespace'           => $namespace,
                    'availableNamespaces' => $availableNamespaceList,
                ));
            }
            $classname = $namespace . '_' . $name;
        } else {
            foreach ($availableNamespaceList as $availableNamespace) {
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

    unset($params['name']);
    unset($params['namespace']);

    $component = CM_Component_Abstract::factory($classname, $params);
    $renderAdapter = CM_RenderAdapter_Component::factory($render, $component);
    return $renderAdapter->fetch();
}
