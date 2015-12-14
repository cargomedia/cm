<?php

function smarty_function_load(array $params, Smarty_Internal_Template $template) {
    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');

    $namespace = isset($params['namespace']) ? $params['namespace'] : null;
    $parse = isset($params['parse']) ? (bool) $params['parse'] : true;
    $needed = isset($params['needed']) ? (bool) $params['needed'] : true;

    if ($parse) {
        $tplPath = $render->getLayoutPath($params['file'], $namespace, null, null, $needed);
        if (null === $tplPath) {
            return '';
        }
        $params = array_merge($template->getTemplateVars(), $params);
        return $render->fetchTemplate($tplPath, $params);
    } else {
        $file = new CM_File($render->getLayoutPath($params['file'], $namespace, null, true, $needed));
        if (null === $file) {
            return '';
        }
        return $file->read();
    }
}
