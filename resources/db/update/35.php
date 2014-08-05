<?php

CM_Db_Db::exec('ALTER TABLE `cm_actionLimit` CHANGE COLUMN `actionType` `actionType` INT NULL DEFAULT NULL');
