<?php
require_once 'function.resourceFileContent.php';

function smarty_function_icon(array $params, Smarty_Internal_Template $template) {
    if (!isset($params['icon'])) {
        throw new CM_Exception_Invalid('Param `icon` missing');
    }

    $iconUrl = 'img/icon/' . $params['icon'] . '.svg';
    unset($params['icon']);

    return smarty_function_resourceFileContent(['path' => $iconUrl], $template);
}
