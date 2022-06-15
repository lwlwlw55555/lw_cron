#!/bin/sh
current=`date "+%Y-%m-%d %H:%M:%S"`  
echo $current 
echo 'begin build vue'

sh /var/code/lw_cron/kill_vue.sh

cd /var/code/html/vueblog-vue

/usr/bin/npm install --save github-markdown-css
npm i
npm install --save github-markdown-css
npm install element-ui --save
npm install axios --save

/usr/bin/npm run build