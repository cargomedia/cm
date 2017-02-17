<?php

class CM_Jobdistribution_Job_Noop extends CM_Jobdistribution_Job_Abstract {

    protected function _execute(CM_Params $params) {
        (new CM_File(DIR_ROOT . 'log.txt'))->appendLine(var_export($params->getParamsEncoded(), true));
        $user = $params->getPhoto('photo');
        (new CM_File(DIR_ROOT . 'log.txt'))->appendLine(CM_Util::jsonEncode($user->toArray()));
    }
}
