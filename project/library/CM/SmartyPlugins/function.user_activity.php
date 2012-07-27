<?php

function smarty_function_user_activity(array $params, Smarty_Internal_Template $template) {
	$forceDisplay = isset($params['force_display']);
	/** @var CM_Model_User $user  */
	$user = $params['user'];

	if ($user->getVisible()) {
		return '<span class="online">Online</span>';
	}

	$activityStamp = $user->getLatestactivity();
	$activityDelta = time() - $activityStamp;
	if (!$forceDisplay && $activityDelta > 10 * 86400) {
		return '';
	}

	return 'Online: ' . smarty_function_date_timeago(array('time' => $activityStamp), $template);

}