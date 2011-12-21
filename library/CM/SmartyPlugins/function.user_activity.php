<?php

function smarty_function_user_activity(array $params, Smarty_Internal_Template $template) {
	$force_display = isset($params['force_display']);
	/** @var CM_Model_User $user  */
	$user = $params['user'];

	if ($user->getVisible()) {
		/** @var CM_Model_User $viewer  */
		return '<span class="online">Online</span>';
	}

	$activity_stamp = $user->getLatestactivity();
	$activity_delta = time() - $activity_stamp;
	if (!$force_display && $activity_delta > 7 * 86400) {
		return '';
	}

	if (($activity_delta / 86400) >= 1) {
		$count = floor($activity_delta / 86400);
		$unit = 'd';
	} elseif (($activity_delta / 3600) >= 1) {
		$count = floor($activity_delta / 3600);
		$unit = 'h';
	} elseif (($activity_delta / 60) >= 1) {
		$count = floor($activity_delta / 60);
		$unit = 'm';
	} else {
		$count = 1;
		$unit = 'm';
	}
	return 'Online: ' . $count . '&nbsp;' . CM_Language::section('profile.labels')->text('activity_' . $unit);

}
