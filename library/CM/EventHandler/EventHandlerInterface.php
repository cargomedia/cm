<?php

interface CM_EventHandler_EventHandlerInterface {

    /**
     * @param string   $event
     * @param callable $callback
     */
    public function bind($event, callable $callback);

    /**
     * @param string        $event
     * @param callable|null $callback
     */
    public function unbind($event, callable $callback = null);

    /**
     * @param string     $event
     * @param mixed|null $param1
     * @param mixed|null $param2 ...
     */
    public function trigger($event, $param1 = null, $param2 = null);
}
