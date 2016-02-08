<?php

class CM_Exception_Javascript extends CM_Exception {

    /**
     * @param string $message
     * @param string $url
     * @param int    $counter
     * @param string $fileUrl
     * @param string $fileLine
     */
    public function __construct($message, $url, $counter, $fileUrl, $fileLine) {
        $message = (string) $message;
        $url = (string) $url;
        $counter = (int) $counter;
        $fileUrl = (string) $fileUrl;
        $fileLine = (string) $fileLine;

        parent::__construct($message, CM_Exception_Invalid::ERROR, [
            'url'      => $url,
            'counter'  => $counter,
            'fileUrl'  => $fileUrl,
            'fileLine' => $fileLine
        ]);
    }
}
