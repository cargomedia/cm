<?php

require_once 'function.tag.php';

function smarty_function_checkbox(array $params, Smarty_Internal_Template $template) {
    $isSwitch = isset($params['isSwitch']) ? (bool) $params['isSwitch'] : false;
    $checked = isset($params['checked']) ? (bool) $params['checked'] : false;
    $id = isset($params['id']) ? (string) $params['id'] : null;
    $label = (string) $params['label'];
    $name = isset($params['name']) ? (string) $params['name'] : null;
    $tabindex = isset($params['tabindex']) ? (int) $params['tabindex'] : null;
    $value = isset($params['value']) ? (string) $params['value'] : null;

    $classList = [];
    if (isset($params['class'])) {
        $classList[] = (string) $params['class'];
    }
    if ($isSwitch) {
        $classList[] = 'checkbox-switch';
    }

    $data = [];
    if (isset($params['data'])) {
        $data = (array) $params['data'];
        unset($params['data']);
    }

    if (null === $id) {
        $id = uniqid();
    }

    $attributeList = [
        'el'       => 'input',
        'type'     => 'checkbox',
        'id'       => $id,
        'class'    => !empty($classList) ? implode(' ', $classList) : null,
        'checked'  => $checked ? 'checked' : null,
        'name'     => $name,
        'tabindex' => $tabindex,
        'value'    => $value,
    ];
    if (!empty($data)) {
        foreach ($data as $name => $value) {
            $attributeList['data-' . $name] = CM_Util::htmlspecialchars($value);
        }
    }
    $html = smarty_function_tag($attributeList, $template);

    $htmlLabelContent = '';
    if ($isSwitch) {
        $htmlLabelContent .= smarty_function_tag([
            'el'    => 'span',
            'class' => 'handle',
        ], $template);
    }
    $htmlLabelContent .= smarty_function_tag([
        'el'      => 'span',
        'content' => $label,
        'class'   => 'label',
    ], $template);

    $html .= smarty_function_tag([
        'el'      => 'label',
        'content' => $htmlLabelContent,
        'for'     => $id,
    ], $template);

    return $html;
}
