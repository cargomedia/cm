<?php
require_once 'function.linkUrl.php';
require_once 'function.tag.php';
require_once 'function.icon.php';

function smarty_function_link(array $params, Smarty_Internal_Template $template) {
    $label = '';
    if (isset($params['label'])) {
        $label = $params['label'];
    }
    unset($params['label']);

    $class = 'link';
    if (isset($params['class'])) {
        $class .= ' ' . $params['class'];
    }
    unset($params['class']);

    $title = null;
    if (isset($params['title'])) {
        $title = $params['title'];
    }
    unset($params['title']);

    $icon = null;
    if (isset($params['icon'])) {
        $icon = $params['icon'];
    }
    unset($params['icon']);

    $iconPosition = 'left';
    if (isset($params['iconPosition']) && $params['iconPosition'] === 'right') {
        $iconPosition = 'right';
    }
    unset($params['iconPosition']);

    $data = array();
    if (isset($params['data'])) {
        $data = (array) $params['data'];
    }
    unset($params['data']);

    $onclick = null;
    if (isset($params['onclick'])) {
        $onclick = $params['onclick'];
    }
    unset($params['onclick']);

    $href = 'javascript:;';
    if (isset($params['href'])) {
        $href = (string) $params['href'];
    }
    unset($params['href']);

    $target = null;
    if (isset($params['target'])) {
        $target = (string) $params['target'];
    }
    unset($params['target']);

    if (isset($params['page'])) {
        $href = smarty_function_linkUrl($params, $template);
    }

    if (empty($label) && empty($icon) && empty($title) && (0 !== strpos($href, 'javascript:'))) {
        $label = $href;
    }

    $iconMarkup = null;
    if (null !== $icon) {
        $iconMarkup = smarty_function_icon(['icon' => $icon], $template);
    }

    $html = '';

    if (null !== $iconMarkup && 'left' === $iconPosition) {
        $html .= $iconMarkup;
        $class .= ' hasIcon';
    }
    if (!empty($label)) {
        $html .= '<span class="label">' . CM_Util::htmlspecialchars($label) . '</span>';
        $class .= ' hasLabel';
    }
    if (null !== $iconMarkup && 'right' === $iconPosition) {
        $html .= $iconMarkup;
        $class .= ' hasIconRight';
    }

    $attributeList = [
        'el'      => 'a',
        'content' => $html,
        'href'    => $href,
        'class'   => $class,
        'title'   => $title,
        'onclick' => $onclick,
        'target'  => $target,
    ];

    foreach ($data as $name => $value) {
        $attributeList['data-' . $name] = $value;
    }

    return smarty_function_tag($attributeList, $template);
}
