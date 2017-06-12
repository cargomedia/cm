<?php

require_once 'function.tag.php';

function smarty_function_checkbox(array $params, Smarty_Internal_Template $template) {
    $display = isset($params['display']) ? (string) $params['display'] : null;
    $checked = isset($params['checked']) ? (bool) $params['checked'] : false;
    $id = isset($params['id']) ? (string) $params['id'] : null;
    $label = (string) $params['label'];
    $name = isset($params['name']) ? (string) $params['name'] : null;
    $tabindex = isset($params['tabindex']) ? (int) $params['tabindex'] : null;
    $value = isset($params['value']) ? (string) $params['value'] : null;
    $buttonTheme = isset($params['buttonTheme']) ? (string) $params['buttonTheme'] : null;
    $buttonIcon = isset($params['buttonIcon']) ? (string) $params['buttonIcon'] : null;

    $classList = [];
    if (isset($params['class'])) {
        $classList[] = (string) $params['class'];
    }

    switch ($display) {
        case CM_FormField_Boolean::DISPLAY_SWITCH:
            $classList[] = 'checkbox-switch';
            break;
        case CM_FormField_Boolean::DISPLAY_BUTTON:
            $classList[] = 'checkbox-button';
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
    if ($display === CM_FormField_Boolean::DISPLAY_BUTTON) {
        $htmlLabelContent .= smarty_function_button_link([
            'label' => $label,
            'theme' => $buttonTheme,
            'icon'  => $buttonIcon,
            'plain' => true,
        ], $template);
    } else {
        if ($display === CM_FormField_Boolean::DISPLAY_SWITCH) {
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
    }

    $html .= smarty_function_tag([
        'el'      => 'label',
        'content' => $htmlLabelContent,
        'for'     => $id,
    ], $template);

    return $html;
}
