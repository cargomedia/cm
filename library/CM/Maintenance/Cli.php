<?php

class CM_Maintenance_Cli extends CM_Cli_Runnable_Abstract {

	/** CM_Clockwork_Manager */
	protected $_clockworkManager;

	public function start() {
		$this->_clockworkManager = new CM_Clockwork_Manager();
		$this->_registerCallbacks();
		$this->_clockworkManager->start();
	}

	/**
	 * @synchronized
	 */
	protected function _registerCallbacks() {
		$this->_registerClockworkCallbacks(new DateInterval('PT1M'), array(
			'CM_Model_User::offlineOld' => function () {
				CM_Model_User::offlineOld();
			},
			'CM_ModelAsset_User_Roles::deleteOld' => function () {
				CM_ModelAsset_User_Roles::deleteOld();
			},
			'CM_Paging_Useragent_Abstract::deleteOlder' => function () {
				CM_Paging_Useragent_Abstract::deleteOlder(100 * 86400);
			},
			'CM_File_UserContent_Temp::deleteOlder' => function () {
				CM_File_UserContent_Temp::deleteOlder(86400);
			},
			'CM_SVM_Model::deleteOldTrainings' => function () {
				CM_SVM_Model::deleteOldTrainings(3000);
			},
			'CM_SVM_Model::trainChanged' => function () {
				CM_SVM_Model::trainChanged();
			},
			'CM_Paging_Ip_Blocked::deleteOlder' => function () {
				CM_Paging_Ip_Blocked::deleteOlder(7 * 86400);
			},
			'CM_Captcha::deleteOlder' => function () {
				CM_Captcha::deleteOlder(3600);
			},
			'CM_Session::deleteExpired' => function () {
				CM_Session::deleteExpired();
			},
			'CM_Stream_Video::synchronize' => function () {
				CM_Stream_Video::getInstance()->synchronize();
			},
			'CM_Stream_Video::checkStreams' => function () {
				CM_Stream_Video::getInstance()->checkStreams();
			},
			'CM_KissTracking::exportEvents' => function () {
				CM_KissTracking::getInstance()->exportEvents();
			},
			'CM_Stream_Message::synchronize' => function () {
				CM_Stream_Message::getInstance()->synchronize();
			}
		));
		$this->_registerClockworkCallbacks(new DateInterval('PT15M'), array(
			'CM_Mail::processQueue' => function () {
				CM_Mail::processQueue(500);
			},
			'CM_Action_Abstract::aggregate' => function () {
				CM_Action_Abstract::aggregate();
			},
			'CM_Paging_Log_Abstract::deleteOlder' => function () {
				CM_Paging_Log_Abstract::deleteOlder(7 * 86400);
			}
		));
	}

	public static function getPackageName() {
		return 'maintenance';
	}

	/**
	 * @param DateInterval  $interval
	 * @param Closure[]     $callbacks
	 */
	protected function _registerClockworkCallbacks(DateInterval $interval, $callbacks) {
		foreach ($callbacks as $name => $callback) {
			$transactionName = 'cm.php ' . static::getPackageName() . ' run: ' . $name;
			$this->_clockworkManager->registerCallback($interval, function () use ($transactionName, $callback) {
				CMService_Newrelic::getInstance()->startTransaction($transactionName);
				try {
					$callback();
				} catch (CM_Exception $e) {
					CM_Bootloader::getInstance()->getExceptionHandler()->handleException($e);
				}
				CMService_Newrelic::getInstance()->endTransaction();
			});
		}
	}
}
