#!/bin/sh
current=`date "+%Y-%m-%d %H:%M:%S"`  
echo $current 
echo 'begin start vue'

sh /var/code/lw_cron/kill_vue.sh

sh /var/code/lw_cron/build_vue.sh

rm -rf /usr/local/dist

scp -r dist /usr/local/.