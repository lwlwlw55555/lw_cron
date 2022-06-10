#!/bin/sh
current=`date "+%Y-%m-%d %H:%M:%S"`  
echo $current 
echo 'begin start vue'

sh /var/code/lw_cron/kill_vue.sh

cd /var/code/html/vueblog-vue

/usr/bin/npm install --save github-markdown-css

nohup /usr/bin/npm run serve > /mnt/lw_vue &