<?php

interface CM_Migration_UpgradableInterface {

    /**
     * @param CM_OutputStream_Interface $output
     * @return mixed
     */
    public function up(CM_OutputStream_Interface $output);
}
