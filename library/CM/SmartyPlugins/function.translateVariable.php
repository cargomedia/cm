<?php

function smarty_function_translateVariable($params, Smarty_Internal_Template $template) {
    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');

    $key = $params['key'];
    unset($params['key']);

    if ($key instanceof CM_Translate_Phrase) {
        return $key->translate($render);
    } else {
        return $render->getTranslation($key, $params);
    }
}
