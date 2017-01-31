<?php

abstract class CM_OutputStream_Abstract implements CM_OutputStream_Interface {

    /**
     * @param string $message
     */
    public function writeln($message) {
        $this->write($message . PHP_EOL);
    }

    /**
     * @param string $format
     * @param array  ...$params
     */
    public function writef($format, ...$params) {
        $this->write(sprintf($format, ...$params));
    }

    /**
     * @param string $format
     * @param array  ...$params
     */
    public function writefln($format, ...$params) {
        $this->writef(sprintf($format . PHP_EOL, ...$params));
    }
}
