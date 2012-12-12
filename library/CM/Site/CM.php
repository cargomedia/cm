<?php

class CM_Site_CM extends CM_Site_Abstract {
	const TYPE = 1;

	public static function match(CM_Request_Abstract $request) {
		// @todo: Remove this class
		$isNotCmProject = false === strpos(DIR_ROOT, 'www') && false === strpos(DIR_ROOT, '/home/fuckbook/releases');
		return IS_TEST && $isNotCmProject;
	}
}
