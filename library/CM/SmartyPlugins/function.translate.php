<?php

function smarty_function_translate($params, Smarty_Internal_Template $template) {
    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');

    $key = $params['key'];
    unset($params['key']);

    if ($key instanceof CM_I18n_Phrase) {
        if (!empty($params)) {
            throw new InvalidArgumentException('Passed params will be ignored as you provided CM_I18n_Phrase object');
        }
        return $key->translate($render);
    } else {
        return $render->getTranslation($key, $params);
    }
}
