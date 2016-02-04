<?php

class CM_Log_Formatter_Html extends CM_Log_Formatter_Text {

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
            $exceptionText = $this->_format('<pre>{exception}</pre>', [
                'exception' => $exceptionText,
            ]);
        }
        return $exceptionText;
    }

    protected function _getDefaults() {
        return [
            'formatMessage' => '<h1>{message}</h1><span>{fqdn} - PHP {phpVersion}</span>',
            'formatDate'    => 'c',
        ];
    }

    /**
     * @param string $text
     * @param array  $data
     * @return string
     */
    protected function _format($text, array $data) {
        $data = \Functional\map($data, function ($value) {
            return CM_Util::htmlspecialchars($value);
        });
        return parent::_format($text, $data);
    }
}
