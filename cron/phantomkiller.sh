#!/bin/bash
#Script kills phantomjs processes running > 10mins
#getting PIDs:
PIDS="`ps aux | egrep "phantomjs" | grep "0:1" | awk '{ print $2 }'`"
#killing them:
echo "Killing phantomjs running >10 mins..."
for i in ${PIDS}; do { echo "killing $i"; kill -9 $i; }; done;