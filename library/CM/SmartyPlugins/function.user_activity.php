<?php
require_once 'function.date_timeago.php';

function smarty_function_user_activity(array $params, Smarty_Internal_Template $template) {
    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');
    $forceDisplay = isset($params['force_display']);
    /** @var CM_Model_User $user */
    $user = $params['user'];

    $activityStamp = $user->getLatestActivity();
    if (null === $activityStamp) {
        return '';
    }
    $activityDelta = time() - $activityStamp;
    if (!$forceDisplay && $activityDelta > 14 * 86400) {
        return '';
    }

    $class = 'user-activity ';

    if ($user->getVisible()) {
        $class .= 'online ';
    }

    $html = '<span class="' . $class . '">';

    if ($user->getVisible() || $forceDisplay) {
        $html .= $render->getTranslation('Online');
    }

    if (!$user->getVisible()) {
        if ($forceDisplay) {
            $html .= ': ' . smarty_function_date_timeago(array('time' => $activityStamp), $template);
        } else {
            $html .= 'Recently online';
        }
    }

    $html .= '</span>';

    return $html;
}
