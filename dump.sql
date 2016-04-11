#!/bin/sh -xe
mysql -e "CREATE DATABASE hello_database;"
mysql -e "Use hello_database;"
mysql -e "CREATE TABLE IF NOT EXISTS `pmt_attempts` (`attempt_id` int(11) NOT NULL AUTO_INCREMENT, `attempt_percent_mark` float NOT NULL)"