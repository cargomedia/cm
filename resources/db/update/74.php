<?php

return;

$tableInfo = CM_Db_Db::exec('SHOW TABLE STATUS LIKE  \'cm_user_online\' ')->fetch();

if ($tableInfo['Engine'] !== 'InnoDB') {
    CM_Db_Db::exec('ALTER TABLE `cm_user_online` ENGINE=InnoDB;');
}

