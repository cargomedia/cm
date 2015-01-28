<?php
require_once 'function.date_timeago.php';

function smarty_function_user_activity(array $params, Smarty_Internal_Template $template) {
    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');
    $forceDisplay = isset($params['force_display']);
    /** @var CM_Model_User $user */
    $user = $params['user'];

    $activityStamp = $user->getLatestActivity();
    $activityDelta = time() - $activityStamp;
    if (!$forceDisplay && $activityDelta > 10 * 86400) {
        return '';
    }

    $class = 'user-activity ';

    if ($user->getVisible()) {
        $class .= 'online ';
    }

    $html = '<span class="' . $class . '">';
    $html .= $render->getTranslation('Online');

    if (!$user->getVisible()) {
        $html .= ': ' . smarty_function_date_timeago(array('time' => $activityStamp), $template);
    }

    $html .= '</span>';

    return $html;
}
