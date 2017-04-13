<?php
require_once 'function.icon.php';

function smarty_function_select(array $params, Smarty_Internal_Template $template) {
    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');

    $htmlAttributes = array('id', 'name', 'class');

    $optionList = array();
    if (isset($params['optionList'])) {
        $optionList = $params['optionList'];
    }

    $selectedValue = null;
    if (isset($params['selectedValue']) && isset($optionList[$params['selectedValue']])) {
        $selectedValue = $params['selectedValue'];
    }

    $translate = !empty($params['translate']);

    $translatePrefix = '';
    if (isset($params['translatePrefix'])) {
        $translatePrefix = (string) $params['translatePrefix'];
    }

    $placeholder = null;
    if (isset($params['placeholder']) && is_string($params['placeholder'])) {
        $placeholder = $params['placeholder'];
    } elseif (!empty($params['placeholder'])) {
        $placeholder = ' -' . $render->getTranslation('Select') . '- ';
    }

    $labelPrefix = null;
    if (isset($params['labelPrefix'])) {
        $labelPrefix = (string) $params['labelPrefix'];
    }

    foreach ($optionList as $itemValue => $itemLabel) {
        if ($translate) {
            $optionList[$itemValue] = $render->getTranslation($translatePrefix . $itemLabel, array());
        } else {
            $optionList[$itemValue] = CM_Util::htmlspecialchars($itemLabel);
        }
    }

    if (null !== $placeholder) {
        $optionList = array(null => $placeholder) + $optionList;
    }

    $html = '';
    $html .= '<select';
    foreach ($htmlAttributes as $name) {
        if (isset($params[$name])) {
            $html .= ' ' . $name . '="' . CM_Util::htmlspecialchars($params[$name]) . '"';
        }
    }
    $html .= '>';
    $selectedLabel = '';
    if (null === $selectedValue && count($optionList) > 0) {
        $optionListValues = array_keys($optionList);
        $selectedValue = reset($optionListValues);
    }
    if (null !== $selectedValue) {
        $selectedValue = (string) $selectedValue;
    }
    foreach ($optionList as $itemValue => $itemLabel) {
        if (null !== $itemValue) {
            $itemValue = (string) $itemValue;
        }
        $html .= '<option value="' . CM_Util::htmlspecialchars($itemValue) . '"';
        if ($itemValue === $selectedValue) {
            $html .= ' selected';
            $selectedLabel = $itemLabel;
        }
        $html .= '>' . $itemLabel . '</option>';
    }
    $html .= '</select>';

    $html .= '<div class="fancySelect-select nowrap">';

    if ($labelPrefix) {
        $html .= '<span class="labelPrefix">' . CM_Util::htmlspecialchars($labelPrefix) . '</span>';
    }
    $html .= '<span class="label">' . $selectedLabel . '</span>' . smarty_function_icon(['icon' => 'select'], $template);
    $html .= '</div>';

    return '<div class="fancySelect">' . $html . '</div>';
}
