<?php

if (!CM_Db_Db::existsTable('cm_requestCounter')) {
    CM_Db_Db::exec('
        CREATE TABLE `cm_requestCounter` (
          `counter` int(10) unsigned NOT NULL,
          PRIMARY KEY (`counter`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    ');
    CM_Db_Db::insert('cm_requestCounter', array('counter' => 0));
}
