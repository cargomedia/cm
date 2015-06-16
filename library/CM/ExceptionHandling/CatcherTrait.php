<?php

trait CM_ExceptionHandling_CatcherTrait {

    /**
     * @param callable $code
     * @return Exception|null
     */
    public function catchException(callable $code) {
        try {
            $code();
            return null;
        } catch (\Exception $e) {
            return $e;
        }
    }
}
