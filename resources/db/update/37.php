<?php

if (!CM_Db_Db::existsTable('cm_requestClientCounter')) {
    CM_Db_Db::exec('
        CREATE TABLE `cm_requestClientCounter` (
          `counter` int(10) unsigned NOT NULL,
          PRIMARY KEY (`counter`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    ');
    CM_Db_Db::insert('cm_requestClientCounter', array('counter' => 0));
}

if (CM_Db_Db::existsTable('cm_requestClient')) {
    $highestEntry = (int) CM_Db_Db::select('cm_requestClient', 'id', null, array('id' => 'DESC'))->fetchColumn();
    CM_Db_Db::update('cm_requestClientCounter', array('counter' => $highestEntry));
    CM_Db_Db::exec('DROP TABLE IF EXISTS `cm_requestClient`;');
}
