#!/bin/bash
PIDS=()
for pid in `ps aux | egrep "[n]ode" | awk '{print $2}'`;
do
    PIDS+=($pid);
done
if [ ${#PIDS[@]} == 0 ];
then
    cd /var/www/instastellar/
    nohup node server.js &
    #adding 3 hours, cuz we're in the MSK timezone
    curdate=`date +"%d.%m.%Y %T" -d "+ 3 hours"`
    echo $curdate>>/var/www/instastellar/nodestart.log
    echo "node restarted at $curdate"
fi