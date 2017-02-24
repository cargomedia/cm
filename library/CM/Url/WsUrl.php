<?php

namespace CM\Url;

class WsUrl extends Url {

    protected static $schemes = [
        'ws'  => 80,
        'wss' => 443,
    ];
}
