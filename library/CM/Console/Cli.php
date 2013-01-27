<?php

class CM_Console_Cli extends CM_Cli_Runnable_Abstract {

	public function interactive() {
		while ('exit' !== ($code = $this->_getInput()->read('php >'))) {
			$result = null;
			try {
				$code = '$result = ' . $code . ';';
				if (false !== eval($code)) {
					$this->_getOutput()->writeln(print_r($result, true));
				}
			} catch (Exception $e) {
				$this->_getOutput()->writeln($e->getMessage());
				$this->_getOutput()->writeln($e->getTraceAsString());
			}
		}
	}

	public static function getPackageName() {
		return 'console';
	}

}
