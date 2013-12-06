<?php

class CM_Maintenance_Cli extends CM_Cli_Runnable_Abstract {

	/** @var callable[] */
	protected $_callbacks;

	/**
	 * @synchronized
	 */
	public function common() {
		$this->_registerCallbacks(array(
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
		$this->_executeCallbacks('common', new DateInterval('PT1M'));
	}

	/**
	 * @synchronized
	 */
	public function heavy() {
		$this->_registerCallbacks(array(
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
		$this->_executeCallbacks('heavy', new DateInterval('PT15M'));
	}

	public static function getPackageName() {
		return 'maintenance';
	}

	/**
	 * @param callable[] $callbacks
	 */
	protected function _registerCallbacks(array $callbacks) {
		if (!$this->_callbacks) {
			$this->_callbacks = array();
		}
		$this->_callbacks = array_merge($this->_callbacks, $callbacks);
	}

	/**
	 * @param string       $namespace
	 * @param DateInterval $interval
	 */
	protected function _executeCallbacks($namespace, DateInterval $interval) {
		$callbacks = $this->_callbacks;
		$transactionPrefix = 'cm.php ' . $this->getPackageName() . ' ' . $namespace . ': ';
		$this->_runInterval(function () use ($callbacks, $transactionPrefix) {
			foreach ($callbacks as $name => $callback) {
				CMService_Newrelic::getInstance()->startTransaction($transactionPrefix . $name);
				try {
					$callback();
				} catch (CM_Exception $e) {
					CM_Bootloader::getInstance()->getExceptionHandler()->handleException($e);
				}
				CMService_Newrelic::getInstance()->endTransaction();
			}
		}, $interval, true);
	}
}
