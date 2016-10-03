<?php

function smarty_function_tag(array $params, Smarty_Internal_Template $template) {
    if (!isset($params['el'])) {
        trigger_error('Param `el` missing.');
    }
    $elementName = $params['el'];
    unset($params['el']);

    $content = '';
    if (isset($params['content'])) {
        $content = (string) $params['content'];
        unset($params['content']);
    }

    $dataHtml = [];
    if (isset($params['data'])) {
        if (!is_array($params['data'])) {
            trigger_error('Param `data` should be an array.');
        }
        $dataHtml = $params['data'];
        unset($params['data']);
    }

    $renderer = new CM_Frontend_HtmlTagRenderer();
    return $renderer->renderTag($elementName, $content, $params, $dataHtml);
}

