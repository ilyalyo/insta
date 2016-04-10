#!/bin/sh -xe
mysql "CREATE DATABASE hello_database;"
mysql "Use hello_database;"
mysql $* < t.sql