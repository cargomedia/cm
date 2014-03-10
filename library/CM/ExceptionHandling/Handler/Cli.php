<?php

class CM_ExceptionHandling_Handler_Cli extends CM_ExceptionHandling_Handler_Abstract {

    protected function _printException(Exception $exception) {
        $output = new CM_OutputStream_Stream_StandardError();
        $formatter = new CM_ExceptionHandling_Formatter_Plain();

        $output->writeln($formatter->formatException($exception));
    }
}
