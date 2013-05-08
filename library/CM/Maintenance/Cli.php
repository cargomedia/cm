<?php

class CM_Maintenance_Cli extends CM_Cli_Runnable_Abstract {

	/**
	 * @synchronized
	 */
	public function common() {
		$this->_executeCallbacks(array(
			function () {
				CM_Model_User::offlineOld();
			},
			function () {
				CM_ModelAsset_User_Roles::deleteOld();
			},
			function () {
				CM_Paging_Useragent_Abstract::deleteOlder(100 * 86400);
			},
			function () {
				CM_File_UserContent_Temp::deleteOlder(86400);
			},
			function () {
				CM_SVM_Model::deleteOldTrainings(3000);
			},
			function () {
				CM_SVM_Model::trainChanged();
			},
			function () {
				CM_Paging_Ip_Blocked::deleteOlder(7 * 86400);
			},
			function () {
				CM_Captcha::deleteOlder(3600);
			},
			function () {
				CM_ModelAsset_User_Roles::deleteOld();
			},
			function () {
				CM_Session::deleteExpired();
			},
			function () {
				CM_Stream_Video::getInstance()->synchronize();
			},
			function () {
				CM_Stream_Video::getInstance()->checkStreams();
			},
			function () {
				CM_KissTracking::getInstance()->exportEvents();
			},
			function () {
				CM_Stream_Message::getInstance()->synchronize();
			}
		));
	}

	/**
	 * @synchronized
	 */
	public function heavy() {
		$this->_executeCallbacks(array(
			function () {
				CM_Mail::processQueue(500);
			},
			function () {
				CM_Action_Abstract::aggregate();
			},
			function () {
				CM_Paging_Log_Abstract::deleteOlder(7 * 86400);
			}
		));
	}

	public static function getPackageName() {
		return 'maintenance';
	}

	/**
	 * @param callback[] $callbacks
	 */
	protected function _executeCallbacks($callbacks) {
		foreach ($callbacks as $callback) {
			try {
				call_user_func($callback);
			} catch (CM_Exception $e) {
				CM_Bootloader::getInstance()->handleException($e);
			}
		}
	}
}
