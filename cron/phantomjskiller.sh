#!/bin/bash
# This script will kill process which running more than X hours
# egrep: the selected process; grep: hours
PIDS="`ps eaxo bsdtime,pid,comm | egrep "phantomjs" | grep " [1-6][0-9]:" | awk '{print $2}'`"

# Kill the process
echo "Killing spamd, exim, mysqld and httpd processes running more than one hour..."
for i in ${PIDS}; do { echo "Killing $i"; kill -9 $i; }; done;