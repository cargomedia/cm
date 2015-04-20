<?php

trait CM_EventHandler_EventHandlerTrait {

    /**
     * @var array[] $_callbacks
     */
    private $_callbacks = array();

    /**
     * @param string  $event
     * @param closure $callback
     */
    public function bind($event, Closure $callback) {
        $event = (string) $event;
        $this->_callbacks[$event][] = $callback;
    }

    /**
     * @param string        $event
     * @param callable|null $callback
     */
    public function unbind($event, Closure $callback = null) {
        $event = (string) $event;
        if (null === $callback) {
            unset($this->_callbacks[$event]);
        } else {
            $this->_callbacks[$event] = \Functional\reject($this->_callbacks[$event], function ($element) use ($callback) {
                return $callback === $element;
            });
        }
    }

    /**
     * @param string     $event
     * @param mixed|null $param1
     * @param mixed|null $param2 ...
     */
    public function trigger($event, $param1 = null, $param2 = null) {
        $event = (string) $event;
        $params = func_get_args();
        array_shift($params);
        if (!empty($this->_callbacks[$event])) {
            foreach ($this->_callbacks[$event] as $callback) {
                call_user_func_array($callback, $params);
            }
        }
    }
}
