<?php

$tableInfo = CM_Db_Db::exec('SHOW TABLE STATUS LIKE  \'cm_user_online\' ')->fetch();

if ($tableInfo['Engine'] !== 'MyISAM') {
    CM_Db_Db::exec('ALTER TABLE `cm_user_online` ENGINE=MyISAM;');
}
