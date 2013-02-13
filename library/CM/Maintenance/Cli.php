<?php

class CM_Maintenance_Cli extends CM_Cli_Runnable_Abstract {

	/**
	 * @synchronized
	 */
	public function common() {
		CM_Model_User::offlineOld();
		CM_ModelAsset_User_Roles::deleteOld();
		CM_Paging_Useragent_Abstract::deleteOlder(100 * 86400);
		CM_File_UserContent_Temp::deleteOlder(86400);
		CM_SVM::deleteOldTrainings(3000);
		CM_SVM::trainChanged();
		CM_Paging_Ip_Blocked::deleteOlder(7 * 86400);
		CM_Captcha::deleteOlder(3600);
		CM_ModelAsset_User_Roles::deleteOld();
		CM_Session::deleteExpired();
		CM_Stream_Video::getInstance()->synchronize();
		CM_Stream_Video::getInstance()->checkStreams();
		CM_KissTracking::getInstance()->exportEvents();
	}

	/**
	 * @synchronized
	 */
	public function heavy() {
		CM_Mail::processQueue(500);
		CM_Action_Abstract::aggregate();
	}

	public static function getPackageName() {
		return 'maintenance';
	}

}
