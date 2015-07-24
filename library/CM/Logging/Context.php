<?php

class CM_Logging_Context {

    /** @var CM_Logging_Context_ComputerInfo */
    private $_computerInfo;

    /** @var CM_Model_User|null */
    private $_user;

    /** @var CM_Http_Request_Abstract|null */
    private $_httpRequest;

    /** @var string[] */
    private $_extra = [];

}
