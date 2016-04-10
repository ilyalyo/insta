#!/bin/sh -xe
mysql -e "CREATE DATABASE hello_database;"
mysql -e "Use hello_database;"
mysql $* < t.sql