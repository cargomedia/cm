<?php

class CM_Log_Formatter_Html extends CM_Log_Formatter_Text {

    public function _getDefaults() {
        return [
            'formatMessage' => '<h2>{message} (fqdn: {fqdn}, php version: {phpVersion})</h2>',
            'formatDate'    => 'c',
        ];
    }

    public function renderMessage(CM_Log_Record $record) {
        $message = parent::renderMessage($record);
        if ($record instanceof CM_Log_Record_Exception) {
            $message = $this->_format('<h1>{exceptionClass}</h1>{message}', [
                'exceptionClass' => $record->getException()->getClass(),
                'message'        => $message
            ]);
        }
        return $message;
    }

    public function renderContext(CM_Log_Record $record) {
        $contextText = parent::renderContext($record);
        if (null !== $contextText) {
            $contextText = $this->_format('<h3>Context:</h3><pre>{context}</pre>', [
                'context' => $contextText
            ]);
        }
        return $contextText;
    }

    public function renderException(CM_Log_Record_Exception $record) {
        $exceptionText = parent::renderException($record);
        if (null !== $exceptionText) {
            $exceptionText = $this->_format('<h3>Exception:</h3><pre>{exception}</pre>', [
                'exception' => $exceptionText,
            ]);
        }
        return $exceptionText;
    }
}
