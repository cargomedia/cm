<?php

class CM_Jobdistribution_Cli extends CM_Cli_Runnable_Abstract {

  /**
   * @keepalive
   */
  public function startManager() {
    $worker = new CM_Jobdistribution_JobWorker();
    $worker->run();
  }

  public static function getPackageName() {
    return 'job-distribution';
  }
}
