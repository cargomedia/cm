<?php

if (!CM_Db_Db::existsTable('cm_model_example_todo')) {
    CM_Db_Db::exec(<<<EOD
CREATE TABLE `cm_model_example_todo` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(64) NOT NULL,
  `description` varchar(140) NOT NULL,
  `state` int(2) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8
EOD
    );
}
