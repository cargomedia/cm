<?php

interface CM_JavascriptEncodable extends CM_ArrayConvertible {

    /**
     * Object data for its javascript representation
     *
     * @return array
     */
    public function toJavascript();

}
