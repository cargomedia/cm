#!/bin/bash
cd $(dirname $0)

DB_NAME=example
DB_USER=root
DB_PASS=root

> dump.sql
echo 'SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";' >> dump.sql
echo '/*!40101 SET NAMES utf8 */;' >> dump.sql

mysqldump --no-data --compact --add-drop-table -u$DB_USER -p$DB_PASS $DB_NAME | perl -pe 's|\s*AUTO_INCREMENT=\d*\s*| |' | perl -pe 's|/\*.*\*/;||' >> dump.sql
