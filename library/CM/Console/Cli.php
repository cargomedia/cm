<?php

class CM_Console_Cli extends CM_Cli_Runnable_Abstract {

    public function interactive() {
        while ('exit' !== ($code = $this->_getStreamInput()->read('php >'))) {
            $result = null;
            try {
                ob_start();
                eval($code . ';');
                $result = ob_get_contents();
                ob_end_clean();
                if ($result) {
                    $this->_getStreamOutput()->writeln($result);
                }
            } catch (Exception $e) {
                $this->_getStreamError()->writeln($e->getMessage());
                $this->_getStreamError()->writeln($e->getTraceAsString());
            }
        }
    }

    public static function getPackageName() {
        return 'console';
    }
}
