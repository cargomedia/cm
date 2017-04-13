<?php
require_once 'function.icon.php';

function smarty_function_button(array $params, Smarty_Internal_Template $template) {
    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');
    $viewResponse = $render->getGlobalResponse()->getClosestViewResponse('CM_Form_Abstract');
    if (null === $viewResponse) {
        throw new CM_Exception_Invalid('Cannot find parent `CM_Form_Abstract` view response. {button} can be only rendered within form view.');
    }
    /** @var CM_Form_Abstract $form */
    $form = $viewResponse->getView();
    if (empty($params['action'])) {
        trigger_error('Param `action` missing.');
    }
    $action = $form->getAction($params['action']);
    $title = isset($params['title']) ? (string) $params['title'] : null;
    $theme = isset($params['theme']) ? (string) $params['theme'] : 'default';
    $isHtmlLabel = isset($params['isHtmlLabel']) ? (bool) $params['isHtmlLabel'] : false;

    $class = 'button ' . 'button-' . $theme . ' clickFeedback';
    if (isset($params['class'])) {
        $class .= ' ' . trim($params['class']);
    }

    $data = array();
    if (isset($params['data'])) {
        $data = $params['data'];
        unset($params['data']);
    }

    if (isset($params['event'])) {
        $data['event'] = (string) $params['event'];
        unset($params['event']);
    }

    $icon = null;
    $iconConfirm = null;
    if (isset($params['icon'])) {
        $icon = $params['icon'];

        if (isset($params['iconConfirm'])) {
            $iconConfirm = $params['iconConfirm'];
        }
    }

    $label = '';
    if (isset($params['label'])) {
        $label = ($isHtmlLabel) ? $params['label'] : CM_Util::htmlspecialchars($params['label']);
    }

    if ($label) {
        $class .= ' hasLabel';
    }
    if ($icon) {
        $class .= ' hasIcon';
    }

    $id = $viewResponse->getAutoId() . '-' . $action->getName() . '-button';

    $html = '';
    $html .= '<button class="' . $class . '" id="' . $id . '" type="submit" value="' . $label . '" data-click-spinner="true"';
    if ($title) {
        $html .= ' title="' . $title . '"';
    }
    if (!empty($data)) {
        foreach ($data as $name => $value) {
            $html .= ' data-' . $name . '="' . CM_Util::htmlspecialchars($value) . '"';
        }
    }
    $html .= '>';
    if ($icon) {
        if ($iconConfirm) {
            $html .= '<span class="confirmClick-state-inactive">' . smarty_function_icon(['icon' => $icon], $template) . '</span>'
                . '<span class="confirmClick-state-active">' . smarty_function_icon(['icon' => $iconConfirm], $template) . '</span>';
        } else {
            $html .= smarty_function_icon(['icon' => $icon], $template);
        }
    }
    if ($label) {
        $html .= '<span class="label">' . $label . '</span>';
    }
    $html .= '</button>';
    return $html;
}
