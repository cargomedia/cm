<?php

CM_Db_Db::exec('ALTER TABLE `cm_stream_publish` CHANGE `key` `key` varchar(36) NOT NULL');
CM_Db_Db::exec('ALTER TABLE `cm_stream_subscribe` CHANGE `key` `key` varchar(36) NOT NULL');
