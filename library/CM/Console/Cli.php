<?php

class CM_Console_Cli extends CM_Cli_Runnable_Abstract {

	public function interactive() {
		while ('exit' !== ($code = $this->_getInput()->read('php >'))) {
			$result = null;
			try {
				ob_start();
				eval($code . ';');
				$result = ob_get_contents();
				ob_end_clean();
				if ($result) {
					$this->_getOutput()->writeln($result);
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
