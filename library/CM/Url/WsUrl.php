<?php

namespace CM\Url;

class WsUrl extends Url {

    protected static $defaultPorts = [
        'ws'  => 80,
        'wss' => 443,
    ];
}
