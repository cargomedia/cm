<?php

CM_Db_Db::exec('ALTER TABLE `cm_actionLimit` CHANGE `actionType` `actionType` INT UNSIGNED DEFAULT NULL');
